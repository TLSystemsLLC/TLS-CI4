SET QUOTED_IDENTIFIER ON
GO
SET ANSI_NULLS ON
GO

CREATE PROCEDURE [dbo].[spDivision_Save]
(
    @CompanyID INT,
    @DivisionID INT,
    @Name VARCHAR(50),
    @Address VARCHAR(50) = NULL,
    @City VARCHAR(50) = NULL,
    @State CHAR(2) = NULL,
    @Zip CHAR(10) = NULL,
    @Phone CHAR(10) = NULL,
    @Fax CHAR(10) = NULL,
    @MainContact INT = NULL,
    @SafetyContact INT = NULL,
    @Active BIT = 1,
    @AccountingContact INT = NULL
)
AS
/* Save (insert or update) a division */

-- Validate that parent company exists
IF NOT EXISTS (SELECT 1 FROM tCompany WHERE CompanyID = @CompanyID)
BEGIN
    RETURN 98; -- Invalid parent
END

-- Check if division exists
IF EXISTS (SELECT 1 FROM tDivision WHERE CompanyID = @CompanyID AND DivisionID = @DivisionID)
BEGIN
    -- UPDATE existing division
    UPDATE tDivision
    SET
        Name = @Name,
        Address = @Address,
        City = @City,
        State = @State,
        Zip = @Zip,
        Phone = @Phone,
        Fax = @Fax,
        MainContact = @MainContact,
        SafetyContact = @SafetyContact,
        Active = @Active,
        AccountingContact = @AccountingContact
    WHERE CompanyID = @CompanyID
      AND DivisionID = @DivisionID;

    IF @@ROWCOUNT = 0
        RETURN 97;
END
ELSE
BEGIN
    -- INSERT new division
    INSERT INTO tDivision
    (
        CompanyID,
        DivisionID,
        Name,
        Address,
        City,
        State,
        Zip,
        Phone,
        Fax,
        MainContact,
        SafetyContact,
        Active,
        AccountingContact
    )
    VALUES
    (
        @CompanyID,
        @DivisionID,
        @Name,
        @Address,
        @City,
        @State,
        @Zip,
        @Phone,
        @Fax,
        @MainContact,
        @SafetyContact,
        @Active,
        @AccountingContact
    );

    IF @@ROWCOUNT = 0
        RETURN 97;
END

RETURN 0;

GO
GRANT EXECUTE ON [dbo].[spDivision_Save] TO [FullUser]
GO
