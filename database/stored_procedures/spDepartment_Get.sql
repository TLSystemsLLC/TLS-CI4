SET QUOTED_IDENTIFIER ON
GO
SET ANSI_NULLS ON
GO

CREATE PROCEDURE [dbo].[spDepartment_Get]
(
    @CompanyID INT,
    @DivisionID INT,
    @DepartmentID INT
)
AS
/* Get a single department by CompanyID, DivisionID, and DepartmentID */
SELECT
    CompanyID,
    DivisionID,
    DepartmentID,
    Description,
    Active
FROM tDepartment
WHERE CompanyID = @CompanyID
  AND DivisionID = @DivisionID
  AND DepartmentID = @DepartmentID;

IF @@ROWCOUNT = 0
    RETURN 99;

RETURN 0;

GO
GRANT EXECUTE ON [dbo].[spDepartment_Get] TO [FullUser]
GO
