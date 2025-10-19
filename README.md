# TLS Operations - CodeIgniter 4 Migration

## Project Overview

This is the **CodeIgniter 4 migration** of the TLS Operations Transportation Management System. The project migrates from custom PHP classes to the CodeIgniter 4 framework while:

- ✅ Preserving ALL existing stored procedures (no ORM, no Eloquent)
- ✅ Maintaining the custom Bootstrap 5 + TLS theme UI
- ✅ Implementing multi-tenant database architecture
- ✅ Using CI4's validation, session, and security features to prevent developer errors

## Why CodeIgniter 4?

The migration to CI4 addresses recurring implementation errors by providing:

1. **Built-in Form Validation** - Prevents input validation mistakes
2. **Structured MVC Pattern** - Enforces separation of concerns
3. **Session Management** - Secure, standardized session handling
4. **BaseModel Pattern** - Consistent stored procedure calling
5. **Filters/Middleware** - Automatic authentication enforcement
6. **Error Prevention** - Framework patterns prevent common mistakes (IDType defaults, two-phase deletion, column mapping, getNextKey, search patterns, SP method selection)

## Project Status

### ✅ Phase 1 Complete: Foundation (Week 1)
- CodeIgniter 4.6.3 installed via Composer
- SQL Server SQLSRV driver configured
- MAMP development environment (Apache 2.4.62 + PHP 8.3.14)
- Clean URLs enabled (.htaccess configured)
- TLS custom theme migrated (app.css, Bootstrap 5.3.0, Bootstrap Icons 1.10.0)
- JavaScript components migrated (tls-autocomplete.js, tls-form-tracker.js, tls-toast.js)
- GitHub repository created: https://github.com/TLSystemsLLC/TLS-CI4

### ✅ Phase 2 Complete: Authentication Infrastructure (Week 2)
- **BaseModel** with stored procedure helpers (callStoredProcedure, callStoredProcedureWithReturn, getNextKey)
- **TLSAuth Library** with multi-tenant database validation and switching
- **AuthFilter** for route protection middleware
- **BaseController** with automatic database context management
- **Login Controller** with CI4 form validation
- **Login View** with TLS theme
- **Dashboard Controller** requiring authentication
- **Dashboard View** displaying user information and permissions
- **Multi-tenant architecture** fully implemented and documented

### 🔄 Phase 3 In Progress: MenuManager Migration
- Migrate MenuManager class to CI4 library
- Dynamic menu generation based on user permissions

### 📋 Phase 4 Planned: Entity Maintenance Screens
- Driver Maintenance (proof-of-concept)
- Owner Maintenance
- Agent Maintenance
- Unit Maintenance (Tractor/Trailer)
- Carrier Maintenance
- Customer Maintenance

## Multi-Tenant Architecture

The TLS CI4 application implements **database-per-customer** multi-tenancy:

### Key Features:
- **Customer Database Validation** - Login validates against `spGetOperationsDB` in master database
- **Automatic Context Switching** - Database context switches to customer database on authentication
- **Session-Based Tenant Isolation** - `customer_db` stored in session for all requests
- **Real-Time Permission Checking** - `spUser_Menu` validates permissions in customer database
- **Guaranteed Database Context** - `getCustomerDb()` ensures operations happen in correct tenant database

See [MULTI_TENANT.md](MULTI_TENANT.md) for complete architecture documentation.

## Environment Configuration

### Development Environment
- **Web Server:** MAMP (Apache 2.4.62 + PHP 8.3.14)
- **Base URL:** http://localhost:8888/tls-ci4/
- **Database:** SQL Server 2017 at 35.226.40.170:1433
- **Default Database:** DEMO (for testing)

### Two-Location Workflow

**⚠️ CRITICAL**: The codebase exists in two locations:

**Source Code Location** (Development & Git):
```
/Users/tonylyle/source/repos/tls-ci4/
```
- Primary development location
- Git repository and version control
- Code editing and testing

**Execution Location** (MAMP Web Server):
```
/Applications/MAMP/htdocs/tls-ci4/
```
- Web server execution environment
- Running application and browser testing

**Synchronization:**
```bash
# After making changes in source location
cp -r /Users/tonylyle/source/repos/tls-ci4/* /Applications/MAMP/htdocs/tls-ci4/
```

**⚠️ Never edit directly in MAMP location** - always edit in source first, then copy to MAMP.

### Database Configuration

**Production Databases (DO NOT MODIFY):**
- ZIPData, Utility, Transflo, TLSystems, ReportServer, ReportServerTempDB
- MRWR, EDI, EDI2, BASEDB

