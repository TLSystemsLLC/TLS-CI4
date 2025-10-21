SET QUOTED_IDENTIFIER ON
GO
SET ANSI_NULLS ON
GO

CREATE PROCEDURE [dbo].[spDivision_Get]
(
    @CompanyID INT,
    @DivisionID INT
)
AS
/* Get a single division by CompanyID and DivisionID */
SELECT
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
FROM tDivision
WHERE CompanyID = @CompanyID
  AND DivisionID = @DivisionID;

IF @@ROWCOUNT = 0
    RETURN 99;

RETURN 0;

GO
GRANT EXECUTE ON [dbo].[spDivision_Get] TO [FullUser]
GO
