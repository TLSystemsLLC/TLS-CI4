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

### 5. MenuManager Library (`app/Libraries/MenuManager.php`)
Pure MVC data provider for navigation menus (Phase 3):

**Design Philosophy:**
- MenuManager returns **data arrays only** (no HTML generation)
- Views consume the data and render HTML
- Clean separation of business logic and presentation

**Methods:**
- `getMenuStructure()` - Returns filtered menu tree based on user permissions
- `hasMenuAccess($menuKey)` - Checks if user can access menu (recursive child checking)
- `getBreadcrumbPath($menuKey)` - Returns breadcrumb trail for current page
- `getAccessibleMenuCount()` - Count of visible menu items
- `getAccessibleMenuKeys()` - Array of menu keys user can access

**Permission System:**
- Reads user permissions from session (loaded by TLSAuth at login via `spUser_Menus`)
- Session-based caching for performance (users re-login to see permission changes)
- Automatically hides security permissions (keys starting with 'sec')
- Shows parent categories if user has access to ANY child menu

**Usage Pattern:**
```php
// In BaseController - MenuManager initialized automatically
$this->menuManager = new MenuManager($this->session);

// In Controller - Use renderView() for automatic menu injection
return $this->renderView('dashboard/index', $data);

// Manual data retrieval (if needed)
$menuStructure = $this->menuManager->getMenuStructure();
$breadcrumbs = $this->menuManager->getBreadcrumbPath('mnuDriverMaint');
```

**Returned Data Structure:**
```php
[
    [
        'key' => 'accounting',
        'label' => 'Accounting',
        'icon' => 'bi-calculator',
        'url' => null,
        'hasAccess' => true,
        'hasChildren' => true,
        'items' => [
            [
                'key' => 'mnuCOAMaint',
                'label' => 'Chart of Account Maintenance',
                'url' => 'accounting/coa-maintenance',
                'hasAccess' => true,
                'hasChildren' => false,
                'items' => []
            ]
        ]
    ]
]
```

### 6. Menu Configuration (`app/Config/Menus.php`)
Defines complete menu hierarchy for the application:

- 8 top-level categories: Accounting, Dispatch, Logistics, Imaging, Reports, Safety, Payroll, Systems
- 100+ menu items migrated from legacy system
- Hierarchical structure with unlimited nesting
- Menu keys match database `MenuKey` values from `tMenu` table
- URLs map to CI4 routes

**Structure:**
```php
public array $structure = [
    'menuKey' => [
        'label' => 'Display Text',
        'icon' => 'bi-icon-class',  // Optional Bootstrap icon
        'url' => 'route/path',       // Optional route (for leaf items)
        'items' => [...]             // Optional sub-menus
    ]
];
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

### ✅ Phase 3: MenuManager Migration (COMPLETE)
**Goal:** Migrate MenuManager from tls-web to CI4 library using Pure MVC Architecture

**Implementation:**
- ✅ **Pure MVC Architecture** - MenuManager returns data arrays only (no HTML generation)
- ✅ MenuManager library in `app/Libraries/MenuManager.php` (data provider)
- ✅ Menus configuration in `app/Config/Menus.php` (100+ menu items)
- ✅ Navigation bar partial in `app/Views/partials/navbar.php` (Bootstrap 5)
- ✅ Breadcrumb partial in `app/Views/partials/breadcrumb.php`
- ✅ Main layout template in `app/Views/layouts/main.php`
- ✅ BaseController integration with automatic menu injection
- ✅ Session-based permission caching (from `spUser_Menus`)
- ✅ Recursive child menu access checking
- ✅ Security permission filtering ('sec' prefix hidden)
- ✅ Responsive mobile menu (hamburger)
- ✅ User dropdown with company/database info

**Key Features:**
```php
// MenuManager returns filtered data structure
public function getMenuStructure(): array;  // Filtered menu tree
public function hasMenuAccess(string $key): bool;  // Permission check
public function getBreadcrumbPath(string $menu): array;  // Breadcrumbs

// BaseController provides helper methods
protected function renderView(string $name, array $data = []): string;  // Auto-inject menus
protected function prepareViewData(array $data = []): array;  // Get menu data

