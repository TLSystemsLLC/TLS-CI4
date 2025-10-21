# Company & Division Maintenance - Implementation Checklist

Use this checklist to complete the installation and testing.

## ✅ Completed (by Claude)

- [x] Create CompanyModel.php
- [x] Create DivisionModel.php
- [x] Create DepartmentModel.php
- [x] Create TeamModel.php
- [x] Create CompanyDivisionMaintenance controller
- [x] Create company_division_maintenance view
- [x] Update Routes.php configuration
- [x] Update Menus.php configuration
- [x] Create 14 stored procedure SQL scripts
- [x] Create INSTALL_ALL.sql master script
- [x] Create comprehensive README.md
- [x] Sync files to MAMP
- [x] Create implementation documentation

## 📋 Your Tasks (To Complete)

### Step 1: Install Stored Procedures

- [ ] Open SQL Server Management Studio
- [ ] Connect to SQL Server instance
- [ ] Select correct database (DEMO, TLSYS, or TEST)
- [ ] Open `database/stored_procedures/INSTALL_ALL.sql`
- [ ] Edit line: `USE [YourDatabaseName]` to your database name
- [ ] Execute the script
- [ ] Verify all 14 procedures installed:
  ```sql
  SELECT name FROM sys.procedures
  WHERE name LIKE 'spCompany%'
     OR name LIKE 'spDivision%'
     OR name LIKE 'spDepartment%'
     OR name LIKE 'spTeam%'
  ORDER BY name;
  ```
- [ ] Should see 14 procedures listed

### Step 2: Add Menu Entries to Database

- [ ] Execute this SQL in your customer database:
  ```sql
  -- Add to tMenu
  INSERT INTO tMenu (MenuKey, MenuText, MenuType, MenuOrder, ParentMenu)
  VALUES ('mnuCompanyDivisionMaint', 'Company & Division Maintenance', 'M', 300, 'systems');

  -- Add to tSecurity
  INSERT INTO tSecurity (MenuKey, Description, SecurityType)
  VALUES ('mnuCompanyDivisionMaint', 'Company & Division Maintenance', 'Menu');
  ```
- [ ] Verify menu entry exists:
  ```sql
  SELECT * FROM tMenu WHERE MenuKey = 'mnuCompanyDivisionMaint';
  SELECT * FROM tSecurity WHERE MenuKey = 'mnuCompanyDivisionMaint';
  ```

### Step 3: Grant Permissions to Users

- [ ] Grant to SYSTEM user:
  ```sql
  EXEC spUser_Menu_Save @UserID = 'SYSTEM', @MenuKey = 'mnuCompanyDivisionMaint', @Granted = 1;
  ```
- [ ] Grant to your user (change 'tlyle' to your UserID):
  ```sql
  EXEC spUser_Menu_Save @UserID = 'tlyle', @MenuKey = 'mnuCompanyDivisionMaint', @Granted = 1;
  ```
- [ ] Verify permissions granted:
  ```sql
  EXEC spUser_Menus @UserID = 'SYSTEM'; -- Should include mnuCompanyDivisionMaint
  EXEC spUser_Menus @UserID = 'tlyle';  -- Should include mnuCompanyDivisionMaint
  ```

### Step 4: Test Stored Procedures (Optional but Recommended)

- [ ] Test Company procedures:
  ```sql
  -- Get all companies
  EXEC spCompanies_GetAll @IncludeInactive = 0;

  -- Get specific company (change CompanyID to existing value)
  EXEC spCompany_Get @CompanyID = 1;
  ```

- [ ] Test Division procedures:
  ```sql
  -- Get divisions for company (change CompanyID to existing value)
  EXEC spDivisions_GetByCompany @CompanyID = 1, @IncludeInactive = 0;

  -- Get specific division (change to existing values)
  EXEC spDivision_Get @CompanyID = 1, @DivisionID = 1;
  ```

- [ ] Test Department procedures:
  ```sql
  -- Get departments (change to existing values)
  EXEC spDepartments_GetByDivision @CompanyID = 1, @DivisionID = 1, @IncludeInactive = 0;
  ```

- [ ] Test Team procedures:
  ```sql
  -- Get teams (change to existing values)
  EXEC spTeams_GetByDivision @CompanyID = 1, @DivisionID = 1;
  ```

### Step 5: Test in CI4 Application

- [ ] **Log out** of TLS-CI4
- [ ] **Log back in** (to refresh menu permissions)
- [ ] Navigate to **Systems** menu
- [ ] Verify **Company & Division Maintenance** appears in menu
- [ ] Click menu item to open maintenance screen
- [ ] Verify companies grid loads with data
- [ ] Click a company row to select it
- [ ] Verify company details populate in left form
- [ ] Verify divisions grid loads in right column

