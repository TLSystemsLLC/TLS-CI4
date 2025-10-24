# CLAUDE.md - TLS-CI4 Project

This file provides guidance to Claude Code when working with the TLS-CI4 CodeIgniter 4 migration project.

## Project Overview

This repository contains the **CodeIgniter 4 migration** of TLS Operations, migrating from custom PHP to a structured MVC framework while preserving all SQL Server stored procedures and multi-tenant database architecture.

**Repository:** https://github.com/TLSystemsLLC/TLS-CI4

**Important:** This is a NEW project, separate from the legacy `/Applications/MAMP/htdocs/tls/` (tls-web) application. They share the same database backend but have different codebases.

## Architecture

### Framework: CodeIgniter 4.6.3

**Why CodeIgniter 4:**
- Compatible with stored procedures (no ORM required)
- Maintains existing database architecture
- Provides structure to prevent developer errors
- Built-in form validation prevents input mistakes
- Session management and CSRF protection
- Can preserve custom UI theme completely

### Database-Centric Design (UNCHANGED)
- All business logic resides in SQL Server stored procedures
- Each customer operates from a separate database instance
- Database connections follow connect-execute-disconnect pattern
- Authentication uses `spUser_Login`, menu security uses `spUser_Menus` and `spUser_Menu`

### Critical Database Conventions

**⚠️ MANDATORY: Null Date Convention**
- SQL Server uses `'1899-12-30 00:00:00.000'` to represent "no date" or "null date"
- This date appears in StartDate, EndDate, HireDate, TerminationDate, etc.
- **NEVER use the ACTIVE column to determine if a record is active**
- **ALWAYS check EndDate to determine active status:**
  - Active: `EndDate IS NULL OR EndDate = '1899-12-30'`
  - Inactive: `EndDate IS NOT NULL AND EndDate != '1899-12-30'`
- When saving empty dates, convert to `'1899-12-30'` before calling stored procedures
- When displaying dates, check if date equals `'1899-12-30'` and show as empty

**Example Usage:**
```php
// Check if agent is active
if (empty($agent['EndDate']) || $agent['EndDate'] == '1899-12-30 00:00:00.000') {
    // Agent is active
}

// Save empty date
$endDate = !empty($formData['end_date']) ? $formData['end_date'] : '1899-12-30';
```

**IMPORTANT:** The ACTIVE column exists in many tables but is NOT reliable. Customers do not maintain this field. Always use date fields to determine status.

**Maintaining ACTIVE Flag Going Forward:**
- When saving records, automatically set ACTIVE based on EndDate:
  ```php
  $isActive = ($endDate === '1899-12-30' || empty($endDate)) ? 1 : 0;
  ```
- Display ACTIVE checkbox as readonly/disabled with note "(auto-set by End Date)"
- This gradually corrects the data as records are edited
- Never rely on ACTIVE for queries - always use EndDate

### Multi-Tenant Architecture

**Automatic Database Context Management:**
```php
// BaseController automatically sets database context on EVERY request
public function initController(...)
{
    parent::initController($request, $response, $logger);
    $this->auth = new TLSAuth();
    $this->session = \Config\Services::session();
    $this->db = \Config\Database::connect();

    // CRITICAL: Set database context to customer database if logged in
    if ($this->auth->isLoggedIn()) {
        $customerDb = $this->session->get('customer_db');
        if ($customerDb) {
            $this->db->setDatabase($customerDb);
        }
    }
}
```

**Session-Based Tenant Isolation:**
```php
$sessionData = [
    'user_id' => 'tlyle',
    'customer_db' => 'DEMO',  // Tenant database
    'user_menus' => [...],
    'company_info' => [...],
    'logged_in' => true
];
```

## Core Classes (CI4 Framework)

### 1. BaseModel (`app/Models/BaseModel.php`)
Extends CodeIgniter's Model class with stored procedure helpers:

```php
// Execute stored procedure and return results
$results = $model->callStoredProcedure('spDriver_Get', [$driverKey]);

// Execute stored procedure and get return code
$returnCode = $model->callStoredProcedureWithReturn('spDriver_Save', $params);

// Generate surrogate keys from tSurrogateKey
$newKey = $model->getNextKey('Driver');
```

