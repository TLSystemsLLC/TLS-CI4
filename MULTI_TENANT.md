# Multi-Tenant Database Architecture

## Overview

The TLS CI4 application implements a **multi-tenant architecture** where each customer operates in their own isolated SQL Server database. This document explains how the authentication and database context management works.

## Architecture Components

### 1. Customer Database Validation (Login)

**Location:** `TLSAuth::isValidCustomerId()` (lines 282-310)

**Process:**
```php
// User enters: Customer = "DEMO", UserID = "tlyle", Password = "xxx"

1. Connect to master database
2. Execute spGetOperationsDB to get valid database list
3. Validate "DEMO" exists in the list
4. If valid, proceed to authentication
5. If invalid, reject with "Invalid customer ID"
```

**Valid Operations Databases:**
- CWKI, CWKI2, DEMO, TLSYS, TEST
- Plus production customer databases

**Protected Databases (NOT accessible via login):**
- master, tempdb, model, msdb
- ReportServer, ReportServerTempDB
- Utility, ZIPData, Transflo, TLSystems

### 2. Database Context Switching (Authentication)

**Location:** `TLSAuth::login()` (line 53)

**Process:**
```php
// After validating customer ID
$this->db->setDatabase($customer);  // Switch to DEMO database

// Execute stored procedures in customer database
EXEC spUser_Login 'tlyle', 'password'    // In DEMO database
EXEC spUser_Menus 'tlyle'                // In DEMO database
EXEC spUser_GetUser 'tlyle'              // In DEMO database
EXEC spCompany_Get 1                     // In DEMO database
```

**Session Storage:**
```php
$sessionData = [
    'user_id' => 'tlyle',
    'customer_db' => 'DEMO',     // CRITICAL: Store tenant database
    'user_menus' => [...],
    'user_details' => [...],
    'company_info' => [...],
    'login_time' => time(),
    'logged_in' => true
];
```

### 3. Automatic Database Context (Every Request)

**Location:** `BaseController::initController()` (lines 75-82)

**Process:**
```php
// On EVERY controller initialization
if ($this->auth->isLoggedIn()) {
    $customerDb = $this->session->get('customer_db');  // Get 'DEMO' from session
    if ($customerDb) {
        $this->db->setDatabase($customerDb);           // Switch to DEMO
    }
}
```

**Result:** All database operations automatically happen in the customer's database without manual switching.

### 4. Menu Permission Checks (Real-Time Validation)

**Location:** `TLSAuth::hasMenuAccess()` (lines 185-208)

**Process:**
```php
// User tries to access mnuDriverMaint
public function hasMenuAccess(string $menuName): bool
{
    $userId = $this->session->get('user_id');       // 'tlyle'
    $customerDb = $this->session->get('customer_db'); // 'DEMO'

    $this->db->setDatabase($customerDb);              // Switch to DEMO

    // Check permission in DEMO database
    EXEC spUser_Menu 'tlyle', 'mnuDriverMaint'

    return $returnCode === 0;  // 0 = allowed, other = denied
}
```

### 5. Guaranteed Tenant Context Helper

**Location:** `BaseController::getCustomerDb()` (lines 157-172)

**Usage in Controllers:**
```php
// Example: Driver Maintenance controller
public function loadDriver(int $driverKey)
{
    $this->requireAuth();  // Ensure logged in

    // Get database connection with guaranteed tenant context
    $db = $this->getCustomerDb();  // Automatically set to customer's database

    // Call stored procedure - executes in customer database
    $model = new BaseModel();
    $driver = $model->callStoredProcedure('spDriver_Get', [$driverKey]);

    return $driver;
}
```

**Error Handling:**
```php
// If not logged in
throw new \RuntimeException('Cannot get customer database: User not logged in');

// If no customer database in session
throw new \RuntimeException('Cannot get customer database: No customer database in session');
```

## Multi-Tenant Data Flow Example

### Scenario: User "tlyle" logs into DEMO database and loads Driver #123

