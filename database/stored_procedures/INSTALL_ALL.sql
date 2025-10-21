/*
 * Master Installation Script for Company/Division/Department/Team Stored Procedures
 *
 * Execute this script in SQL Server Management Studio against your customer database
 *
 * IMPORTANT: Run this in the correct database context (e.g., DEMO, TLSYS, TEST, etc.)
 *
 * Installation Order:
 * 1. Company procedures (no dependencies)
 * 2. Division procedures (depends on tCompany)
 * 3. Department procedures (depends on tDivision)
 * 4. Team procedures (depends on tDivision)
 */

USE [CWKI2]  -- CHANGE THIS TO YOUR DATABASE NAME
GO

PRINT 'Installing Company Stored Procedures...'
GO

-- Company procedures
:r spCompanies_GetAll.sql
:r spCompany_Get.sql
:r spCompany_Save.sql

PRINT 'Company procedures installed successfully.'
GO

PRINT 'Installing Division Stored Procedures...'
GO

-- Division procedures
:r spDivisions_GetByCompany.sql
:r spDivision_Get.sql
:r spDivision_Save.sql

PRINT 'Division procedures installed successfully.'
GO

PRINT 'Installing Department Stored Procedures...'
GO

-- Department procedures
:r spDepartments_GetByDivision.sql
:r spDepartment_Get.sql
:r spDepartment_Save.sql
:r spDepartment_Delete.sql

PRINT 'Department procedures installed successfully.'
GO

PRINT 'Installing Team Stored Procedures...'
GO

-- Team procedures
:r spTeams_GetByDivision.sql
:r spTeam_Get.sql
:r spTeam_Save.sql
:r spTeam_Delete.sql

PRINT 'Team procedures installed successfully.'
GO

PRINT ''
PRINT 'All stored procedures installed successfully!'
PRINT ''
PRINT 'Total procedures installed: 14'
PRINT '  - Company: 3 procedures'
PRINT '  - Division: 3 procedures'
PRINT '  - Department: 4 procedures'
PRINT '  - Team: 4 procedures'
PRINT ''
PRINT 'Next Steps:'
PRINT '1. Add menu entry to tMenu table for mnuCompanyDivisionMaint'
PRINT '2. Grant permissions in tSecurity table'
PRINT '3. Test the Company & Division Maintenance screen in CI4'
GO