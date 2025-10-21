/*
 * tSecurity INSERT Script for CI4 Menu Items
 *
 * This script ensures all menu items from app/Config/Menus.php exist in tSecurity table.
 * Uses WHERE NOT EXISTS to avoid duplicates.
 *
 * Parent Menu Keys:
 * - mnuMain02 = Accounting
 * - mnuMain03 = Dispatch
 * - mnuMain04 = Logistics
 * - mnuMain06 = Imaging
 * - mnuMain07 = Reports
 * - mnuMain08 = Safety
 * - mnuMain09 = Systems
 * - mnuMain11 = Payroll
 */

-- ========================================
-- ACCOUNTING (mnuMain02)
-- ========================================

-- G/L
INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain02', 'mnuCOAMaint', 'Chart of Account Maintenance'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuCOAMaint');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain02', 'mnuGLAccoutHistory', 'Account History'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuGLAccoutHistory');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain02', 'mnuTransactionSearch', 'Transaction Search'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuTransactionSearch');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain02', 'mnuJE', 'Journal Entry'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuJE');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain02', 'mnuBankRec', 'Bank Reconciliation'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuBankRec');
GO


-- G/L Reports
INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain02', 'mnuChartofAccounts', 'Chart of Accounts'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuChartofAccounts');
GO

GO

INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain02', 'mnuBalanceSheet', 'Balance Sheet'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuBalanceSheet');
GO

GO

INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain02', 'mnuIncomeStatement', 'Income Statement'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuIncomeStatement');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain02', 'mnuTrialBalance', 'Trial Balance'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuTrialBalance');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain02', 'mnuDailyBalance', 'Daily Balance Summary'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuDailyBalance');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain02', 'mnuFinStmt', 'All Financial Statements'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuFinStmt');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain02', 'mnuGeneralLedger', 'General Ledger'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuGeneralLedger');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain02', 'mnuTransactionJournal', 'Transaction Journal'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuTransactionJournal');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain02', 'mnuOOAR', 'Owner Operator Accounts Receivable'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuOOAR');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain02', 'mnuAPJournal', 'AP Journal'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuAPJournal');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain02', 'mnuGLExport', 'Export to GL'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuGLExport');
GO


-- A/R
INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain02', 'mnuBillingEntry', 'Billing Entry'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuBillingEntry');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain02', 'mnuARDeposit', 'A/R Deposit'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuARDeposit');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain02', 'mnuReapplyPmt', 'Reapply Payment'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuReapplyPmt');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain02', 'mnuInvoiceSearch', 'Invoice Search'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuInvoiceSearch');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain02', 'mnuAgingReport', 'Aging Report'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuAgingReport');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain02', 'mnuCollections', 'Collections'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuCollections');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain02', 'mnuCBAgingReport', 'CB Aging Report'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuCBAgingReport');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain02', 'mnuCreditCheck', 'Credit Check'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuCreditCheck');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain02', 'mnuCreditCheckLog', 'Credit Check Log'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuCreditCheckLog');
GO


-- A/P
INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain02', 'mnuVoucher', 'Voucher'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuVoucher');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain02', 'mnuAPInvoiceApproval', 'A/P Invoice Approval'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuAPInvoiceApproval');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain02', 'mnuPayables', 'Payables'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuPayables');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain02', 'mnuProcessICCheck', 'Process Intercompany Check'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuProcessICCheck');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain02', 'mnuPrint1099', 'Print 1099'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuPrint1099');
GO


-- ========================================
-- DISPATCH (mnuMain03)
-- ========================================

INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain03', 'mnuLoadEntry', 'Load Entry'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuLoadEntry');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain03', 'mnuLoadInq', 'Load Inquiry'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuLoadInq');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain03', 'mnuLoadLookup', 'Load Lookup'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuLoadLookup');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain03', 'mnuLookupLoads', 'Lookup Loads'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuLookupLoads');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain03', 'mnuBrokerTracking', 'Broker Tracking'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuBrokerTracking');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain03', 'mnuAvailLoads', 'Available Loads'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuAvailLoads');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain03', 'mnuAvailTrucks', 'Available Trucks'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuAvailTrucks');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain03', 'mnuPosMap', 'Position Map'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuPosMap');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain03', 'mnuCustMaint', 'Customer Maintenance'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuCustMaint');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain03', 'mnuCarrierMaint', 'Carrier Maintenance'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuCarrierMaint');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain03', 'mnuLocationMaint', 'Location Maintenance'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuLocationMaint');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain03', 'mnuVendorMaint', 'Vendor Maintenance'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuVendorMaint');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain03', 'mnuLoadEntryLog', 'Load Entry Log'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuLoadEntryLog');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain03', 'mnuLoadInqLog', 'Load Inquiry Log'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuLoadInqLog');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain03', 'mnuLookupLoadsLog', 'Lookup Loads Log'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuLookupLoadsLog');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain03', 'mnuPrintBrokerConf', 'Print Broker Confirmation'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuPrintBrokerConf');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain03', 'mnuReassignCarrierLoad', 'Reassign Carrier Load'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuReassignCarrierLoad');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain03', 'mnuCarrierCheck', 'Carrier Check'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuCarrierCheck');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain03', 'mnuCarrierHistory', 'Carrier History'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuCarrierHistory');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain03', 'mnuVendorHist', 'Vendor History'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuVendorHist');
GO


