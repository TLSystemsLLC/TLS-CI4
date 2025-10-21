SET QUOTED_IDENTIFIER ON
GO
SET ANSI_NULLS ON
GO

CREATE OR ALTER PROCEDURE [dbo].[spCompany_Get]
(@CompanyID INT)
AS
/* Get a single company by CompanyID */

SELECT
    CompanyID,
    CompanyName,
    MailingAddress,
    MailingCity,
    MailingState,
    MailingZip,
    ShippingAddress,
    ShippingCity,
    ShippingState,
    ShippingZip,
    MainPhone,
    MainFax,
    SCAC,
    DUNS,
    ICC,
    DOT,
    FID,
    Active,
    APAccount,
    ARAccount,
    BadDebtAccount,
    MiscAccount,
    FreightRevAccount,
    BrokerRevAccount,
    FreightPayableAccount,
    GeneralBankAccount,
    SettlementBankAccount,
    SettlementClearingAccount,
    FreightDetailPost,
    InterCompanyClearing,
    FTLBillingFee,
    FTLLogisticsFee,
    InterCompanyAR,
    InterCompanyAP,
    FrieghtRevExp,
    SystemRemitVendor,
    ShortName,
    CompanyFreightRevenue,
    CompanyFreightExpense,
    CompanyTruckFuelExpense,
    CompanyReeferFuelExpense,
    DriverAR,
    OwnerSettlementStep,
    ComdataInterface,
    TranfloMobileInterface,
    RetainedEarningsAccount,
    ifta_miles_lock
FROM tCompany
WHERE CompanyID = @CompanyID;

IF @@ROWCOUNT = 0
    RETURN 99;

RETURN 0;

GO
GRANT EXECUTE ON [dbo].[spCompany_Get] TO [FullUser]
GO