// Views consume data (pure HTML rendering)
<?= $this->extend('layouts/main') ?>  // Use layout template
<?= view('partials/navbar') ?>  // Render navigation
```

**Architecture Benefits:**
- ✅ Clean separation: Data (Library) vs Presentation (Views)
- ✅ No HTML string concatenation in PHP
- ✅ Easy to modify navigation styling
- ✅ Testable business logic
- ✅ Reusable view components
- ✅ True CI4 MVC patterns

### ✅ Phase 4: User Maintenance (COMPLETE)
**Goal:** Build User Maintenance screen as first entity maintenance example

**Implementation:**
- ✅ **UserModel** (`app/Models/UserModel.php`) - Uses direct SQL queries (exception to stored procedure pattern)
- ✅ **UserMaintenance Controller** (`app/Controllers/UserMaintenance.php`) - Full CRUD operations
- ✅ **User Maintenance View** (`app/Views/systems/user_maintenance.php`) - Two-column responsive layout
- ✅ **TLS Autocomplete** (`public/js/tls-autocomplete.js`) - Adapted for CI4 routes
- ✅ **TLS Form Tracker** (`public/js/tls-form-tracker.js`) - Change tracking and validation
- ✅ Routes configured in `app/Config/Routes.php` under 'systems' group

**Key Features:**
```php
// UserModel - Direct SQL exception to standard pattern
public function searchUser(string $searchTerm): ?array;  // Search by UserID or UserName
public function createUser(array $data): bool;  // Create new user
public function updateUser(string $userId, array $data): bool;  // Update existing
public function searchUsersForAutocomplete(string $term, bool $includeInactive): array;
public function getLookupTables(): array;  // Dropdown data

// UserMaintenance Controller - Full CRUD workflow
public function index();  // Display form
public function search();  // Search and load user
public function save();  // Create or update user
public function load(string $userId);  // Load by UserID
public function autocomplete();  // API endpoint for autocomplete
```

**Notable Patterns:**
- **Exception to Standard:** User Maintenance uses direct SQL queries instead of stored procedures (predates SP standard)
- **Lazy Model Initialization:** UserModel initialized with correct database context via `getUserModel()` helper
- **Two-Column Layout:** Responsive design with left/right column sections
- **Form Tracking:** Real-time change detection with unsaved changes counter
- **Autocomplete:** Dropdown search with keyboard navigation and inactive user filtering
- **CI4 Validation:** Built-in validation with error display
- **Flash Messages:** Session-based success/error notifications

**Completed Testing:**
- ✅ Search and load users (by UserID and UserName)
- ✅ Autocomplete with partial matches
- ✅ Create new users with validation
- ✅ Update existing users
- ✅ Include/exclude inactive users filter
- ✅ Change tracking with unsaved changes warning
- ✅ Form reset functionality
- ✅ Multi-tenant database context switching

**Test Credentials (TEST Database):**
- **testfulluser** / abc1234! - Active user with full permissions
- **testlimiteduser** / abc1234! - Active user with limited permissions
- **testnotactive** / abc1234! - Inactive user (shows only with "Include Inactive" checked)

**Working URL:**
- http://localhost:8888/tls-ci4/systems/user-maintenance

### ✅ Phase 5: User Security (COMPLETE)
**Goal:** Build User Security management to grant/deny menu permissions per user

**Implementation:**
- ✅ **UserSecurityModel** (`app/Models/UserSecurityModel.php`) - Optimized permission loading using stored procedures
- ✅ **UserSecurity Controller** (`app/Controllers/UserSecurity.php`) - AJAX endpoints for loading/saving permissions
- ✅ **User Security View** (`app/Views/systems/user_security.php`) - Two-column masonry layout with permission cards
- ✅ Routes configured in `app/Config/Routes.php` under 'systems' group

**Key Features:**
```php
// UserSecurityModel - Optimized approach
public function getAllSecurityItems(): array;  // SELECT DISTINCT from tSecurity
public function getUserPermissions($userId): array;  // Uses spUser_Menus (1 call vs 150+)
public function savePermission($userId, $menuKey, $granted): bool;  // spUser_Menu_Save
public function savePermissionChanges($userId, $changes): int;  // Batch save changes
public function getRoleTemplate($role): array;  // Get permissions from tSecurityGroups
public function organizePermissionsByCategory(): array;  // Group by Menus config