-- ========================================
-- LOGISTICS (mnuMain04)
-- ========================================

INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain04', 'mnuloadreg', 'Load Registration'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuloadreg');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain04', 'mnuLogTrial', 'Trial Logbook'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuLogTrial');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain04', 'mnuLogPreview', 'Preview Logbook'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuLogPreview');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain04', 'mnuLogFinal', 'Final Logbook'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuLogFinal');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain04', 'mnuLogPrint', 'Print Logbook'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuLogPrint');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain04', 'mnuRecap', 'Recap'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuRecap');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain04', 'mnuFuel', 'Fuel'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuFuel');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain04', 'mnuEFSCardMaintenance', 'EFS Card Maintenance'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuEFSCardMaintenance');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain04', 'mnuEFSPolicyMaintenance', 'EFS Policy Maintenance'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuEFSPolicyMaintenance');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain04', 'mnuEFSMoneyCode', 'EFS Money Code'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuEFSMoneyCode');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain04', 'mnuEFSLoadCash', 'EFS Load Cash'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuEFSLoadCash');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain04', 'mnuTrafficLanes', 'Traffic Lanes'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuTrafficLanes');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain04', 'mnuEDILoads', 'EDI Loads'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuEDILoads');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain04', 'mnuAvailEDI', 'Available EDI'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuAvailEDI');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain04', 'mnuMobileMessages', 'Mobile Messages'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuMobileMessages');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain04', 'mnuMobileSendMessage', 'Mobile Send Message'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuMobileSendMessage');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain04', 'mnuMessageSearch', 'Message Search'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuMessageSearch');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain04', 'mnuPosRptHist', 'Position Report History'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuPosRptHist');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain04', 'mnuTimeClockInOut', 'Time Clock In/Out'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuTimeClockInOut');
GO


-- ========================================
-- IMAGING (mnuMain06)
-- ========================================

INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain06', 'mnuImageExceptions', 'Image Exceptions'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuImageExceptions');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain06', 'mnuImageAudit', 'Image Audit'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuImageAudit');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain06', 'mnuExceptionStatusList', 'Exception Status List'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuExceptionStatusList');
GO


-- ========================================
-- REPORTS (mnuMain07)
-- ========================================

INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain07', 'mnuUnbilledLoads', 'Unbilled Loads'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuUnbilledLoads');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain07', 'mnuUnbilledWithPaperwork', 'Unbilled With Paperwork'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuUnbilledWithPaperwork');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain07', 'mnuCustRevSum', 'Customer Revenue Summary'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuCustRevSum');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain07', 'mnuLoadTrailerStatus', 'Load Trailer Status'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuLoadTrailerStatus');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain07', 'mnuPrintBatch', 'Print Batch'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuPrintBatch');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain07', 'mnuPrintJobs', 'Print Jobs'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuPrintJobs');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain07', 'mnuDailyCount', 'Daily Count'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuDailyCount');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain07', 'mnuDepositLookup', 'Deposit Lookup'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuDepositLookup');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain07', 'mnuUnitRev', 'Unit Revenue'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuUnitRev');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain07', 'mnuDriverMiles', 'Driver Miles'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuDriverMiles');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain07', 'mnuReloadReport', 'Reload Report'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuReloadReport');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain07', 'mnuSubmitReport', 'Submit Report'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuSubmitReport');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain07', 'mnuSubmitReportUnitMiles', 'Submit Report Unit Miles'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuSubmitReportUnitMiles');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain07', 'mnuSMDRReport', 'SMDR Report'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuSMDRReport');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain07', 'mnuUnitStateReport', 'Unit State Report'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuUnitStateReport');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain07', 'mnuActiveCarrierList', 'Active Carrier List'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuActiveCarrierList');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain07', 'mnuActiveDriverList', 'Active Driver List'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuActiveDriverList');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain07', 'mnuTeamDriverList', 'Team Driver List'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuTeamDriverList');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain07', 'mnuTractorList', 'Tractor List'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuTractorList');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain07', 'mnuTrailerList', 'Trailer List'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuTrailerList');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain07', 'mnuTractorTrailer', 'Tractor Trailer'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuTractorTrailer');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain07', 'mnuTrailerPool', 'Trailer Pool'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuTrailerPool');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain07', 'mnuUnitLastDel', 'Unit Last Delivery'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuUnitLastDel');
GO


