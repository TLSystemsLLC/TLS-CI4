<?php

namespace App\Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Menu Structure Configuration
 *
 * Defines the complete menu hierarchy for TLS Operations.
 * Menu items are filtered based on user permissions from spUser_Menus (cached in session).
 *
 * Menu Structure:
 * - key: Menu permission key (matches database MenuKey from tMenu)
 * - label: Display text
 * - icon: Bootstrap icon class (optional)
 * - url: Route path (optional, for leaf items)
 * - items: Sub-menu items (optional, for categories)
 * - separator: true (optional, for visual separators)
 *
 * @author Tony Lyle
 * @version 2.0 - CI4 Migration (Pure MVC - Data Only)
 */
class Menus extends BaseConfig
{
    /**
     * Complete menu structure
     * Migrated from /Applications/MAMP/htdocs/tls/config/menus.php
     */
    public array $structure = [
        'accounting' => [
            'label' => 'Accounting',
            'icon' => 'bi-calculator',
            'items' => [
                'gl' => [
                    'label' => 'G/L',
                    'icon' => 'bi-journal-text',
                    'items' => [
                        'mnuCOAMaint' => [
                            'label' => 'Chart of Account Maintenance',
                        ],
                        'mnuGLAccoutHistory' => [
                            'label' => 'Account History',
                        ],
                        'mnuTransactionSearch' => [
                            'label' => 'Transaction Search',
                        ],
                        'separator1' => [
                            'separator' => true,
                        ],
                        'mnuJE' => [
                            'label' => 'Journal Entry',
                        ],
                        'mnuBankRec' => [
                            'label' => 'Bank Reconciliation',
                        ],
                        'separator2' => [
                            'separator' => true,
                        ],
                        'reports' => [
                            'label' => 'Reports',
                            'icon' => 'bi-file-earmark-text',
                            'items' => [
                                'mnuChartofAccounts' => [
                                    'label' => 'Chart of Accounts',
                                ],
                                'separator3' => [
                                    'separator' => true,
                                ],
                                'mnuBalanceSheet' => [
                                    'label' => 'Balance Sheet',
                                ],
                                'mnuIncomeStatement' => [
                                    'label' => 'Income Statement',
                                ],
                                'mnuTrialBalance' => [
                                    'label' => 'Trial Balance',
                                ],
                                'mnuDailyBalance' => [
                                    'label' => 'Daily Balance Summary',
                                ],
                                'mnuFinStmt' => [
                                    'label' => 'All Financial Statements',
                                ],
                                'separator4' => [
                                    'separator' => true,
                                ],
                                'mnuGeneralLedger' => [
                                    'label' => 'General Ledger',
                                ],
                                'mnuTransactionJournal' => [
                                    'label' => 'Transaction Journal',
                                ],
                                'mnuOOAR' => [
                                    'label' => 'Owner Operator Accounts Receivable',
                                ],
                                'mnuAPJournal' => [
                                    'label' => 'AP Journal',
                                ],
                            ],
                        ],
                        'separator5' => [
                            'separator' => true,
                        ],
                        'mnuGLExport' => [
                            'label' => 'GL Export',
                        ],
                    ],
                ],
                'ap' => [
                    'label' => 'A/P',
                    'icon' => 'bi-credit-card',
                    'items' => [
                        'mnuVendorMaint' => [
                            'label' => 'Vendor Maintenance',
                        ],
                        'mnuVendorHist' => [
                            'label' => 'Vendor History',
                        ],
                        'mnuInvoiceSearch' => [
                            'label' => 'Invoice Search',
                        ],
                        'mnuVoucher' => [
                            'label' => 'Voucher Entry',
                        ],
                        'mnuAPInvoiceApproval' => [
                            'label' => 'Invoice Approval',
                        ],
                        'mnuPayables' => [
                            'label' => 'Process Checks',
                        ],
                        'mnuProcessICCheck' => [
                            'label' => 'Process Inter-Company Checks',
                        ],
                    ],
                ],
                'ar' => [
                    'label' => 'A/R',
                    'icon' => 'bi-receipt',
                    'items' => [
                        'mnuCollections' => [
                            'label' => 'Collections Work',
                        ],
                        'mnuReapplyPmt' => [
                            'label' => 'Reapply Payments',
                        ],
                        'mnuARDeposit' => [
                            'label' => 'Deposit Entry',
                        ],
                        'mnuDepositLookup' => [
                            'label' => 'Deposit Lookup',
                        ],
                        'mnuLoadLookup' => [
                            'label' => 'Load Lookup',
                        ],
                        'mnuBillingEntry' => [
                            'label' => 'Billing Entry',
                        ],
                        'mnuCustMaint' => [
                            'label' => 'Customer Maintenance',
                        ],
                        'mnuloadreg' => [
                            'label' => 'Loads Billed Register',
                        ],
                        'separator10' => [
                            'separator' => true,
                        ],
                        'ar_reports' => [
                            'label' => 'Reports',
                            'items' => [
                                'mnuUnbilledLoads' => [
                                    'label' => 'Unbilled Loads',
                                ],
                                'mnuUnbilledWithPaperwork' => [
                                    'label' => 'Unbilled Loads with Paperwork',
                                ],
                            ],
                        ],
                        'separator11' => [
                            'separator' => true,
                        ],
                        'mnuPrintBatch' => [
                            'label' => 'Print Batch of Invoices',
                        ],
                    ],
                ],
                'pr' => [
                    'label' => 'P/R',
                    'icon' => 'bi-people',
                    'items' => [
                        'mnuDriverPRMaint' => [
                            'label' => 'Driver Payroll Maintenance',
                        ],
                        'mnuTrialDriverPR' => [
                            'label' => 'Run Trial Driver PR',
                        ],
                        'mnuPreviewDriverPR' => [
                            'label' => 'Preview Driver PR',
                        ],
                        'mnuFinalizeDriverPR' => [
                            'label' => 'Finalize Driver PR',
                        ],
                    ],
                ],
            ],
        ],
        'dispatch' => [
            'label' => 'Dispatch',
            'icon' => 'bi-truck',
            'items' => [
                'mnuEDILoads' => [
                    'label' => 'Available EDI Loads',
                ],
                'mnuLoadEntry' => [
                    'label' => 'Load Entry',
                    'url' => 'dispatch/load-entry',
                ],
                'mnuAvailLoads' => [
                    'label' => 'Loads Available for Dispatch',
                ],
                'mnuLoadInq' => [
                    'label' => 'Load Inquiry',
                ],
                'mnuCreditCheck' => [
                    'label' => 'Customer Credit Check',
                ],
                'mnuExceptionStatusList' => [
                    'label' => 'Exception Status List',
                ],
                'mnuLookupLoads' => [
                    'label' => 'Lookup Loads by Location',
                ],
                'mnuDailyCount' => [
                    'label' => 'Daily Count',
                ],
                'mnuUnitHistory' => [
                    'label' => 'Unit History',
                ],
                'mnuUnitLastDel' => [
                    'label' => 'Unit Last Delivery',
                ],
                'mnuAgentHistory' => [
                    'label' => 'Agent History',
                ],
                'mnuLocationMaint' => [
                    'label' => 'Location Maintenance',
                ],
                'mnuTrailerPool' => [
                    'label' => 'Trailer Pool Maintenance',
                ],
                'mnuLoadTrailerStatus' => [
                    'label' => 'Load Trailer Status',
                ],
                'mnuEFSLoadCash' => [
                    'label' => 'EFS Load Cash',
                ],
                'mobile' => [
                    'label' => 'Mobile',
                    'items' => [
                        'mnuMobileSendMessage' => [
                            'label' => 'Send Mobile Message',
                        ],
                        'mnuMobileMessages' => [
                            'label' => 'Message Status',
                        ],
                        'mnuPosRptHist' => [
                            'label' => 'Position Report History',
                        ],
                        'mnuMessageSearch' => [
                            'label' => 'Message Search',
                        ],
                    ],
                ],
            ],
        ],
        'logistics' => [
            'label' => 'Logistics',
            'icon' => 'bi-diagram-3',
            'items' => [
                'mnuAvailEDI' => [
                    'label' => 'Available EDI Loads',
                ],
                'mnuLoadEntryLog' => [
                    'label' => 'Load Entry',
                    'url' => 'dispatch/load-entry',
                ],
                'mnuAvailTrucks' => [
                    'label' => 'Available Trucks/Loads',
                ],
                'mnuLoadInqLog' => [
                    'label' => 'Load Inquiry',
                ],
                'mnuCreditCheckLog' => [
                    'label' => 'Customer Credit Check',
                ],
                'mnuLookupLoadsLog' => [
                    'label' => 'Lookup Loads by Location',
                ],
                'mnuBrokerTracking' => [
                    'label' => 'Broker Tracking',
                ],
                'mnuCarrierMaint' => [
                    'label' => 'Carrier Maintenance',
                ],
                'mnuCarrierHistory' => [
                    'label' => 'Carrier History',
                ],
                'mnuPrintBrokerConf' => [
                    'label' => 'Reprint Broker Confirmation',
                ],
            ],
        ],
        'imaging' => [
            'label' => 'Imaging',
            'icon' => 'bi-images',
            'items' => [
                'mnuImageAudit' => [
                    'label' => 'Image Audit',
                ],
                'mnuImageExceptions' => [
                    'label' => 'Image Exceptions',
                ],
            ],
        ],
        'reports' => [
            'label' => 'Reports',
            'icon' => 'bi-file-earmark-bar-graph',
            'items' => [
                'mnuPrintJobs' => [
                    'label' => 'Print Jobs',
                ],
                'submit_reports' => [
                    'label' => 'Submit Reports',
                    'items' => [
                        'mnuDriverMiles' => [
                            'label' => 'Driver Miles',
                        ],
                        'mnuSubmitReportUnitMiles' => [
                            'label' => 'Unit Miles',
                        ],
                    ],
                ],
                'mnuSubmitReport' => [
                    'label' => 'Submit Report Job',
                ],
                'mnuAgingReport' => [
                    'label' => 'Aging Report',
                ],
                'mnuCBAgingReport' => [
                    'label' => 'C/B Aging Report',
                ],
                'mnuCustRevSum' => [
                    'label' => 'Customer Revenue Summary',
                ],
                'mnuUnitRev' => [
                    'label' => 'Unit Revenue Summary',
                ],
                'mnuUnitStateReport' => [
                    'label' => 'Unit State Mileage Summary',
                ],
                'mnuReloadReport' => [
                    'label' => 'Reload Report',
                ],
                'mnuTrafficLanes' => [
                    'label' => 'Traffic Lanes',
                ],
                'mnuPosMap' => [
                    'label' => 'Unit/Load Positions',
                ],
                'mnuOwnerLabels' => [
                    'label' => 'Owner/Driver Labels',
                ],
                'mnuTeamDriverList' => [
                    'label' => 'Team Driver List (Excel)',
                ],
                'mnuActiveDriverList' => [
                    'label' => 'Active Driver Address Listing',
                ],
                'mnuActiveCarrierList' => [
                    'label' => 'Active Carrier Address Listing',
                ],
                'mnuTractorList' => [
                    'label' => 'Tractor Listing',
                ],
                'mnuTrailerList' => [
                    'label' => 'Trailer Listing',
                ],
                'mnuDriverInsRpt' => [
                    'label' => 'Driver Insurance Report',
                ],
                'mnuRecap' => [
                    'label' => 'Weekly Recap',
                ],
                'mnuPrint1099' => [
                    'label' => 'Print 1099',
                ],
                'mnuSMDRReport' => [
                    'label' => 'SMDR Report',
                ],
            ],
        ],
        'safety' => [
            'label' => 'Safety',
            'icon' => 'bi-shield-check',
            'items' => [
                'mnuAgentMaint' => [
                    'label' => 'Agent Maintenance',
                    'url' => 'safety/agent-maintenance',
                ],
                'mnuDriverMaint' => [
                    'label' => 'Driver Maintenance',
                    'url' => 'safety/driver-maintenance',
                ],
                'mnuOwnerMaint' => [
                    'label' => 'Owner Maintenance',
                    'url' => 'safety/owner-maintenance',
                ],
                'mnuUnitMaint' => [
                    'label' => 'Unit Maintenance',
                ],
                'mnuTractorTrailer' => [
                    'label' => 'Tractor/Trailer Lookup',
                ],
                'mnuDriverPictures' => [
                    'label' => 'Load Driver Pictures',
                ],
                'mnuFuel' => [
                    'label' => 'Fuel Receipts',
                ],
                'mnuEFSMoneyCode' => [
                    'label' => 'Issue EFS Money Code (Paper Check)',
                ],
                'efs_maintenance' => [
                    'label' => 'EFS Maintenance',
                    'items' => [
                        'mnuEFSPolicyMaintenance' => [
                            'label' => 'EFS Policy Maintenance',
                        ],
                        'mnuEFSCardMaintenance' => [
                            'label' => 'EFS Card Maintenance',
                        ],
                    ],
                ],
            ],
        ],
        'payroll' => [
            'label' => 'Payroll',
            'icon' => 'bi-clock',
            'items' => [
                'mnuTimeClockInOut' => [
                    'label' => 'Clock In/Out',
                ],
                'separator_payroll1' => [
                    'separator' => true,
                ],
                'owner_settlements' => [
                    'label' => 'Unit Settlements',
                    'items' => [
                        'mnuPayrollMaint' => [
                            'label' => 'Payroll Maintenance',
                        ],
                        'mnuUnitCheck' => [
                            'label' => 'Unit Check Lookup',
                        ],
                        'separator_owner1' => [
                            'separator' => true,
                        ],
                        'mnuCreateRepeating' => [
                            'label' => 'Create Repeating Deductions',
                        ],
                        'mnuTrialOwnerPR' => [
                            'label' => 'Run Trial Settlements',
                        ],
                        'mnuPreviewOwnerPR' => [
                            'label' => 'Preview Settlements',
                        ],
                        'mnuFinalizeOwnerPR' => [
                            'label' => 'Finalize Settlements',
                        ],
                        'mnuPrintOwnerPR' => [
                            'label' => 'Print Checks',
                        ],
                        'mnuPrintOwnerDD' => [
                            'label' => 'Print Direct Deposits',
                        ],
                    ],
                ],
                'agent_settlements' => [
                    'label' => 'Agent Settlements',
                    'items' => [
                        'mnuAgentCheck' => [
                            'label' => 'Agent Check Lookup',
                        ],
                        'separator_agent1' => [
                            'separator' => true,
                        ],
                        'mnuTrialAgentPR' => [
                            'label' => 'Run Trial Settlements',
                        ],
                        'mnuPreviewAgentPR' => [
                            'label' => 'Preview Settlements',
                        ],
                        'mnuFinalizeAgentPR' => [
                            'label' => 'Finalize Settlements',
                        ],
                        'mnuPrintAgentPR' => [
                            'label' => 'Print Settlements',
                        ],
                    ],
                ],
                'carrier_settlements' => [
                    'label' => 'Carrier Settlements',
                    'items' => [
                        'mnuCarrierCheck' => [
                            'label' => 'Carrier Check Lookup',
                        ],
                        'separator_carrier2' => [
                            'separator' => true,
                        ],
                        'mnuLogTrial' => [
                            'label' => 'Run Trial Settlements',
                        ],
                        'mnuLogPreview' => [
                            'label' => 'Preview Settlements',
                        ],
                        'mnuLogFinal' => [
                            'label' => 'Finalize Settlements',
                        ],
                        'mnuLogPrint' => [
                            'label' => 'Print Settlements',
                        ],
                        'mnuReassignCarrierLoad' => [
                            'label' => 'Re-Assign Carrier Load',
                        ],
                    ],
                ],
            ],
        ],
        'system' => [
            'label' => 'Systems',
            'icon' => 'bi-gear',
            'items' => [
                'mnuProjectLog' => [
                    'label' => 'Support Request',
                ],
                'mnuTLSBilling' => [
                    'label' => 'TL Systems Billing',
                ],
                'mnuUserMaint' => [
                    'label' => 'User Maintenance',
                    'url' => 'systems/user-maintenance',
                ],
                'mnuUserSecurity' => [
                    'label' => 'User Security',
                    'url' => 'systems/user-security',
                ],
                'mnuEDIUserProfile' => [
                    'label' => 'EDI User Profile',
                ],
                'development' => [
                    'label' => 'Development',
                    'items' => [
                        'devTrailerTypeColor' => [
                            'label' => 'Trailer Type Colors',
                        ],
                        'devExportMenu' => [
                            'label' => 'Export Menu',
                        ],
                    ],
                ],
            ],
        ],
    ];
}
