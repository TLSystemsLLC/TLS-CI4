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

### 2. TLSAuth Library (`app/Libraries/TLSAuth.php`)
Custom authentication library (not using CI4's built-in auth):

**Methods:**
- `login($customer, $userId, $password)` - Three-field authentication with database validation
- `isLoggedIn()` - Session validation with timeout
- `hasMenuAccess($menuKey)` - Real-time permission checking via spUser_Menu
- `getCurrentUser()` - Get user data from session
- `logout()` - Destroy session
- `isValidCustomerId($customerId)` - Validate against spGetOperationsDB in master database

**Critical Pattern:**
```php
// ALWAYS validate customer database before switching
if (!$this->isValidCustomerId($customer)) {
    return ['success' => false, 'message' => 'Invalid customer ID specified'];
}

// Switch to customer database for all operations
$this->db->setDatabase($customer);
```

### 3. AuthFilter (`app/Filters/AuthFilter.php`)
Middleware for route protection:

```php
// Apply to routes in app/Config/Routes.php
$routes->get('/dashboard', 'Dashboard::index', ['filter' => 'auth']);
```

- Automatically redirects to login if not authenticated
- Stores intended URL for redirect after successful login

### 4. BaseController (`app/Controllers/BaseController.php`)
All controllers extend this with built-in auth helpers:

**Helper Methods:**
- `requireAuth()` - Require authentication or redirect
- `hasMenuAccess($menuKey)` - Check menu permission
- `requireMenuPermission($menuKey)` - Enforce permission or return 403
- `getCurrentUser()` - Get current user from session
- `getCurrentDatabase()` - Get customer database name
- `getCustomerDb()` - Get DB connection with guaranteed tenant context

**Usage Pattern:**
```php
class DriverMaintenance extends BaseController
{
    public function index()
    {
        // Authentication and permission checking
        $this->requireAuth();
        $this->requireMenuPermission('mnuDriverMaint');

        // Database automatically set to customer's database
        $db = $this->getCustomerDb();

        // All stored procedures execute in correct tenant database
        $drivers = $db->query('EXEC spDriver_Get ?', [$driverKey])->getResultArray();

        return view('safety/driver_maintenance', $data);
    }
}
```

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

### Clean URLs Configuration

**Root .htaccess:**
```apache
# Redirect all requests to public folder
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_URI} !^/tls-ci4/public/
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

**Result:** Production-ready URLs without /public/ visible:
- ✅ http://localhost:8888/tls-ci4/
- ✅ http://localhost:8888/tls-ci4/login
- ✅ http://localhost:8888/tls-ci4/dashboard
- ❌ ~~http://localhost:8888/tls-ci4/public/login~~ (old)

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
- `app/Views/dispatch/load_entry.php`

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

### Standardized Page Structure

```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle ?? 'TLS Operations') ?></title>

    <!-- REQUIRED CSS includes -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= base_url('css/app.css') ?>" rel="stylesheet">
</head>
<body>
    <!-- REQUIRED: Main Navigation (once MenuManager is migrated) -->
    <?= $menuHtml ?? '' ?>

    <div class="container-fluid">
        <!-- REQUIRED: Standardized Page Header -->
        <div class="tls-page-header">
            <h1 class="tls-page-title">Page Title</h1>
            <div class="tls-top-actions">
                <button type="button" class="btn tls-btn-primary">Save</button>
                <button type="button" class="btn tls-btn-secondary">Cancel</button>
            </div>
        </div>

        <!-- REQUIRED: Use standardized form cards -->
        <div class="tls-form-card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-icon-name me-2"></i>Section Title
                </h5>
            </div>
            <div class="card-body">
                <!-- Page content here -->
            </div>
        </div>
    </div>

    <!-- REQUIRED: Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
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

