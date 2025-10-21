# Company/Division/Department/Team Stored Procedures

This directory contains SQL Server stored procedure templates for the Company & Division Maintenance feature in TLS-CI4.

## Overview

These stored procedures provide CRUD operations for the hierarchical Company → Division → Department/Team structure.

## Hierarchy

```
tCompany (CompanyID)
  └── tDivision (CompanyID, DivisionID)
       ├── tDepartment (CompanyID, DivisionID, DepartmentID)
       └── tTeam (TeamKey + optional CompanyID, DivisionID)
```

## Stored Procedures

### Company (3 procedures)

| Procedure | Purpose | Parameters | Return Codes |
|-----------|---------|------------|--------------|
| `spCompanies_GetAll` | Get all companies for grid | @IncludeInactive BIT | 0 = success |
| `spCompany_Get` | Get single company | @CompanyID INT | 0 = success, 99 = not found |
| `spCompany_Save` | Insert or update company | 47 parameters (see file) | 0 = success, 97 = failed |

### Division (3 procedures)

| Procedure | Purpose | Parameters | Return Codes |
|-----------|---------|------------|--------------|
| `spDivisions_GetByCompany` | Get divisions for company | @CompanyID, @IncludeInactive | 0 = success |
| `spDivision_Get` | Get single division | @CompanyID, @DivisionID | 0 = success, 99 = not found |
| `spDivision_Save` | Insert or update division | 13 parameters | 0 = success, 97 = failed, 98 = invalid parent |

### Department (4 procedures)

| Procedure | Purpose | Parameters | Return Codes |
|-----------|---------|------------|--------------|
| `spDepartments_GetByDivision` | Get departments for division | @CompanyID, @DivisionID, @IncludeInactive | 0 = success |
| `spDepartment_Get` | Get single department | @CompanyID, @DivisionID, @DepartmentID | 0 = success, 99 = not found |
| `spDepartment_Save` | Insert or update department | 5 parameters | 0 = success, 97 = failed, 98 = invalid parent |
| `spDepartment_Delete` | Delete department | @CompanyID, @DivisionID, @DepartmentID | 0 = success, 99 = not found |

### Team (4 procedures)

| Procedure | Purpose | Parameters | Return Codes |
|-----------|---------|------------|--------------|
| `spTeams_GetByDivision` | Get teams for division | @CompanyID, @DivisionID | 0 = success |
| `spTeam_Get` | Get single team | @TeamKey INT | 0 = success, 99 = not found |
| `spTeam_Save` | Insert or update team | 9 parameters | 0 = success, 97 = failed, 98 = invalid parent |
| `spTeam_Delete` | Delete team | @TeamKey INT | 0 = success, 99 = not found |

## Return Code Standards

All stored procedures follow the standard return code convention:

- **0** = Success (SRV_NORMAL)
- **97** = Insert/Update failed (SRV_INSERTFAILED / SRV_UPDATEFAILED)
- **98** = Invalid parent (parent record doesn't exist)
- **99** = Not found (SRV_NOTFOUND)

## Installation Instructions

### Option 1: Install All (Recommended)

1. Open SQL Server Management Studio
2. Connect to your SQL Server instance
3. **IMPORTANT:** Select the correct database (e.g., DEMO, TLSYS, TEST)
4. Edit `INSTALL_ALL.sql` and change `USE [YourDatabaseName]` to your database
5. Execute the script

The script will install all 14 stored procedures in the correct order.

### Option 2: Install Individually

Execute each SQL file in this order:

**Company:**
1. `spCompanies_GetAll.sql`
2. `spCompany_Get.sql`
3. `spCompany_Save.sql`

**Division:**
4. `spDivisions_GetByCompany.sql`
5. `spDivision_Get.sql`
6. `spDivision_Save.sql`

**Department:**
7. `spDepartments_GetByDivision.sql`
8. `spDepartment_Get.sql`
9. `spDepartment_Save.sql`
10. `spDepartment_Delete.sql`

**Team:**
11. `spTeams_GetByDivision.sql`
12. `spTeam_Get.sql`
13. `spTeam_Save.sql`
14. `spTeam_Delete.sql`

## Database Setup (After Installing Procedures)

### 1. Add Menu Entry

Execute this SQL to add the menu item to `tMenu`:

```sql
INSERT INTO tMenu (MenuKey, MenuText, MenuType, MenuOrder, ParentMenu)
VALUES ('mnuCompanyDivisionMaint', 'Company & Division Maintenance', 'M', 300, 'systems');
```

### 2. Add Security Entry

Execute this SQL to add the security permission to `tSecurity`:

```sql
INSERT INTO tSecurity (MenuKey, Description, SecurityType)
VALUES ('mnuCompanyDivisionMaint', 'Company & Division Maintenance', 'Menu');
```

### 3. Grant Permissions

Use the User Security screen in CI4 to grant access to users/roles, or execute SQL:

```sql
-- Grant to SYSTEM user
EXEC spUser_Menu_Save @UserID = 'SYSTEM', @MenuKey = 'mnuCompanyDivisionMaint', @Granted = 1;

-- Grant to your user
EXEC spUser_Menu_Save @UserID = 'YourUserID', @MenuKey = 'mnuCompanyDivisionMaint', @Granted = 1;
```

## Testing

### Test Company Procedures

```sql
-- Get all companies
EXEC spCompanies_GetAll @IncludeInactive = 0;

-- Get specific company
EXEC spCompany_Get @CompanyID = 1;

-- Create test company
DECLARE @RC INT;
EXEC @RC = spCompany_Save
    @CompanyID = 999,
    @CompanyName = 'Test Company',
    @Active = 1;
SELECT @RC AS ReturnCode;
```

### Test Division Procedures

```sql
-- Get divisions for company
EXEC spDivisions_GetByCompany @CompanyID = 1, @IncludeInactive = 0;

-- Get specific division
EXEC spDivision_Get @CompanyID = 1, @DivisionID = 1;

-- Create test division
DECLARE @RC INT;
EXEC @RC = spDivision_Save
    @CompanyID = 1,
    @DivisionID = 999,
    @Name = 'Test Division',
    @Active = 1;
SELECT @RC AS ReturnCode;
```

### Test Department Procedures

```sql
-- Get departments for division
EXEC spDepartments_GetByDivision @CompanyID = 1, @DivisionID = 1, @IncludeInactive = 0;

-- Create test department
DECLARE @RC INT;
EXEC @RC = spDepartment_Save
    @CompanyID = 1,
    @DivisionID = 1,
    @DepartmentID = 999,
    @Description = 'Test Department',
    @Active = 1;
SELECT @RC AS ReturnCode;
```

### Test Team Procedures

```sql
-- Get teams for division
EXEC spTeams_GetByDivision @CompanyID = 1, @DivisionID = 1;

-- Get specific team
EXEC spTeam_Get @TeamKey = 1;

-- Create test team
DECLARE @RC INT;
EXEC @RC = spTeam_Save
    @TeamKey = 999,
    @TeamName = 'Test Team',
    @CompanyID = 1,
    @DivisionID = 1;
SELECT @RC AS ReturnCode;
```

## Important Notes

### tTeam Triggers

The `tTeam` table has existing triggers (`tD_tTeam` and `tU_tTeam`) that automatically set `TeamKey` to NULL in `tUser` and `tUnit` when a team is deleted or the TeamKey is updated. This is intentional behavior to prevent orphaned references.

### Active vs EndDate

- **Company**: Uses `Active` BIT field only (no EndDate)
- **Division**: Uses `Active` BIT field only (no EndDate)
- **Department**: Uses `Active` BIT field (required, NOT NULL)
- **Team**: Has NO Active or EndDate fields

This is different from Agent Maintenance which uses the EndDate = '1899-12-30' convention.

### Foreign Key Validation

The Save procedures include validation to ensure parent records exist:
- `spDivision_Save` validates that the Company exists
- `spDepartment_Save` validates that the Division exists
- `spTeam_Save` validates that the Division exists (if provided)

If validation fails, return code **98** is returned.

### Composite Keys

Department uses a 3-part composite key: (CompanyID, DivisionID, DepartmentID)
Division uses a 2-part composite key: (CompanyID, DivisionID)

## CI4 Integration

These stored procedures are called by:
- **Models**: `CompanyModel.php`, `DivisionModel.php`, `DepartmentModel.php`, `TeamModel.php`
- **Controller**: `CompanyDivisionMaintenance.php`
- **View**: `systems/company_division_maintenance.php`

All models use the `BaseModel::callStoredProcedure()` and `BaseModel::callStoredProcedureWithReturn()` methods.

## Troubleshooting

### "Could not find stored procedure 'spCompany_Get'"

Make sure you executed the scripts in the correct database. Check with:

```sql
SELECT name FROM sys.procedures WHERE name LIKE 'spCompany%';
```

### Return code 98 (Invalid parent)

The parent record doesn't exist:
- For Division: Check that CompanyID exists in tCompany
- For Department: Check that (CompanyID, DivisionID) exists in tDivision
- For Team: Check that (CompanyID, DivisionID) exists in tDivision

### Return code 97 (Insert/Update failed)

The INSERT or UPDATE statement failed. Check:
- SQL Server error log for constraint violations
- Data type mismatches
- NULL constraint violations

## Files

```
database/stored_procedures/
├── README.md                           # This file
├── INSTALL_ALL.sql                     # Master installation script
├── spCompanies_GetAll.sql             # Get all companies
├── spCompany_Get.sql                  # Get single company
├── spCompany_Save.sql                 # Save company
├── spDivisions_GetByCompany.sql       # Get divisions for company
├── spDivision_Get.sql                 # Get single division
├── spDivision_Save.sql                # Save division
├── spDepartments_GetByDivision.sql    # Get departments for division
├── spDepartment_Get.sql               # Get single department
├── spDepartment_Save.sql              # Save department
├── spDepartment_Delete.sql            # Delete department
├── spTeams_GetByDivision.sql          # Get teams for division
├── spTeam_Get.sql                     # Get single team
├── spTeam_Save.sql                    # Save team
└── spTeam_Delete.sql                  # Delete team
```

## Version History

- **2025-01-21**: Initial creation for Company & Division Maintenance feature
