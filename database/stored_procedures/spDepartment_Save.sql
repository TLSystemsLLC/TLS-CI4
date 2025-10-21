SET QUOTED_IDENTIFIER ON
GO
SET ANSI_NULLS ON
GO

CREATE PROCEDURE [dbo].[spDepartment_Save]
(
    @CompanyID INT,
    @DivisionID INT,
    @DepartmentID INT,
    @Description VARCHAR(50),
    @Active BIT = 1
)
AS
/* Save (insert or update) a department */

-- Validate that parent division exists
IF NOT EXISTS (SELECT 1 FROM tDivision WHERE CompanyID = @CompanyID AND DivisionID = @DivisionID)
BEGIN
    RETURN 98; -- Invalid parent
END

-- Check if department exists
IF EXISTS (SELECT 1 FROM tDepartment WHERE CompanyID = @CompanyID AND DivisionID = @DivisionID AND DepartmentID = @DepartmentID)
BEGIN
    -- UPDATE existing department
    UPDATE tDepartment
    SET
        Description = @Description,
        Active = @Active
    WHERE CompanyID = @CompanyID
      AND DivisionID = @DivisionID
      AND DepartmentID = @DepartmentID;

    IF @@ROWCOUNT = 0
        RETURN 97;
END
ELSE
BEGIN
    -- INSERT new department
    INSERT INTO tDepartment
    (
        CompanyID,
        DivisionID,
        DepartmentID,
        Description,
        Active
    )
    VALUES
    (
        @CompanyID,
        @DivisionID,
        @DepartmentID,
        @Description,
        @Active
    );

    IF @@ROWCOUNT = 0
        RETURN 97;
END

RETURN 0;

GO
GRANT EXECUTE ON [dbo].[spDepartment_Save] TO [FullUser]
GO