## CodeIgniter 4 Patterns

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
        // 1. Require authentication
        $this->requireAuth();
        $this->requireMenuPermission('mnuDriverMaint');

        // 2. Validate input (CI4 validation)
        $validation = \Config\Services::validation();
        $validation->setRules([
            'driver_name' => 'required|min_length[3]',
            'driver_id' => 'required|alpha_numeric'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        // 3. Get validated data
        $driverName = $this->request->getPost('driver_name');
        $driverId = $this->request->getPost('driver_id');

        // 4. Execute stored procedure
        $db = $this->getCustomerDb();
        $returnCode = $db->query('DECLARE @RC INT; EXEC @RC = spDriver_Save ?, ?; SELECT @RC AS ReturnValue',
            [$driverName, $driverId])->getRow()->ReturnValue;

        // 5. Handle result
        if ($returnCode === 0) {
            return redirect()->to('/driver-maintenance')->with('success', 'Driver saved successfully');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to save driver');
        }
    }
}
```

### View Pattern (Using CI4 View Helpers)

```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= esc($pageTitle) ?></title>
    <link href="<?= base_url('css/app.css') ?>" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <!-- Display flash messages -->
        <?php if (session()->has('success')): ?>
            <div class="alert alert-success"><?= esc(session('success')) ?></div>
        <?php endif; ?>

        <?php if (session()->has('error')): ?>
            <div class="alert alert-danger"><?= esc(session('error')) ?></div>
        <?php endif; ?>

        <!-- Display validation errors -->
        <?php if (session()->has('errors')): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach (session('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Form with CI4 CSRF protection -->
        <form method="POST" action="<?= base_url('driver-maintenance/save') ?>">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label for="driver_name" class="form-label">Driver Name</label>
                <input type="text" class="form-control" id="driver_name" name="driver_name"
                       value="<?= esc(old('driver_name', $driver['DriverName'] ?? '')) ?>">
            </div>

            <button type="submit" class="btn tls-btn-primary">Save</button>
        </form>
    </div>
</body>
</html>
```

## Database Operations

### Using BaseModel Pattern

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

### Direct Database Query (Alternative)

```php
// In controller
$db = $this->getCustomerDb();

// Simple query
$results = $db->query('EXEC spDriver_Get ?', [$driverKey])->getResultArray();

// With return code
$query = $db->query('DECLARE @RC INT; EXEC @RC = spDriver_Save ?, ?; SELECT @RC AS ReturnValue',
    [$driverName, $driverId]);
$returnCode = $query->getRow()->ReturnValue;
```

## Routing

### Route Definition (`app/Config/Routes.php`)

```php
<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Root redirects to dashboard (with auth filter)
$routes->get('/', 'Dashboard::index', ['filter' => 'auth']);

// Authentication routes (no filter needed)
$routes->get('/login', 'Login::index');
$routes->post('/login/attempt', 'Login::attempt');
$routes->get('/logout', 'Login::logout');

// Protected routes (require auth filter)
$routes->get('/dashboard', 'Dashboard::index', ['filter' => 'auth']);
$routes->get('/driver-maintenance', 'DriverMaintenance::index', ['filter' => 'auth']);
$routes->post('/driver-maintenance/save', 'DriverMaintenance::save', ['filter' => 'auth']);

// Route groups for organization
$routes->group('safety', ['filter' => 'auth'], function($routes) {
    $routes->get('driver-maintenance', 'DriverMaintenance::index');
    $routes->post('driver-maintenance/save', 'DriverMaintenance::save');
});
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
- Comprehensive testing documentation created

**Working URLs:**
- http://localhost:8888/tls-ci4/ → Dashboard (redirects to login if not authenticated)
- http://localhost:8888/tls-ci4/login
- http://localhost:8888/tls-ci4/dashboard
- http://localhost:8888/tls-ci4/logout

**Test Credentials:**
- **DEMO Database:** chodge, cknox, cscott, dhatfield, egarcia
- **TLSYS Database:** SYSTEM, tlyle, wjohnston

### 🔄 Phase 3: MenuManager Migration (NEXT)
**Goal:** Migrate MenuManager from tls-web to CI4 library

**Tasks:**
1. Create MenuManager library in `app/Libraries/MenuManager.php`
2. Port menu generation logic from tls-web
3. Use `spUser_Menus` to get user permissions
4. Generate responsive Bootstrap 5 navigation
5. Filter menus based on user permissions
6. Hide 'sec' prefixed security permissions
7. Integrate into dashboard and all future pages

**Files to Reference:**
- `/Applications/MAMP/htdocs/tls/classes/MenuManager.php` (existing)
- `/Applications/MAMP/htdocs/tls/config/menus.php` (menu structure)

### 📋 Phase 4: Entity Maintenance Screens (PLANNED)
**Goal:** Build first entity maintenance screen using CI4 patterns

**Suggested Start:** Driver Maintenance (proof-of-concept)
1. Create DriverMaintenance controller
2. Build search interface with autocomplete
3. Implement CRUD operations via stored procedures
4. Apply standardized UI theme
5. Form validation with CI4
6. Change tracking with TLSFormTracker
7. Test complete workflow

**Reference Files:**
- `/Applications/MAMP/htdocs/tls/safety/driver-maintenance.php` (existing PHP version)
- VB6 form: `/Users/tonylyle/source/repos/tls/operations/frmDriverMaintenance.frm`

## Testing

### Test Databases

**Production Databases (DO NOT MODIFY):**
- All standard production databases

**Development Testing Databases:**
- **DEMO**: Contains `spUser_Login`, `spUser_Menus`, active users (chodge, cknox, cscott, dhatfield, egarcia)
- **TLSYS**: Contains all required stored procedures, active users (SYSTEM, tlyle, wjohnston)

### Authentication Testing

Comprehensive testing guide available in: `TESTING_AUTHENTICATION.md`

**Test Scenarios:**
1. Basic authentication flow
2. Multi-tenant isolation (DEMO vs TLSYS)
3. Form validation
4. Session timeout
5. Permission checking
6. Clean URL verification
7. Browser compatibility

## Documentation

**Project Documentation:**
- `README.md` - Project overview and installation
- `MULTI_TENANT.md` - Multi-tenant architecture details
- `TESTING_AUTHENTICATION.md` - Testing procedures
- `SESSION_SUMMARY.md` - Development session notes
- `CLAUDE.md` - This file

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
| **URL Format** | `/tls/page.php` | `/tls-ci4/page` (clean URLs) |
| **Base URL** | `/tls/` | `/tls-ci4/` |
| **CSRF Protection** | Manual | Automatic (CI4) |
| **Form Validation** | Manual | CI4 validation library |
| **Session Management** | Custom Session class | CI4 session library |
| **Error Handling** | Custom | CI4 exception handling |

## Critical Reminders

1. **⚠️ ALWAYS edit in source location** (`/Users/tonylyle/source/repos/tls-ci4/`), never MAMP location
2. **⚠️ ALWAYS use stored procedures** - no direct table access
3. **⚠️ ALWAYS check authentication** with `$this->requireAuth()` in controllers
4. **⚠️ ALWAYS check permissions** with `$this->requireMenuPermission($menuKey)`
5. **⚠️ ALWAYS use `esc()` or `htmlspecialchars()`** when outputting user data in views
6. **⚠️ ALWAYS include CSRF protection** with `<?= csrf_field() ?>` in forms
7. **⚠️ ALWAYS validate input** using CI4's validation library
8. **⚠️ ALWAYS use `base_url()`** for internal links, not hardcoded paths
9. **⚠️ Database context is automatic** - BaseController sets it on every request

## Lessons Learned

### What Works Well:
1. ✅ CI4's structure prevents common errors
2. ✅ BaseModel pattern enforces stored procedure usage
3. ✅ AuthFilter automatically protects routes
4. ✅ Multi-tenant isolation works seamlessly
5. ✅ CI4 validation prevents input errors
6. ✅ TLS theme integrates perfectly with CI4
7. ✅ Clean URLs achieved with minimal configuration

### Watch Out For:
1. ⚠️ Void return types - use `redirect()->send(); exit;` instead of `return redirect()`
2. ⚠️ Filter registration - must register in `Filters.php` AND apply to routes
3. ⚠️ Two-location workflow - easy to edit wrong location
4. ⚠️ Database context - verify customer DB is set before queries
5. ⚠️ Session data - ensure `customer_db` is always in session

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