**Development/Testing Databases:**
- **DEMO** - Contains `spUser_Login`, `spUser_Menus`, active test users
- **TLSYS** - Contains all required stored procedures, system users
- **CWKI2** - Production data copy for development testing
- **TEST** - Dedicated database for automated testing (PHPUnit)

**Available Test Users:**
- DEMO: chodge, cknox, cscott, dhatfield, egarcia
- TLSYS: SYSTEM, tlyle, wjohnston

## Installation & Setup

### 1. Clone Repository
```bash
git clone git@github.com:TLSystemsLLC/TLS-CI4.git tls-ci4
cd tls-ci4
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Configure Environment
```bash
cp env .env
```

Edit `.env` file:
```env
CI_ENVIRONMENT = development
app.baseURL = 'http://localhost:8888/tls-ci4/'

database.default.hostname = 35.226.40.170
database.default.database = DEMO
database.default.username = Admin
database.default.password = Aspen4Home!
database.default.DBDriver = SQLSRV
database.default.port = 1433
```

### 4. Configure Web Server

For MAMP, ensure DocumentRoot points to:
```
/Applications/MAMP/htdocs/tls-ci4/public/
```

For other servers, point to the `public/` folder.

### 5. Test Installation

Visit: http://localhost:8888/tls-ci4/

You should see the CodeIgniter welcome page.

## Testing Authentication Flow

### 1. Visit Dashboard (Unauthenticated)
URL: http://localhost:8888/tls-ci4/dashboard

Expected: Redirect to login page

### 2. Login
URL: http://localhost:8888/tls-ci4/login

Test Credentials:
- **Customer:** DEMO
- **User ID:** tlyle (or cknox, cscott, etc.)
- **Password:** [your password]

### 3. View Dashboard
After successful login, should display:
- User information (ID, name, company)
- Company information
- Permission list
- Logout button

### 4. Test Multi-Tenant Isolation
1. Login as User A to DEMO database
2. Note the data/permissions
3. Logout
4. Login as User B to TLSYS database
5. Note different data/permissions/company

## Project Structure

```
tls-ci4/
├── app/
│   ├── Config/
│   │   ├── Database.php          # SQL Server SQLSRV configuration
│   │   └── Routes.php             # Application routes
│   ├── Controllers/
│   │   ├── BaseController.php     # Authentication helpers, database context
│   │   ├── Login.php              # Login/logout with CI4 validation
│   │   └── Dashboard.php          # Protected dashboard
│   ├── Filters/
│   │   └── AuthFilter.php         # Authentication middleware
│   ├── Libraries/
│   │   └── TLSAuth.php            # Multi-tenant authentication
│   ├── Models/
│   │   └── BaseModel.php          # Stored procedure helpers
│   └── Views/
│       ├── auth/
│       │   └── login.php          # TLS-themed login page
│       └── dashboard/
│           └── index.php          # Dashboard view
├── public/
│   ├── css/
│   │   └── app.css                # TLS custom theme
│   ├── js/
│   │   ├── tls-autocomplete.js    # Autocomplete component
│   │   ├── tls-form-tracker.js    # Change detection
│   │   └── tls-toast.js           # Toast notifications
│   └── index.php                  # Application entry point
├── .env                           # Environment configuration
├── composer.json                  # Dependencies
├── MULTI_TENANT.md                # Multi-tenant architecture docs
└── README.md                      # This file
```

## Core Classes

### BaseModel (`app/Models/BaseModel.php`)
Provides helpers for calling stored procedures:

```php
// Call stored procedure that returns data
$results = $this->callStoredProcedure('spDriver_Get', [$driverKey]);

// Call stored procedure with return code
$returnCode = $this->callStoredProcedureWithReturn('spDriver_Save', $params);

// Get next surrogate key
$nextKey = $this->getNextKey('tDriver');
```

### TLSAuth (`app/Libraries/TLSAuth.php`)
Multi-tenant authentication with database validation:

```php
// Login with customer database validation
$result = $auth->login($customer, $userId, $password);

// Check if user is logged in
if ($auth->isLoggedIn()) { ... }

// Check menu permission (real-time in customer database)
if ($auth->hasMenuAccess('mnuDriverMaint')) { ... }

// Get current user information
$user = $auth->getCurrentUser();

// Logout
$auth->logout();
```

### BaseController (`app/Controllers/BaseController.php`)
Authentication helpers with automatic database context:

```php
// Require authentication (redirect if not logged in)
$this->requireAuth();

// Require specific menu permission (403 if denied)
$this->requireMenuPermission('mnuDriverMaint');

// Check menu access (boolean)
if ($this->hasMenuAccess('mnuDriverMaint')) { ... }

// Get current user
$user = $this->getCurrentUser();

