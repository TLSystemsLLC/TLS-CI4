SET QUOTED_IDENTIFIER ON
GO
SET ANSI_NULLS ON
GO

CREATE PROCEDURE [dbo].[spDepartment_Delete]
(
    @CompanyID INT,
    @DivisionID INT,
    @DepartmentID INT
)
AS
/* Delete a department */

DELETE FROM tDepartment
WHERE CompanyID = @CompanyID
  AND DivisionID = @DivisionID
  AND DepartmentID = @DepartmentID;

IF @@ROWCOUNT = 0
    RETURN 99;

RETURN 0;

GO
GRANT EXECUTE ON [dbo].[spDepartment_Delete] TO [FullUser]
GO