**CRITICAL:** `callStoredProcedureWithReturn()` uses `sqlsrv_next_result()` to properly iterate through SQL Server's multi-result sets. This is required to capture return codes.

### 2. TLSAuth Library (`app/Libraries/TLSAuth.php`)
Custom authentication library (not using CI4's built-in auth):

**Methods:**
- `login($customer, $userId, $password)` - Three-field authentication with database validation
- `isLoggedIn()` - Session validation with timeout
- `hasMenuAccess($menuKey)` - Real-time permission checking via spUser_Menu
- `getCurrentUser()` - Get user data from session
- `logout()` - Destroy session
- `isValidCustomerId($customerId)` - Validate against spGetOperationsDB in master database

### 3. AuthFilter (`app/Filters/AuthFilter.php`)
Middleware for route protection - automatically redirects to login if not authenticated.

### 4. BaseController (`app/Controllers/BaseController.php`)
All controllers extend this with built-in auth helpers:

**Helper Methods:**
- `requireAuth()` - Require authentication or redirect
- `hasMenuAccess($menuKey)` - Check menu permission
- `requireMenuPermission($menuKey)` - Enforce permission or return 403
- `getCurrentUser()` - Get current user from session
- `getCurrentDatabase()` - Get customer database name
- `getCustomerDb()` - Get DB connection with guaranteed tenant context

### 5. MenuManager Library (`app/Libraries/MenuManager.php`)
Pure MVC data provider for navigation menus:

**Design Philosophy:**
- MenuManager returns **data arrays only** (no HTML generation)
- Views consume the data and render HTML
- Clean separation of business logic and presentation

**Methods:**
- `getMenuStructure()` - Returns filtered menu tree based on user permissions
- `hasMenuAccess($menuKey)` - Checks if user can access menu (recursive child checking)
- `getBreadcrumbPath($menuKey)` - Returns breadcrumb trail for current page

**Permission System:**
- Reads user permissions from session (loaded by TLSAuth at login via `spUser_Menus`)
- Session-based caching for performance
- Automatically hides security permissions (keys starting with 'sec')
- Shows parent categories if user has access to ANY child menu

### 6. Menu Configuration (`app/Config/Menus.php`)
Defines complete menu hierarchy for the application:
- 8 top-level categories: Accounting, Dispatch, Logistics, Imaging, Reports, Safety, Payroll, Systems
- 100+ menu items migrated from legacy system
- Menu keys match database `MenuKey` values from `tMenu` table
- URLs map to CI4 routes

## Development Environment

### Two-Location Workflow (CRITICAL)

**⚠️ CRITICAL:** The codebase exists in two locations that must be synchronized:

**Source Code Location** (Development & Git):
```
/Users/tonylyle/source/repos/tls-ci4/
```
- Primary development location
- Git repository and version control
- Used for: Code editing, Git operations

**Execution Location** (MAMP Web Server):
```
/Applications/MAMP/htdocs/tls-ci4/
```
- Web server execution environment
- Used for: Running the application, browser testing

**Synchronization Command:**
```bash
# After making changes in source location, copy to MAMP for testing:
cp -r /Users/tonylyle/source/repos/tls-ci4/* /Applications/MAMP/htdocs/tls-ci4/
```

**⚠️ NEVER edit directly in MAMP location** - always edit in source location first, then sync.

### Environment Configuration

**Development URL:** http://localhost:8888/tls-ci4/

**Environment:** MAMP (Apache 2.4.62 + PHP 8.3.14) on macOS

**Database:** SQL Server 2017 at 35.226.40.170:1433

**Configuration Files:**
- `.env` - Environment variables (database credentials, base URL)
- `app/Config/App.php` - Application settings (`indexPage = ''` for clean URLs)
- `app/Config/Database.php` - Database connection settings
- `app/Config/Routes.php` - URL routing
- `app/Config/Filters.php` - Middleware registration

## CodeIgniter 4 Conventions

### Directory Structure

```
tls-ci4/
├── app/
│   ├── Config/           # Configuration files
│   ├── Controllers/      # Request handlers
│   ├── Filters/          # Middleware (like AuthFilter)
│   ├── Libraries/        # Custom libraries (like TLSAuth)
│   ├── Models/           # Database models
│   └── Views/            # HTML templates
├── public/               # Web root (index.php, assets)
│   ├── css/             # Stylesheets (app.css)
│   ├── js/              # JavaScript files
│   └── .htaccess        # URL rewriting
├── writable/            # Logs, cache, sessions
├── .env                 # Environment configuration
├── .htaccess            # Root redirect to public/
└── composer.json        # PHP dependencies
```

### Naming Conventions

**Controllers:** PascalCase, singular
- `Login.php`, `Dashboard.php`, `DriverMaintenance.php`

**Models:** PascalCase, singular, ends with "Model"
- `DriverModel.php`, `LoadModel.php`

**Views:** snake_case, organized by section
- `app/Views/auth/login.php`
- `app/Views/safety/driver_maintenance.php`

**Libraries:** PascalCase
- `TLSAuth.php`, `MenuManager.php`

**Routes:** Lowercase with hyphens
- `/driver-maintenance`, `/load-entry`, `/user-security`

## UI/UX Standards (MANDATORY)

### Required CSS Framework Includes

**MANDATORY for every HTML page:**

```html
<!-- REQUIRED: Bootstrap CSS (exact version) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- REQUIRED: Bootstrap Icons (exact version) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

<!-- REQUIRED: TLS Application CSS (standardized theme) -->
<link href="<?= base_url('css/app.css') ?>" rel="stylesheet">
```

### Standardized Classes

**Use these classes from app.css:**
- `.tls-page-header` - Page header with title and action buttons
- `.tls-page-title` - Main page title
- `.tls-top-actions` - Action button container (right-aligned)
- `.tls-form-card` - Standardized card styling
- `.tls-btn-primary` - Green gradient (save/submit)
- `.tls-btn-secondary` - Red gradient (cancel/reset)
- `.tls-btn-warning` - Warning/caution actions

## Standard Patterns

### Controller Pattern

```php
<?php
namespace App\Controllers;

class DriverMaintenance extends BaseController
{
    public function index()
    {
        // 1. Require authentication
        $this->requireAuth();

        // 2. Check menu permissions
        $this->requireMenuPermission('mnuDriverMaint');

        // 3. Get database with guaranteed tenant context
        $db = $this->getCustomerDb();

        // 4. Execute stored procedures
        $drivers = $db->query('EXEC spDriver_GetAll')->getResultArray();

        // 5. Prepare view data
        $data = [
            'pageTitle' => 'Driver Maintenance',
            'drivers' => $drivers,
            'user' => $this->getCurrentUser()
        ];

        // 6. Return view
        return view('safety/driver_maintenance', $data);
    }

    public function save()
    {
        $this->requireAuth();
        $this->requireMenuPermission('mnuDriverMaint');

        // Validate input (CI4 validation)
        $validation = \Config\Services::validation();
        $validation->setRules([
            'driver_name' => 'required|min_length[3]',
            'driver_id' => 'required|alpha_numeric'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        // Execute stored procedure
        $db = $this->getCustomerDb();
        $returnCode = $db->query('DECLARE @RC INT; EXEC @RC = spDriver_Save ?, ?; SELECT @RC AS ReturnValue',
            [$driverName, $driverId])->getRow()->ReturnValue;

        // Handle result
        if ($returnCode === 0) {
            return redirect()->to('/driver-maintenance')->with('success', 'Driver saved successfully');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to save driver');
        }
    }
}
```

### Model Pattern

```php
<?php
namespace App\Models;

use App\Models\BaseModel;

class DriverModel extends BaseModel
{
    public function getDriver(int $driverKey): ?array
    {
        $results = $this->callStoredProcedure('spDriver_Get', [$driverKey]);
        return !empty($results) ? $results[0] : null;
    }

    public function saveDriver(array $data): int
    {
        $params = [
            $data['DriverKey'] ?? 0,
            $data['DriverName'],
            $data['DriverID'],
            // ... more parameters
        ];

        return $this->callStoredProcedureWithReturn('spDriver_Save', $params);
    }

    public function createNewDriver(): int
    {
        // Generate new key from tSurrogateKey
        return $this->getNextKey('Driver');
    }
}
```

**Lazy Model Initialization Pattern:**
Models must be initialized with correct database context. Use helper methods in controllers:

```php
private function getDriverModel(): DriverModel
{
    if (!isset($this->driverModel)) {
        $this->driverModel = new DriverModel();
        $this->driverModel->setDatabase($this->getCustomerDb());
    }
    return $this->driverModel;
}
```

## Current Project Status

### ✅ Phase 1: Foundation Setup (COMPLETE)
- CodeIgniter 4.6.3 installed via Composer
- SQL Server SQLSRV driver configured
- MAMP development environment working
- TLS custom UI theme migrated (app.css)
- Clean URLs configured (.htaccess)
- GitHub repository created

### ✅ Phase 2: Authentication Infrastructure (COMPLETE)
- BaseModel with stored procedure helpers
- TLSAuth library with three-field authentication
- AuthFilter middleware for route protection
- BaseController with automatic database context switching
- Login/Logout controllers and views
- Dashboard with user information display
- Multi-tenant isolation verified and tested
- Clean URLs working (no /public/ exposed)

**Working URLs:**
- http://localhost:8888/tls-ci4/ → Dashboard
- http://localhost:8888/tls-ci4/login
- http://localhost:8888/tls-ci4/dashboard
- http://localhost:8888/tls-ci4/logout

**Test Credentials:**
- **DEMO Database:** chodge, cknox, cscott, dhatfield, egarcia
- **TLSYS Database:** SYSTEM, tlyle, wjohnston

### ✅ Phase 3: MenuManager Migration (COMPLETE)
- Pure MVC architecture - MenuManager returns data arrays only (no HTML generation)
- MenuManager library in `app/Libraries/MenuManager.php`
- Menus configuration in `app/Config/Menus.php` (100+ menu items)
- Navigation bar partial in `app/Views/partials/navbar.php`
- Breadcrumb partial in `app/Views/partials/breadcrumb.php`
- Main layout template in `app/Views/layouts/main.php`
- BaseController integration with automatic menu injection
- Session-based permission caching
- Responsive mobile menu

### ✅ Phase 4: User Maintenance (COMPLETE)
**First entity maintenance example**

- UserModel with direct SQL queries (exception to stored procedure pattern - predates SP standard)
- UserMaintenance Controller with full CRUD operations
- Two-column responsive layout
- TLSAutocomplete component adapted for CI4
- TLSFormTracker for change tracking
- Working URL: http://localhost:8888/tls-ci4/systems/user-maintenance

**Test Database (TEST):**
- testfulluser / abc1234! - Active user with full permissions
- testlimiteduser / abc1234! - Active user with limited permissions
- testnotactive / abc1234! - Inactive user

### ✅ Phase 5: User Security (COMPLETE)
**Menu permission management**

- UserSecurityModel with optimized permission loading
- UserSecurity Controller with AJAX endpoints
- Two-column masonry layout with permission cards
- Category-level toggles
- Role templates (Dispatch, Broker, Accounting)
- Search/filter functionality
- Performance: 2 database operations vs 150+ (75x faster)
- Working URL: http://localhost:8888/tls-ci4/systems/user-security

### ✅ Phase 6: Agent Maintenance (COMPLETE)

**Note:** Agent Maintenance was the original template, but has been superseded by the **Base Entity Template System** (Phase 7).

### ✅ Phase 7: Base Entity Template System (COMPLETE) - **OFFICIAL STANDARD**
**Complete entity maintenance pattern established**

Agent Maintenance serves as the official template for all entity maintenance screens.

**Implementation:**
- AgentModel using stored procedures (spAgent_Get, spAgent_Save)
- AgentMaintenance Controller with full CRUD and business rule validation
- Two-column responsive layout using CI4 layout templates
- AddressModel for address management via junction tables
- ContactModel for contact management with 3-level chain architecture
- CommentModel for comments with user audit trail
- TLSAutocomplete for agent search
- TLSFormTracker for change tracking
- New entity creation flow with immediate dependent object access

**Database Patterns:**
- **Address:** Agent → tAgents_tNameAddress → tNameAddress
- **Contact:** Agent → tAgents_tNameAddress → tNameAddress → tNameAddress_tContact → tContact
- **Comment:** Agent → tAgents_tComment → tComment

**Key Features:**
- EndDate-based active status (not ACTIVE column)
- AJAX operations for dependent objects
- Always-visible "New Agent" button
- Immediate access to address/contacts/comments on new agents
- User audit trails (CommentBy, CommentDate, EditedBy, EditedDate)
- Column name mapping in model layer (DB → UI)

**Working URL:** http://localhost:8888/tls-ci4/safety/agent-maintenance

**Test Database:** CWKI2 contains agents including "KNOW SOLUTIONS, LLC"

**Key Patterns to Replicate:**
1. Two-column responsive layout with CI4 layout templates
2. Lazy model initialization with guaranteed database context
3. AJAX operations for dependent objects without page reload
4. Junction tables for many-to-many relationships
5. User audit trails for comments and changes
6. Change tracking with TLSFormTracker
7. Autocomplete search with TLSAutocomplete
8. Business rule validation server-side with user-friendly messages
9. New entity creation flow with immediate dependent object access
10. Always-visible "New" button with unsaved changes protection

**OFFICIAL STANDARD for all entity maintenance screens.**

The Base Entity Template System provides:
- **BaseEntityMaintenance** abstract controller (707 lines) with all 15 standard endpoints
- **5 reusable view partials** (360 lines) for search, forms, address, contacts, comments
- **Base view template** (140 lines) that auto-generates forms from field definitions
- **Common JavaScript** (640 lines) for all AJAX operations
- **Total core: 1,847 lines written once, inherited by all entities**

**Creating a new entity:**
1. Create child controller (250-450 lines) - extends BaseEntityMaintenance
2. Implement 6 abstract methods (entity name, fields, defaults)
3. Create tiny view wrapper (15 lines) - uses base template
4. Add routes
5. **Done in 15-30 minutes!**

**Benefits:**
- 78% less code per entity (457 lines vs 2,055 lines)
- Zero find/replace needed
- Guaranteed consistency across all entities
- Bug fixes propagate automatically
- Field-driven form generation

**Example:** DriverMaintenance_NEW.php (442 lines) vs old DriverMaintenance.php (955 lines)

**Documentation:**
- Design: `BASE_ENTITY_TEMPLATE_DESIGN.md`
- Progress: `BASE_TEMPLATE_PROGRESS.md`
- Complete: `BASE_TEMPLATE_COMPLETE.md`

**Files:**
- Controller: `app/Controllers/BaseEntityMaintenance.php`
- Partials: `app/Views/partials/entity_*.php` (5 files)
- Base View: `app/Views/safety/base_entity_maintenance.php`
- JavaScript: `public/js/tls-entity-maintenance.js`
- Example: `app/Controllers/DriverMaintenance_NEW.php`

---

### 📋 Next Phase: Migrate to Base Template System
Apply the Base Entity Template to:
- ✅ Driver Maintenance (DriverMaintenance_NEW.php created, ready for testing)
- [ ] Agent Maintenance (refactor to use base template)
- [ ] Owner Maintenance (create using base template as proof-of-concept)
- [ ] Customer Maintenance
- [ ] Unit Maintenance
- [ ] Load Entry
- [ ] Other entity maintenance screens

## Testing

### Test Databases

**Production Databases (DO NOT MODIFY):**
- All standard production databases

**Development Testing Databases:**
- **DEMO**: Contains `spUser_Login`, `spUser_Menus`, active users
- **TLSYS**: Contains all required stored procedures, active users
- **TEST**: Contains test users for User Maintenance testing
- **CWKI2**: Contains agents for Agent Maintenance testing

### Authentication Testing

Comprehensive testing guide available in: `TESTING_AUTHENTICATION.md`

## Development Workflow

1. **Edit code** in `/Users/tonylyle/source/repos/tls-ci4/`
2. **Sync to MAMP** using: `cp -r /Users/tonylyle/source/repos/tls-ci4/* /Applications/MAMP/htdocs/tls-ci4/`
3. **Test** at http://localhost:8888/tls-ci4/
4. **Commit changes** using Git from source location
5. **Push to GitHub**: https://github.com/TLSystemsLLC/TLS-CI4

## Key Differences from tls-web

| Aspect | tls-web (Custom PHP) | tls-ci4 (CodeIgniter 4) |
|--------|---------------------|------------------------|
| **Structure** | Procedural PHP files | MVC framework |
| **Routing** | Direct file access | Route configuration |
| **Authentication** | Auth class | TLSAuth library + AuthFilter |
| **Database** | Database class | BaseModel + Query Builder |
| **Views** | Mixed PHP/HTML | Separate view files |
| **Menu System** | HTML string generation | Pure MVC (data + view partials) |
| **Layout System** | Include header/footer | CI4 view layouts + sections |
| **URL Format** | `/tls/page.php` | `/tls-ci4/page` (clean URLs) |
| **Base URL** | `/tls/` | `/tls-ci4/` |
| **CSRF Protection** | Manual | Automatic (CI4) |
| **Form Validation** | Manual | CI4 validation library |
| **Session Management** | Custom Session class | CI4 session library |

## Critical Reminders

1. **⚠️ ALWAYS edit in source location** (`/Users/tonylyle/source/repos/tls-ci4/`), never MAMP location
2. **⚠️ ALWAYS use stored procedures** - no direct table access (except User Maintenance which predates SP standard)
3. **⚠️ ALWAYS check authentication** with `$this->requireAuth()` in controllers
4. **⚠️ ALWAYS check permissions** with `$this->requireMenuPermission($menuKey)`
5. **⚠️ ALWAYS use `esc()` or `htmlspecialchars()`** when outputting user data in views
6. **⚠️ ALWAYS include CSRF protection** with `<?= csrf_field() ?>` in forms
7. **⚠️ ALWAYS validate input** using CI4's validation library
8. **⚠️ ALWAYS use `base_url()`** for internal links, not hardcoded paths
9. **⚠️ Database context is automatic** - BaseController sets it on every request
10. **⚠️ Use EndDate for active status**, not ACTIVE column

## Lessons Learned

### What Works Well:
1. ✅ CI4's structure prevents common errors
2. ✅ BaseModel pattern enforces stored procedure usage
3. ✅ AuthFilter automatically protects routes
4. ✅ Multi-tenant isolation works seamlessly
5. ✅ CI4 validation prevents input errors
6. ✅ TLS theme integrates perfectly with CI4
7. ✅ Clean URLs achieved with minimal configuration
8. ✅ Pure MVC menu system (data vs presentation separation)
9. ✅ View layouts eliminate header/footer duplication
10. ✅ Session-based permission caching performs well

### Watch Out For:
1. ⚠️ Void return types - use `redirect()->send(); exit;` instead of `return redirect()`
2. ⚠️ Filter registration - must register in `Filters.php` AND apply to routes
3. ⚠️ Two-location workflow - easy to edit wrong location
4. ⚠️ Database context - verify customer DB is set before queries
5. ⚠️ Session data - ensure `customer_db` is always in session
6. ⚠️ **SQL Server OUTPUT parameters** - MUST use `sqlsrv_next_result()` to iterate through result sets
7. ⚠️ **Model database initialization** - Models must initialize `$this->db` in constructor with customer database context

## Contact/References

**Repository:** https://github.com/TLSystemsLLC/TLS-CI4

**Development Environment:**
- MAMP: Apache 2.4.62 + PHP 8.3.14
- Database: SQL Server 2017 at 35.226.40.170:1433
- CodeIgniter: 4.6.3
- Bootstrap: 5.3.0
- Bootstrap Icons: 1.10.0

**Related Projects:**
- Legacy tls-web: `/Applications/MAMP/htdocs/tls/`
- Legacy VB6: `/Users/tonylyle/source/repos/tls/operations/`
- Database schema: `/Users/tonylyle/source/repos/tls/tls-basedb/`