```
1. LOGIN PHASE
   ┌─────────────────────────────────────────────────────────┐
   │ User Input: Customer=DEMO, UserID=tlyle, Password=xxx  │
   └──────────────────────┬──────────────────────────────────┘
                          │
   ┌──────────────────────▼──────────────────────────────────┐
   │ TLSAuth::isValidCustomerId('DEMO')                      │
   │ • Connect to master database                            │
   │ • EXEC spGetOperationsDB                                │
   │ • Verify 'DEMO' in results                              │
   │ • ✓ Valid                                               │
   └──────────────────────┬──────────────────────────────────┘
                          │
   ┌──────────────────────▼──────────────────────────────────┐
   │ TLSAuth::login()                                         │
   │ • $this->db->setDatabase('DEMO')                        │
   │ • EXEC spUser_Login 'tlyle', 'xxx' → return 0 (success) │
   │ • EXEC spUser_Menus 'tlyle' → [mnuDriverMaint, ...]    │
   │ • EXEC spUser_GetUser 'tlyle' → user details            │
   │ • EXEC spCompany_Get 1 → company info                   │
   └──────────────────────┬──────────────────────────────────┘
                          │
   ┌──────────────────────▼──────────────────────────────────┐
   │ Session Created                                          │
   │ • user_id = 'tlyle'                                     │
   │ • customer_db = 'DEMO' ← STORED FOR ALL FUTURE REQUESTS │
   │ • user_menus = [mnuDriverMaint, ...]                    │
   │ • logged_in = true                                      │
   └──────────────────────────────────────────────────────────┘

2. DRIVER MAINTENANCE PHASE
   ┌─────────────────────────────────────────────────────────┐
   │ User clicks Driver Maintenance menu                      │
   └──────────────────────┬──────────────────────────────────┘
                          │
   ┌──────────────────────▼──────────────────────────────────┐
   │ BaseController::initController()                         │
   │ • isLoggedIn() → true                                   │
   │ • $customerDb = session->get('customer_db') → 'DEMO'   │
   │ • $this->db->setDatabase('DEMO')                        │
   └──────────────────────┬──────────────────────────────────┘
                          │
   ┌──────────────────────▼──────────────────────────────────┐
   │ BaseController::requireMenuPermission('mnuDriverMaint')  │
   │ • TLSAuth::hasMenuAccess('mnuDriverMaint')              │
   │ • $this->db->setDatabase('DEMO')                        │
   │ • EXEC spUser_Menu 'tlyle', 'mnuDriverMaint' → 0 (OK)  │
   └──────────────────────┬──────────────────────────────────┘
                          │
   ┌──────────────────────▼──────────────────────────────────┐
   │ User searches for Driver #123                            │
   └──────────────────────┬──────────────────────────────────┘
                          │
   ┌──────────────────────▼──────────────────────────────────┐
   │ DriverController::loadDriver(123)                        │
   │ • $db = $this->getCustomerDb()                          │
   │   ├─ Verify logged in ✓                                 │
   │   ├─ Get customer_db from session → 'DEMO'             │
   │   └─ $this->db->setDatabase('DEMO')                     │
   │ • BaseModel::callStoredProcedure('spDriver_Get', [123]) │
   │   └─ EXEC spDriver_Get 123 IN DEMO DATABASE             │
   │ • Driver data returned from DEMO.dbo.tDriver table      │
   └──────────────────────────────────────────────────────────┘
```

## Security Features

### 1. Database Isolation
- Each customer's data is completely isolated in separate databases
- Users can only access data in their authenticated customer database
- No cross-tenant data access possible

### 2. Validation Layers
```
Layer 1: Customer ID Validation
  ↓ spGetOperationsDB in master database
  ↓ Only valid operations databases allowed

Layer 2: Authentication
  ↓ spUser_Login in customer database
  ↓ User must exist and password must match

Layer 3: Session Management
  ↓ customer_db stored in encrypted session
  ↓ Timeout enforcement

Layer 4: Permission Checking
  ↓ spUser_Menu real-time validation
  ↓ Menu-based security model

Layer 5: Database Context Enforcement
  ↓ Automatic context switching on every request
  ↓ getCustomerDb() guarantees correct database
```

