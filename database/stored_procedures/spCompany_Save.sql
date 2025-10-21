SET QUOTED_IDENTIFIER ON
GO
SET ANSI_NULLS ON
GO

CREATE PROCEDURE [dbo].[spCompany_Save]
(
    @CompanyID INT,
    @CompanyName VARCHAR(50),
    @MailingAddress VARCHAR(50) = NULL,
    @MailingCity VARCHAR(50) = NULL,
    @MailingState CHAR(2) = NULL,
    @MailingZip VARCHAR(50) = NULL,
    @ShippingAddress VARCHAR(50) = NULL,
    @ShippingCity VARCHAR(50) = NULL,
    @ShippingState CHAR(2) = NULL,
    @ShippingZip VARCHAR(10) = NULL,
    @MainPhone CHAR(10) = NULL,
    @MainFax CHAR(10) = NULL,
    @SCAC CHAR(4) = NULL,
    @DUNS VARCHAR(50) = NULL,
    @ICC VARCHAR(50) = NULL,
    @DOT VARCHAR(50) = NULL,
    @FID VARCHAR(50) = NULL,
    @Active BIT = 1,
    @APAccount DECIMAL(12, 0) = NULL,
    @ARAccount DECIMAL(12, 0) = NULL,
    @BadDebtAccount DECIMAL(12, 0) = NULL,
    @MiscAccount DECIMAL(12, 0) = NULL,
    @FreightRevAccount DECIMAL(12, 0) = NULL,
    @BrokerRevAccount DECIMAL(12, 0) = NULL,
    @FreightPayableAccount DECIMAL(12, 0) = NULL,
    @GeneralBankAccount DECIMAL(12, 0) = NULL,
    @SettlementBankAccount DECIMAL(12, 0) = NULL,
    @SettlementClearingAccount DECIMAL(12, 0) = NULL,
    @FreightDetailPost BIT = NULL,
    @InterCompanyClearing DECIMAL(12, 0) = NULL,
    @FTLBillingFee DECIMAL(15, 5) = NULL,
    @FTLLogisticsFee DECIMAL(15, 5) = NULL,
    @InterCompanyAR DECIMAL(12, 0) = NULL,
    @InterCompanyAP DECIMAL(12, 0) = NULL,
    @FrieghtRevExp DECIMAL(12, 0) = NULL,
    @SystemRemitVendor INT = NULL,
    @ShortName VARCHAR(20) = NULL,
    @CompanyFreightRevenue DECIMAL(12, 0) = NULL,
    @CompanyFreightExpense DECIMAL(12, 0) = NULL,
    @CompanyTruckFuelExpense DECIMAL(12, 0) = NULL,
    @CompanyReeferFuelExpense DECIMAL(12, 0) = NULL,
    @DriverAR DECIMAL(12, 0) = NULL,
    @OwnerSettlementStep INT = NULL,
    @ComdataInterface BIT = NULL,
    @TranfloMobileInterface BIT = NULL,
    @RetainedEarningsAccount DECIMAL(12, 0) = NULL,
    @ifta_miles_lock DATE = NULL
)
AS
/* Save (insert or update) a company */

-- Check if company exists
SELECT @CompanyID = CompanyID
FROM tCompany
WHERE CompanyID = @CompanyID;

IF @@ROWCOUNT = 0
BEGIN
    -- INSERT new company
    INSERT INTO tCompany
    (
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
    )
    VALUES
    (
        @CompanyID,
        @CompanyName,
        @MailingAddress,
        @MailingCity,
        @MailingState,
        @MailingZip,
        @ShippingAddress,
        @ShippingCity,
        @ShippingState,
        @ShippingZip,
        @MainPhone,
        @MainFax,
        @SCAC,
        @DUNS,
        @ICC,
        @DOT,
        @FID,
        @Active,
        @APAccount,
        @ARAccount,
        @BadDebtAccount,
        @MiscAccount,
        @FreightRevAccount,
        @BrokerRevAccount,
        @FreightPayableAccount,
        @GeneralBankAccount,
        @SettlementBankAccount,
        @SettlementClearingAccount,
        @FreightDetailPost,
        @InterCompanyClearing,
        @FTLBillingFee,
        @FTLLogisticsFee,
        @InterCompanyAR,
        @InterCompanyAP,
        @FrieghtRevExp,
        @SystemRemitVendor,
        @ShortName,
        @CompanyFreightRevenue,
        @CompanyFreightExpense,
        @CompanyTruckFuelExpense,
        @CompanyReeferFuelExpense,
        @DriverAR,
        @OwnerSettlementStep,
        @ComdataInterface,
        @TranfloMobileInterface,
        @RetainedEarningsAccount,
        @ifta_miles_lock
    );

    IF @@ROWCOUNT = 0
        RETURN 97;
END
ELSE
BEGIN
    -- UPDATE existing company
    UPDATE tCompany
    SET
        CompanyName = @CompanyName,
        MailingAddress = @MailingAddress,
        MailingCity = @MailingCity,
        MailingState = @MailingState,
        MailingZip = @MailingZip,
        ShippingAddress = @ShippingAddress,
        ShippingCity = @ShippingCity,
        ShippingState = @ShippingState,
        ShippingZip = @ShippingZip,
        MainPhone = @MainPhone,
        MainFax = @MainFax,
        SCAC = @SCAC,
        DUNS = @DUNS,
        ICC = @ICC,
        DOT = @DOT,
        FID = @FID,
        Active = @Active,
        APAccount = @APAccount,
        ARAccount = @ARAccount,
        BadDebtAccount = @BadDebtAccount,
        MiscAccount = @MiscAccount,
        FreightRevAccount = @FreightRevAccount,
        BrokerRevAccount = @BrokerRevAccount,
        FreightPayableAccount = @FreightPayableAccount,
        GeneralBankAccount = @GeneralBankAccount,
        SettlementBankAccount = @SettlementBankAccount,
        SettlementClearingAccount = @SettlementClearingAccount,
        FreightDetailPost = @FreightDetailPost,
        InterCompanyClearing = @InterCompanyClearing,
        FTLBillingFee = @FTLBillingFee,
        FTLLogisticsFee = @FTLLogisticsFee,
        InterCompanyAR = @InterCompanyAR,
        InterCompanyAP = @InterCompanyAP,
        FrieghtRevExp = @FrieghtRevExp,
        SystemRemitVendor = @SystemRemitVendor,
        ShortName = @ShortName,
        CompanyFreightRevenue = @CompanyFreightRevenue,
        CompanyFreightExpense = @CompanyFreightExpense,
        CompanyTruckFuelExpense = @CompanyTruckFuelExpense,
        CompanyReeferFuelExpense = @CompanyReeferFuelExpense,
        DriverAR = @DriverAR,
        OwnerSettlementStep = @OwnerSettlementStep,
        ComdataInterface = @ComdataInterface,
        TranfloMobileInterface = @TranfloMobileInterface,
        RetainedEarningsAccount = @RetainedEarningsAccount,
        ifta_miles_lock = @ifta_miles_lock
    WHERE CompanyID = @CompanyID;

    IF @@ROWCOUNT = 0
        RETURN 97;
END

RETURN 0;

GO
GRANT EXECUTE ON [dbo].[spCompany_Save] TO [FullUser]
GO