### Step 6: Test Company Operations

- [ ] Edit company name
- [ ] Click "Save Company"
- [ ] Verify success message appears
- [ ] Reload page and verify change persisted
- [ ] Click "Reset" button
- [ ] Verify form reverts to saved values
- [ ] Expand "Mailing Address" accordion section
- [ ] Edit mailing address fields
- [ ] Save and verify
- [ ] Expand "Shipping Address" accordion section
- [ ] Edit shipping address fields
- [ ] Save and verify
- [ ] Expand "Contact Information" accordion section
- [ ] Edit phone/fax fields
- [ ] Save and verify
- [ ] Expand "Identifiers" accordion section
- [ ] Edit SCAC, DUNS, etc.
- [ ] Save and verify
- [ ] Toggle "Active" checkbox
- [ ] Save and verify (company should appear/disappear from grid based on active status)

### Step 7: Test New Company Creation

- [ ] Click "New Company" button
- [ ] Confirm the creation dialog
- [ ] Verify new company appears in grid
- [ ] Verify new company is auto-loaded in form
- [ ] Edit company details
- [ ] Save and verify

### Step 8: Test Division Operations

- [ ] Select a company that has divisions
- [ ] Verify divisions appear in grid
- [ ] Click a division row
- [ ] Verify division details appear below grid
- [ ] Edit division name
- [ ] Click "Save Division"
- [ ] Verify success message
- [ ] Verify division grid refreshes
- [ ] Click "Cancel" button
- [ ] Verify division details panel closes

### Step 9: Test New Division Creation

- [ ] Select a company
- [ ] Click "Add Division" button
- [ ] Confirm the creation dialog
- [ ] Verify new division appears in grid
- [ ] Edit division details
- [ ] Save and verify

### Step 10: Verify Error Handling

- [ ] Try to save company with empty name
- [ ] Verify validation error appears
- [ ] Try to save division with empty name
- [ ] Verify validation error appears
- [ ] Check browser console for JavaScript errors
- [ ] Check `writable/logs/log-*.php` for PHP errors

### Step 11: Test in Multiple Databases (if applicable)

- [ ] Test in DEMO database
- [ ] Test in TLSYS database
- [ ] Test in TEST database
- [ ] Verify multi-tenant isolation works correctly

### Step 12: Performance Check

- [ ] Check page load time (should be fast with 1-2 companies)
- [ ] Check AJAX response times
- [ ] Verify no SQL timeout errors
- [ ] Check database CPU/memory usage during operations

## 🚨 Troubleshooting

If something doesn't work:

### Menu Item Doesn't Appear
- Did you log out and log back in?
- Check permissions: `EXEC spUser_Menus @UserID = 'YourUserID'`
- Check menu entry exists: `SELECT * FROM tMenu WHERE MenuKey = 'mnuCompanyDivisionMaint'`

### "Could not find stored procedure" Error
- Check database context (are you in the right database?)
- Verify procedures installed: `SELECT name FROM sys.procedures WHERE name LIKE 'spCompany%'`
- Re-run INSTALL_ALL.sql if needed

### Return Code 99 (Not Found)
- Check that company/division/department records exist in database
- Verify you're using correct IDs

### Return Code 98 (Invalid Parent)
- Check that parent records exist:
  - For Division: Company must exist
  - For Department: Division must exist
  - For Team: Division must exist

### JavaScript Errors
- Check browser console (F12)
- Verify jQuery and Bootstrap are loaded
- Check CI4 base_url is correct

### Database Errors
- Check `writable/logs/log-*.php`
- Enable CI4 debug mode in `.env`: `CI_ENVIRONMENT = development`
- Check SQL Server error log

## 📝 Notes

- Companies grid shows ALL companies (typically 1-2 records)
- Divisions/Departments/Teams tabs are placeholder - full CRUD UI can be added later
- Return codes: 0=success, 97=failed, 98=invalid parent, 99=not found
- tTeam has triggers that set TeamKey to NULL in tUser/tUnit on delete
- Active status uses bit field, NOT EndDate convention

## ✅ Sign-Off

When all tests pass:

- [ ] Company CRUD working ✅
- [ ] Division CRUD working ✅
- [ ] No JavaScript errors ✅
- [ ] No PHP errors ✅
- [ ] Multi-tenant isolation verified ✅
- [ ] Performance acceptable ✅

**Tested By**: ___________________

**Date**: ___________________

**Database**: ___________________

**Notes**: ___________________