// Get customer database name
$database = $this->getCurrentDatabase();

// Get database connection with guaranteed tenant context
$db = $this->getCustomerDb();
```

## Controller Pattern

All controllers should follow this pattern:

```php
<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\BaseModel;

class DriverMaintenance extends BaseController
{
    public function index()
    {
        // Require authentication - redirects if not logged in
        $this->requireAuth();

        // Require specific permission - 403 if denied
        $this->requireMenuPermission('mnuDriverMaint');

        // Database context automatically set to customer database
        $model = new BaseModel();

        // Call stored procedure - executes in customer database
        $drivers = $model->callStoredProcedure('spDriver_Search', [
            $searchTerm,
            $includeInactive
        ]);

        return view('driver/maintenance', [
            'pageTitle' => 'Driver Maintenance',
            'drivers' => $drivers
        ]);
    }

    public function save()
    {
        $this->requireAuth();
        $this->requireMenuPermission('mnuDriverMaint');

        // Get guaranteed customer database context
        $db = $this->getCustomerDb();

        $model = new BaseModel();

        // Save driver - executes in customer database
        $returnCode = $model->callStoredProcedureWithReturn(
            'spDriver_Save',
            $params
        );

        if ($returnCode === 0) {
            return redirect()->to('/driver-maintenance')
                ->with('success', 'Driver saved successfully');
        }
    }
}
```

## View Pattern

All views should use TLS standardized theme:

```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle) ?></title>

    <!-- REQUIRED CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= base_url('css/app.css') ?>" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="tls-page-header">
            <h1 class="tls-page-title">
                <i class="bi-person-badge me-2"></i>Driver Maintenance
            </h1>
            <div class="tls-top-actions">
                <button type="button" class="btn tls-btn-primary">Save</button>
                <button type="button" class="btn tls-btn-secondary">Cancel</button>
            </div>
        </div>

        <!-- Form Card -->
        <div class="tls-form-card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi-info-circle me-2"></i>Driver Information
                </h5>
            </div>
            <div class="card-body">
                <!-- Content here -->
            </div>
        </div>
    </div>

    <!-- REQUIRED JAVASCRIPT -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url('js/tls-form-tracker.js') ?>"></script>
</body>
</html>
```

## Development Workflow

1. **Edit code** in `/Users/tonylyle/source/repos/tls-ci4/`
2. **Copy to MAMP** for testing:
   ```bash
   cp -r /Users/tonylyle/source/repos/tls-ci4/* /Applications/MAMP/htdocs/tls-ci4/
   ```
3. **Test in browser** at http://localhost:8888/tls-ci4/
4. **Commit changes** from source repo:
   ```bash
   cd /Users/tonylyle/source/repos/tls-ci4
   git add .
   git commit -m "feat: description of changes"
   git push origin main
   ```

## Key Benefits of CI4 Migration

### Error Prevention
- ✅ **Form Validation** - CI4 validates all input automatically
- ✅ **Structured Patterns** - Framework enforces consistent code patterns
- ✅ **Database Context Management** - Automatic tenant isolation
- ✅ **Session Security** - Built-in CSRF protection and session regeneration
- ✅ **BaseModel Pattern** - Consistent stored procedure calling prevents errors

### Developer Experience
- ✅ **Clear Controller Pattern** - Easy to follow, hard to get wrong
- ✅ **Automatic Authentication** - Filters handle route protection
- ✅ **Helper Methods** - `requireAuth()`, `hasMenuAccess()`, `getCustomerDb()`
- ✅ **Error Messages** - Framework provides detailed error reporting
- ✅ **Documentation** - Well-documented framework with large community

### UI/UX Consistency
- ✅ **TLS Theme Preserved** - 100% identical to custom PHP version
- ✅ **Bootstrap 5.3.0** - Modern, responsive framework
- ✅ **Standardized Components** - Consistent buttons, cards, forms
- ✅ **JavaScript Libraries** - TLSAutocomplete, TLSFormTracker reusable

## Resources

- **CodeIgniter 4 Documentation:** https://codeigniter.com/user_guide/
- **CI4 Database Guide:** https://codeigniter.com/user_guide/database/index.html
- **CI4 Session Library:** https://codeigniter.com/user_guide/libraries/sessions.html
- **CI4 Validation:** https://codeigniter.com/user_guide/libraries/validation.html
- **GitHub Repository:** https://github.com/TLSystemsLLC/TLS-CI4

## Support

For issues or questions:
1. Check this README and MULTI_TENANT.md documentation
2. Review CI4 user guide for framework questions
3. Check existing code patterns in BaseController/TLSAuth
4. Test authentication flow with DEMO database credentials

## License

Proprietary - TLS Systems LLC
