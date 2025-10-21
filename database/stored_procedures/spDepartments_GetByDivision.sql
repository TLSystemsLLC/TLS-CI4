SET QUOTED_IDENTIFIER ON
GO
SET ANSI_NULLS ON
GO

CREATE PROCEDURE [dbo].[spDepartments_GetByDivision]
(
    @CompanyID INT,
    @DivisionID INT,
    @IncludeInactive BIT = 0
)
AS
/* Get all departments for a division */
SELECT
    CompanyID,
    DivisionID,
    DepartmentID,
    Description,
    Active
FROM tDepartment
WHERE CompanyID = @CompanyID
  AND DivisionID = @DivisionID
  AND (@IncludeInactive = 1 OR Active = 1)
ORDER BY Description;

RETURN 0;

GO
GRANT EXECUTE ON [dbo].[spDepartments_GetByDivision] TO [FullUser]
GO