### 3. Audit Trail
All database operations are logged with customer context:
```
[INFO] Successful login: User 'tlyle' to database 'DEMO'
[INFO] User logout: 'tlyle' from database 'DEMO'
[WARNING] Failed login attempt: User 'invalid' to database 'DEMO' - Code: -1
[ERROR] Customer ID validation error: Invalid customer ID 'master'
```

## Testing Multi-Tenant Functionality

### Test Scenario 1: Different Users, Same Database
```
User A: Customer=DEMO, UserID=tlyle
User B: Customer=DEMO, UserID=cknox

Expected: Both access DEMO database with different permissions
```

### Test Scenario 2: Same User, Different Databases
```
Session 1: Customer=DEMO, UserID=tlyle
Session 2: Customer=CWKI2, UserID=tlyle

Expected: Different data, different companies, different permissions
```

### Test Scenario 3: Invalid Customer
```
User Input: Customer=master, UserID=tlyle

Expected: Login rejected with "Invalid customer ID"
```

### Test Scenario 4: Cross-Database Protection
```
Login: Customer=DEMO, UserID=tlyle
Attempt: Manually query CWKI2 data

Expected: Impossible - database context locked to DEMO
```

## Best Practices for Developers

### ✅ DO:
1. **Always use `$this->getCustomerDb()`** in controllers when you need database access
2. **Trust the automatic context switching** in BaseController
3. **Use stored procedures only** - they execute in the current database context
4. **Log customer database** in error messages for debugging

### ❌ DON'T:
1. **Never hardcode database names** in queries or connection strings
2. **Never call `setDatabase()` manually** unless you have a specific reason
3. **Never assume you're in a specific database** - always check session
4. **Never query tables directly** - use stored procedures

### Example: Correct Controller Pattern
```php
<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\BaseModel;

class DriverMaintenance extends BaseController
{
    public function index()
    {
        // Authentication automatically sets database context
        $this->requireAuth();
        $this->requireMenuPermission('mnuDriverMaint');

        // Database connection already set to customer database
        $model = new BaseModel();

        // This will execute in the customer's database
        $drivers = $model->callStoredProcedure('spDriver_Search', [
            $searchTerm,
            $includeInactive
        ]);

        return view('driver/maintenance', ['drivers' => $drivers]);
    }

    public function save()
    {
        $this->requireAuth();
        $this->requireMenuPermission('mnuDriverMaint');

        // Get guaranteed customer database context
        $db = $this->getCustomerDb();

        $model = new BaseModel();

        // Automatically executes in customer database
        $returnCode = $model->callStoredProcedureWithReturn(
            'spDriver_Save',
            [$driverKey, $firstName, $lastName, /* ... */]
        );

        if ($returnCode === 0) {
            return redirect()->to('/driver-maintenance')->with('success', 'Driver saved');
        }
    }
}
```

## Troubleshooting

### Problem: "User not logged in" error
**Cause:** Session expired or not authenticated
**Solution:** Check session timeout, verify login flow

### Problem: "Cannot find stored procedure"
**Cause:** Wrong database context
**Solution:** Verify customer database has the stored procedure

### Problem: Permission denied on menu
**Cause:** User doesn't have permission in customer database
**Solution:** Check tUserMenus table in customer database

### Problem: Data from wrong customer appears
**Cause:** Database context not set correctly
**Solution:** Use `$this->getCustomerDb()` instead of `$this->db`

## Summary

The CI4 TLS application maintains **perfect tenant isolation** through:

1. ✅ Customer ID validation against master database
2. ✅ Automatic database context switching on login
3. ✅ Session storage of customer database
4. ✅ Automatic context restoration on every request
5. ✅ Real-time permission checking in customer database
6. ✅ Helper methods to guarantee correct database context
7. ✅ Comprehensive logging of all tenant operations

**Result:** Developers can focus on business logic without worrying about database context - the framework handles multi-tenant isolation automatically.