// UserSecurity Controller - AJAX endpoints
public function index();  // Display main page
public function getUserPermissions();  // AJAX: Load user permissions
public function savePermissions();  // AJAX: Save permission changes
public function applyRoleTemplate();  // AJAX: Apply role template
```

**Performance Optimization:**
- **Before:** 1 query + 150+ `spUser_Menu` calls = 151+ database operations
- **After:** 1 DISTINCT query + 1 `spUser_Menus` call = 2 database operations
- **Result:** ~75x faster initial load

**Notable Features:**
- ✅ User selection dropdown (from `spUsers_GetAll`)
- ✅ Permission grid organized by categories (Accounting, Dispatch, Safety, Systems, etc.)
- ✅ Toggle switches for each permission
- ✅ **Category-level toggle** - Check/uncheck all permissions in a card (NEW enhancement)
- ✅ Category checkbox shows three states: all checked, all unchecked, indeterminate (some checked)
- ✅ Change tracking - only saves modified permissions
- ✅ Bulk actions (Grant All Visible, Deny All Visible)
- ✅ Role templates (Dispatch, Broker, Accounting) from tSecurityGroups
- ✅ Search/filter permissions with real-time filtering
- ✅ Permission summary stats (X granted, Y denied, Z total)
- ✅ Category stats showing granted/total per card
- ✅ Collapse/expand individual categories
- ✅ Unsaved changes warning with change counter
- ✅ AJAX for loading/saving without page reload
- ✅ True masonry layout - cards pack naturally without white space

**Completed Testing:**
- ✅ User selection and permission loading (fast!)
- ✅ Individual permission toggles with change tracking
- ✅ Category toggle all (grant/deny entire card)
- ✅ Save permission changes
- ✅ Role template application
- ✅ Bulk actions (grant all, deny all)
- ✅ Search/filter functionality
- ✅ Unsaved changes warning
- ✅ Multi-tenant database context

**Working URL:**
- http://localhost:8888/tls-ci4/systems/user-security

### ✅ Phase 6: Agent Maintenance - Step 1 (COMPLETE)
**Goal:** Build Agent Maintenance as template for entity maintenance screens

**Implementation:**
- ✅ **AgentModel** (`app/Models/AgentModel.php`) - Uses stored procedures (spAgent_Get, spAgent_Save)
- ✅ **AgentMaintenance Controller** (`app/Controllers/AgentMaintenance.php`) - Full CRUD with business rule validation
- ✅ **Agent Maintenance View** (`app/Views/safety/agent_maintenance.php`) - Two-column responsive layout using CI4 layout template
- ✅ Routes configured in `app/Config/Routes.php` under 'safety' group
- ✅ TLSAutocomplete updated for agents API type

**Key Features:**
```php
// AgentModel - Standard stored procedure pattern
public function getAgent(int $agentKey): ?array;  // spAgent_Get
public function saveAgent(array $agentData): bool;  // spAgent_Save (17 parameters)
public function searchAgentByName(string $name): ?array;  // Direct SQL with EndDate filter
public function searchAgentsForAutocomplete(string $term, bool $includeInactive): array;

