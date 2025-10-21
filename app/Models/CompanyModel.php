<?php

namespace App\Models;

use App\Models\BaseModel;

class CompanyModel extends BaseModel
{
    protected $table = 'tCompany';
    protected $primaryKey = 'CompanyID';

    /**
     * Get all companies for grid display
     *
     * @param bool $includeInactive Include inactive companies
     * @return array Array of companies
     */
    public function getAllCompanies(bool $includeInactive = false): array
    {
        $results = $this->callStoredProcedure('spCompanies_GetAll', [$includeInactive ? 1 : 0]);
        return $results ?? [];
    }

    /**
     * Get a single company by CompanyID
     *
     * @param int $companyID The company ID
     * @return array|null Company data or null if not found
     */
    public function getCompany(int $companyID): ?array
    {
        $results = $this->callStoredProcedure('spCompany_Get', [$companyID]);
        return !empty($results) ? $results[0] : null;
    }

    /**
     * Save (create or update) a company
     *
     * @param array $companyData Company data array
     * @return int Return code (0 = success, 97 = failed)
     */
    public function saveCompany(array $companyData): int
    {
        $params = [
            $companyData['CompanyID'] ?? 0,
            $companyData['CompanyName'] ?? null,
            $companyData['MailingAddress'] ?? null,
            $companyData['MailingCity'] ?? null,
            $companyData['MailingState'] ?? null,
            $companyData['MailingZip'] ?? null,
            $companyData['ShippingAddress'] ?? null,
            $companyData['ShippingCity'] ?? null,
            $companyData['ShippingState'] ?? null,
            $companyData['ShippingZip'] ?? null,
            $companyData['MainPhone'] ?? null,
            $companyData['MainFax'] ?? null,
            $companyData['SCAC'] ?? null,
            $companyData['DUNS'] ?? null,
            $companyData['ICC'] ?? null,
            $companyData['DOT'] ?? null,
            $companyData['FID'] ?? null,
            isset($companyData['Active']) ? ($companyData['Active'] ? 1 : 0) : 1,
            $companyData['APAccount'] ?? null,
            $companyData['ARAccount'] ?? null,
            $companyData['BadDebtAccount'] ?? null,
            $companyData['MiscAccount'] ?? null,
            $companyData['FreightRevAccount'] ?? null,
            $companyData['BrokerRevAccount'] ?? null,
            $companyData['FreightPayableAccount'] ?? null,
            $companyData['GeneralBankAccount'] ?? null,
            $companyData['SettlementBankAccount'] ?? null,
            $companyData['SettlementClearingAccount'] ?? null,
            isset($companyData['FreightDetailPost']) ? ($companyData['FreightDetailPost'] ? 1 : 0) : null,
            $companyData['InterCompanyClearing'] ?? null,
            $companyData['FTLBillingFee'] ?? null,
            $companyData['FTLLogisticsFee'] ?? null,
            $companyData['InterCompanyAR'] ?? null,
            $companyData['InterCompanyAP'] ?? null,
            $companyData['FrieghtRevExp'] ?? null,
            $companyData['SystemRemitVendor'] ?? null,
            $companyData['ShortName'] ?? null,
            $companyData['CompanyFreightRevenue'] ?? null,
            $companyData['CompanyFreightExpense'] ?? null,
            $companyData['CompanyTruckFuelExpense'] ?? null,
            $companyData['CompanyReeferFuelExpense'] ?? null,
            $companyData['DriverAR'] ?? null,
            $companyData['OwnerSettlementStep'] ?? null,
            isset($companyData['ComdataInterface']) ? ($companyData['ComdataInterface'] ? 1 : 0) : null,
            isset($companyData['TranfloMobileInterface']) ? ($companyData['TranfloMobileInterface'] ? 1 : 0) : null,
            $companyData['RetainedEarningsAccount'] ?? null,
            $companyData['ifta_miles_lock'] ?? null
        ];

        return $this->callStoredProcedureWithReturn('spCompany_Save', $params);
    }

    /**
     * Get next available CompanyID from tSurrogateKey
     *
     * @return int Next company ID
     */
    public function getNextCompanyID(): int
    {
        return $this->getNextKey('Company');
    }
}