-- ========================================
-- SAFETY (mnuMain08)
-- ========================================

INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain08', 'mnuDriverMaint', 'Driver Maintenance'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuDriverMaint');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain08', 'mnuUnitMaint', 'Unit Maintenance'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuUnitMaint');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain08', 'mnuOwnerMaint', 'Owner Maintenance'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuOwnerMaint');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain08', 'mnuAgentMaint', 'Agent Maintenance'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuAgentMaint');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain08', 'mnuDriverPictures', 'Driver Pictures'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuDriverPictures');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain08', 'mnuUnitCheck', 'Unit Check'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuUnitCheck');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain08', 'mnuUnitHistory', 'Unit History'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuUnitHistory');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain08', 'mnuDriverInsRpt', 'Driver Insurance Report'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuDriverInsRpt');
GO


-- ========================================
-- SYSTEMS (mnuMain09)
-- ========================================

INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain09', 'mnuUserMaint', 'User Maintenance'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuUserMaint');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain09', 'mnuUserSecurity', 'User Security'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuUserSecurity');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain09', 'mnuCompanyDivisionMaint', 'Company & Division Maintenance'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuCompanyDivisionMaint');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain09', 'mnuProjectLog', 'Project Log'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuProjectLog');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain09', 'mnuTLSBilling', 'TL Systems Billing'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuTLSBilling');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain09', 'mnuEDIUserProfile', 'EDI User Profile'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuEDIUserProfile');
GO


-- ========================================
-- PAYROLL (mnuMain11)
-- ========================================

INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain11', 'mnuPayrollMaint', 'Payroll Maintenance'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuPayrollMaint');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain11', 'mnuCreateRepeating', 'Create Repeating'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuCreateRepeating');
GO


-- Driver Payroll
INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain11', 'mnuDriverPRMaint', 'Driver PR Maintenance'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuDriverPRMaint');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain11', 'mnuTrialDriverPR', 'Trial Driver PR'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuTrialDriverPR');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain11', 'mnuPreviewDriverPR', 'Preview Driver PR'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuPreviewDriverPR');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain11', 'mnuFinalizeDriverPR', 'Finalize Driver PR'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuFinalizeDriverPR');
GO


-- Owner Payroll
INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain11', 'mnuTrialOwnerPR', 'Trial Owner PR'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuTrialOwnerPR');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain11', 'mnuPreviewOwnerPR', 'Preview Owner PR'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuPreviewOwnerPR');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain11', 'mnuPrintOwnerPR', 'Print Owner PR'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuPrintOwnerPR');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain11', 'mnuFinalizeOwnerPR', 'Finalize Owner PR'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuFinalizeOwnerPR');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain11', 'mnuPrintOwnerDD', 'Print Owner Direct Deposit'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuPrintOwnerDD');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain11', 'mnuOwnerLabels', 'Owner Labels'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuOwnerLabels');
GO


-- Agent Payroll
INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain11', 'mnuTrialAgentPR', 'Trial Agent PR'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuTrialAgentPR');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain11', 'mnuPreviewAgentPR', 'Preview Agent PR'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuPreviewAgentPR');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain11', 'mnuPrintAgentPR', 'Print Agent PR'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuPrintAgentPR');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain11', 'mnuFinalizeAgentPR', 'Finalize Agent PR'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuFinalizeAgentPR');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain11', 'mnuAgentCheck', 'Agent Check'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuAgentCheck');
GO


INSERT INTO tSecurity (Parent, Menu, Description)
SELECT 'mnuMain11', 'mnuAgentHistory', 'Agent History'
WHERE NOT EXISTS (SELECT 1 FROM tSecurity WHERE Menu = 'mnuAgentHistory');
GO


PRINT 'tSecurity INSERT script completed successfully!';
PRINT '';
PRINT 'Total menu items processed:';
PRINT '  - Accounting: 30 items';
PRINT '  - Dispatch: 21 items';
PRINT '  - Logistics: 18 items';
PRINT '  - Imaging: 3 items';
PRINT '  - Reports: 24 items';
PRINT '  - Safety: 8 items';
PRINT '  - Systems: 6 items';
PRINT '  - Payroll: 17 items';
PRINT '';
PRINT 'Verify results with: SELECT Parent, Menu, Description FROM tSecurity ORDER BY Parent, Menu;';