// AgentMaintenance Controller - CRUD with business rules
public function index();  // Display form
public function search();  // Search and load agent
public function save();  // Create or update with validation
public function load(int $agentKey);  // Load by AgentKey
public function autocomplete();  // API endpoint for autocomplete
```

**Critical Business Rule Implemented:**
- Active = 1 (checked) requires EndDate = empty or '1899-12-30'
- Active = 0 (unchecked) requires EndDate = real date
- Server-side validation with user-friendly error messages

**Form Sections:**
- ✅ **Basic Information:** AgentKey, Name, Start/End dates, Active checkbox, Email, Division
- ✅ **Pay Information:** Broker%, Fleet%, Company%, Full Freight Pay checkbox
- ✅ **Tax/ID Information:** PII-protected section with show/hide, SSN/EIN formatting
- 📋 **Future:** Address (Step 2), Comments (Step 3), Contacts (Step 4)

**Notable Patterns:**
- **EndDate-based Active Status:** Queries filter by EndDate, not ACTIVE column
- **ACTIVE Column Maintenance:** Auto-maintained during save based on validation
- **CI4 Layout Template:** Uses `$this->extend('layouts/main')` with sections (proper pattern)
- **Lazy Model Initialization:** AgentModel initialized with correct database context
- **Form Tracking:** TLSFormTracker with change counter (`id="tls-change-counter"`)
- **Autocomplete:** Reusable TLSAutocomplete component with 'agents' type
- **Flash Messages:** Session-based success/error notifications

**Completed Testing:**
- ✅ Search and load agents (by AgentKey and Name)
- ✅ Autocomplete with partial matches
- ✅ Change tracking with unsaved changes counter
- ✅ Form validation
- ✅ Business rule enforcement (Active/EndDate relationship)
- ✅ Multi-tenant database context

**Working URL:**
- http://localhost:8888/tls-ci4/safety/agent-maintenance

**Test Database:**
- **CWKI2:** Contains agents including "KNOW SOLUTIONS, LLC"

### ✅ Phase 6 - Step 2: Agent Address Management (COMPLETE)
**Goal:** Add address management to Agent Maintenance

**Implementation:**
- ✅ **AddressModel** (`app/Models/AddressModel.php`) - Address CRUD using stored procedures
- ✅ **AgentModel Extended** - Added `getAgentAddress()` method
- ✅ **AgentMaintenance Controller Extended** - Added getAddress() and saveAddress() AJAX endpoints
- ✅ **Agent Maintenance View Extended** - Added address display/edit UI in right column
- ✅ Routes extended in `app/Config/Routes.php` for address endpoints
- ✅ **CRITICAL FIX:** BaseModel `callStoredProcedureWithReturn()` now uses `sqlsrv_next_result()` to properly iterate through result sets

**Key Features:**
```php
// AddressModel - Standard stored procedure pattern
public function getAddress(int $nameKey): ?array;  // spNameAddress_Get
public function saveAddress(array $addressData): int;  // spNameAddress_Save (15 params), returns NameKey
public function getAgentAddresses(int $agentKey): array;  // spAgentNameAddresses_Get
public function linkAgentAddress(int $agentKey, int $nameKey): bool;  // spAgentNameAddresses_Save
public function createBlankAddress(string $nameQual = 'AG'): int;  // Create empty address

// AgentModel - Address integration
public function getAgentAddress(int $agentKey): ?array;  // Get first address via junction table

// AgentMaintenance Controller - AJAX endpoints
public function getAddress();  // AJAX: Load address for agent
public function saveAddress();  // AJAX: Save address and link to agent
```

**Database Structure:**
- **tNameAddress** - Stores all addresses (NameKey as primary key)
- **tAgents_tNameAddress** - Junction table linking agents to addresses (many-to-many)
- **Stored Procedures:**
  - `spNameAddress_Get` - Get address by NameKey
  - `spNameAddress_Save` - Save/update address, returns standard return code (0 = success)
  - `spAgentNameAddresses_Get` - Get NameKeys for an agent
  - `spAgentNameAddresses_Save` - Link address to agent

**UI Features:**
- Display mode shows formatted address with "Edit Address" button
- Edit mode with inline form (Name1, Name2, Address lines, City, State, ZIP, Phone)
- AJAX save without page reload
- Automatic address creation for new agents (blank address linked on save)
- Change tracking with cancel confirmation

**CRITICAL FIX - BaseModel.callStoredProcedureWithReturn():**
The method now properly handles SQL Server's multi-result set behavior using native SQLSRV functions:
```php
// Uses sqlsrv_query() instead of CI4's query() method
$stmt = sqlsrv_query($conn, $sql, $parameters);

// Iterates through result sets using sqlsrv_next_result()
do {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $rows[] = $row;
    }
    if (!empty($rows)) {
        $result = $rows[0]; // Keep last non-empty result
    }
} while (sqlsrv_next_result($stmt));
```

**Why This Fix Was Needed:**
- CI4's Query object doesn't expose `nextResultSet()` functionality
- SQL Server returns the `SELECT @ReturnValue` as a separate result set
- Without `sqlsrv_next_result()`, `getRowArray()` returned NULL
- This pattern mirrors tls-web's `executeStoredProcedureWithReturn()` using PDO

**Completed Testing:**
- ✅ Address loading via AJAX
- ✅ Address saving with proper success/error messages
- ✅ Return code properly captured (0 = success)
- ✅ Blank address auto-creation for new agents
- ✅ Junction table linking verified

**Notable Pattern:**
- **Lazy Model Initialization:** AddressModel initialized with guaranteed database context via `getAddressModel()` helper
- **AJAX Pattern:** Display/edit modes without page reload
- **Return Code Handling:** Uses BaseModel constants (SRV_NORMAL = 0) with human-readable messages

### ✅ Phase 6 - Step 3: Agent Contact Management (COMPLETE)
**Goal:** Add contact management to Agent Maintenance using 3-level chain architecture

**Implementation:**
- ✅ **ContactModel** (`app/Models/ContactModel.php`) - Contact CRUD using stored procedures with column name mapping
- ✅ **AgentModel Extended** - Added `getAgentContacts()` method with 3-level chain retrieval
- ✅ **AgentMaintenance Controller Extended** - Added getContacts(), saveContact(), deleteContact() AJAX endpoints
- ✅ **Agent Maintenance View Extended** - Added contacts table, Bootstrap modal, and JavaScript functions
- ✅ Routes extended in `app/Config/Routes.php` for contact endpoints

**Key Features:**
```php
// ContactModel - Standard stored procedure pattern with column mapping
public function getContact(int $contactKey): ?array;  // spContact_Get with DB→UI column mapping
public function getContactKeysForNameKey(int $nameKey): array;  // spContacts_Get
public function saveContact(array $contactData, int $entityKey): int;  // spContact_Save, returns ContactKey
public function deleteContact(int $contactKey): bool;  // spContact_Delete

// AgentModel - 3-level chain retrieval
public function getAgentContacts(int $agentKey): array;  // Agent → NameAddress → Contact chain

// AgentMaintenance Controller - AJAX endpoints
public function getContacts();  // AJAX: Load contacts for agent
public function saveContact();  // AJAX: Save contact (create or update)
public function deleteContact();  // AJAX: Delete contact
```

**Database Structure - 3-Level Chain:**
- **Agent** → (via tAgents_tNameAddress) → **NameAddress** → (via tNameAddress_tContact) → **Contact**
- **tContact** - Stores all contacts (ContactKey as primary key)
- **tNameAddress_tContact** - Junction table linking addresses to contacts (many-to-many)
- **Stored Procedures:**
  - `spContact_Get` - Get contact by ContactKey
  - `spContact_Save` - Save/update contact (9 parameters), returns standard return code (0 = success)
  - `spContact_Delete` - Delete contact, returns standard return code
  - `spContacts_Get` - Get ContactKeys for a NameKey

**CRITICAL Column Name Mapping (DB → UI):**
ContactModel.getContact() maps database column names to UI-friendly names:
- **TelephoneNo** → **Phone**
- **CellNo** → **Mobile**
- **ContactFunction** → **Relationship**

This mapping happens in the model layer to maintain clean separation.

**UI Features:**
- Contacts table displays: Name, Phone, Mobile, Relationship, Actions
- "Add Contact" button opens Bootstrap modal
- Modal fields: First Name, Last Name, Phone, Mobile, Email, Relationship, Primary Contact checkbox
- Edit/Delete buttons for each contact
- AJAX operations without page reload
- Contact count badge updates automatically
- Change tracking and unsaved changes warning

**JavaScript Functions:**
```javascript
loadAgentContacts();     // Load contacts for current agent
displayContacts(contacts);  // Render contacts table
showContactModal();      // Open modal for new contact
editContact(contactKey); // Load contact into modal for editing
saveContact();           // Save contact via AJAX
deleteContact(contactKey); // Delete contact with confirmation
```

**Completed Testing:**
- ✅ Contact loading via AJAX (3-level chain retrieval)
- ✅ Contact display with proper column mapping
- ✅ Add new contact with modal form
- ✅ Edit existing contact
- ✅ Delete contact with confirmation
- ✅ Primary contact checkbox handling
- ✅ Return codes properly captured (0 = success)

**Notable Pattern:**
- **3-Level Chain Architecture:** Agent → tAgents_tNameAddress → tNameAddress → tNameAddress_tContact → tContact
- **Lazy Model Initialization:** ContactModel initialized with guaranteed database context via `getContactModel()` helper
- **Column Name Mapping:** Database columns mapped to UI-friendly names in model layer (not controller or view)
- **AJAX Pattern:** Same pattern as address management for consistency
- **Return Code Handling:** Uses `callStoredProcedureWithReturn()` for save/delete operations

### ✅ Phase 6 - Step 4: Agent Comment Management (COMPLETE)
**Goal:** Add comment management to Agent Maintenance

**Implementation:**
- ✅ **CommentModel** (`app/Models/CommentModel.php`) - Comment CRUD using stored procedures with user audit trail
- ✅ **AgentModel Extended** - Added `getAgentComments()` method
- ✅ **AgentMaintenance Controller Extended** - Added getComments(), saveComment(), deleteComment() AJAX endpoints
- ✅ **Agent Maintenance View Extended** - Added comments section, Bootstrap modal, and JavaScript functions
- ✅ Routes extended in `app/Config/Routes.php` for comment endpoints

**Key Features:**
```php
// CommentModel - Standard stored procedure pattern with audit trail
public function getComment(int $commentKey): ?array;  // spComment_Get (includes CommentBy, CommentDate, EditedBy, EditedDate)
public function getCommentKeysForAgent(int $agentKey): array;  // spAgentComments_Get
public function saveComment(array $commentData, int $agentKey): int;  // spComment_Save, returns CommentKey
public function deleteComment(int $commentKey): bool;  // spComment_Delete

// AgentModel - Comment retrieval
public function getAgentComments(int $agentKey): array;  // Get all comments for agent

// AgentMaintenance Controller - AJAX endpoints
public function getComments();  // AJAX: Load comments for agent
public function saveComment();  // AJAX: Save comment (create or update)
public function deleteComment();  // AJAX: Delete comment
```

**Database Structure:**
- **tComment** - Stores comments (CommentKey, Comment, CommentBy, CommentDate, EditedBy, EditedDate)
- **tAgents_tComment** - Junction table linking agents to comments (many-to-many)
- **Stored Procedures:**
  - `spComment_Get` - Get comment by CommentKey with full audit trail
  - `spComment_Save` - Save/update comment (3 parameters: @Key, @Comment, @User)
  - `spComment_Delete` - Delete comment
  - `spAgentComments_Get` - Get CommentKeys for an agent
  - `spAgentComments_Save` - Link comment to agent

**UI Features:**
- Full-width comments section below agent/address/contacts
- Comments display with user audit trail (CommentBy, CommentDate, EditedBy, EditedDate)
- "Add Comment" button opens Bootstrap modal
- Modal with textarea (255 character limit)
- Edit/Delete buttons for each comment
- AJAX operations without page reload
- Auto-loads comments when agent is loaded

**JavaScript Functions:**
```javascript
loadAgentComments();       // Load comments for current agent
displayComments(comments); // Render comments list
showCommentModal();        // Open modal for new comment
editComment(commentKey);   // Load comment into modal for editing
saveComment();             // Save comment via AJAX
deleteComment(commentKey); // Delete comment with confirmation
```

**Completed Testing:**
- ✅ Comment loading via AJAX
- ✅ Add new comment with user audit
- ✅ Edit existing comment
- ✅ Delete comment with confirmation
- ✅ User and timestamp tracking verified
- ✅ Only new comments linked via junction table

**Notable Pattern:**
- **User Audit Trail:** Automatically captures UserID from session for CommentBy/EditedBy
- **Lazy Model Initialization:** CommentModel initialized with guaranteed database context
- **AJAX Pattern:** Consistent with address and contact management

### ✅ Phase 6 - Final Enhancement: New Agent Creation Flow (COMPLETE)
**Goal:** Allow creating new agents with immediate access to dependent objects (Address, Contacts, Comments)

**Problem Solved:**
- Previously, AgentKey = 0 until save, preventing dependent objects from being added via AJAX
- Users couldn't add address/contacts/comments until after saving the agent

**Implementation:**
- ✅ Added `createNewAgent()` endpoint in AgentMaintenance controller
- ✅ Updated `newAgent()` JavaScript with two-step confirmation
- ✅ Auto-create blank address for new agents
- ✅ Modified page header to always show "New Agent" button
- ✅ Added unsaved changes warning before creating new agent

**Key Features:**
```php
// AgentMaintenance Controller - New agent creation
public function createNewAgent();  // Creates minimal agent, returns AgentKey
```

**UI/UX Flow:**
1. User clicks "New Agent" → First confirmation: "Are you sure you want to create a new agent? This will reserve an Agent Key in the database."
2. If user has unsaved changes → Second confirmation: "You have unsaved changes. Are you sure? All unsaved changes will be lost."
3. AJAX creates minimal agent record with defaults (Name = "New Agent")
4. Auto-creates blank address and links to agent
5. Redirects to load newly created agent
6. User can immediately add contacts, comments, and edit all fields
7. User fills out agent details and saves

**Page Header Layout:**
- **No agent loaded:** `[New Agent]`
- **Agent loaded:** `[New Agent] [Save Agent] [Reset]`
- "New Agent" button is always visible using `d-flex gap-2` for spacing

**Completed Testing:**
- ✅ New agent creation with confirmation
- ✅ Blank address auto-creation
- ✅ Immediate access to dependent objects
- ✅ Unsaved changes warning when switching agents
- ✅ "New Agent" button always visible

**Critical Fix - spAgent_Save:**
- Removed lines 107-108 (problematic `IF @@ROWCOUNT = 0 RETURN 97;` after tAgentRotation UPDATE)
- The tAgentRotation UPDATE is conditional and may legitimately not execute
- Removed the check to prevent false error code 97 when save actually succeeded

### ✅ Phase 6 - COMPLETE
**Agent Maintenance is now the official template for all entity maintenance screens**

All features implemented:
- ✅ Step 1: Core Agent CRUD with business rule validation
- ✅ Step 2: Address Management with AJAX
- ✅ Step 3: Contact Management with 3-level chain architecture
- ✅ Step 4: Comment Management with user audit trail
- ✅ New Agent Creation Flow with immediate dependent object access
- ✅ Always-visible "New Agent" button with unsaved changes protection

**Pattern Established:**
This Agent Maintenance implementation serves as the complete template for:
- Driver Maintenance
- Owner Maintenance
- Customer Maintenance
- Unit Maintenance
- Any other entity maintenance screens

**Key Patterns to Replicate:**
1. **Two-column responsive layout** with CI4 layout templates
2. **Lazy model initialization** with guaranteed database context
3. **AJAX operations** for dependent objects without page reload
4. **Junction tables** for many-to-many relationships
5. **User audit trails** for comments and changes
6. **Change tracking** with TLSFormTracker
7. **Autocomplete search** with TLSAutocomplete
8. **Business rule validation** server-side with user-friendly messages
9. **New entity creation flow** with immediate dependent object access
10. **Always-visible "New" button** with unsaved changes protection

**Next Phase:** Apply this established pattern to other entity maintenance screens

## Testing

### Test Databases

**Production Databases (DO NOT MODIFY):**
- All standard production databases

**Development Testing Databases:**
- **DEMO**: Contains `spUser_Login`, `spUser_Menus`, active users (chodge, cknox, cscott, dhatfield, egarcia)
- **TLSYS**: Contains all required stored procedures, active users (SYSTEM, tlyle, wjohnston)
- **TEST**: Contains test users for User Maintenance testing (testfulluser, testlimiteduser, testnotactive)

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
| **Menu System** | HTML string generation | Pure MVC (data + view partials) |
| **Layout System** | Include header/footer | CI4 view layouts + sections |
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
7. ⚠️ **Model database initialization** - Models must initialize `$this->db` in constructor with customer database context from session

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
