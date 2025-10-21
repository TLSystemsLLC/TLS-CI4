<?php

namespace App\Models;

use App\Models\BaseModel;

class DivisionModel extends BaseModel
{
    protected $table = 'tDivision';
    protected $primaryKey = ['CompanyID', 'DivisionID'];

    /**
     * Get all divisions for a company
     *
     * @param int $companyID The company ID
     * @param bool $includeInactive Include inactive divisions
     * @return array Array of divisions
     */
    public function getDivisionsByCompany(int $companyID, bool $includeInactive = false): array
    {
        $results = $this->callStoredProcedure('spDivisions_GetByCompany', [$companyID, $includeInactive ? 1 : 0]);
        return $results ?? [];
    }

    /**
     * Get a single division by CompanyID and DivisionID
     *
     * @param int $companyID The company ID
     * @param int $divisionID The division ID
     * @return array|null Division data or null if not found
     */
    public function getDivision(int $companyID, int $divisionID): ?array
    {
        $results = $this->callStoredProcedure('spDivision_Get', [$companyID, $divisionID]);
        return !empty($results) ? $results[0] : null;
    }

    /**
     * Save (create or update) a division
     *
     * @param array $divisionData Division data array
     * @return int Return code (0 = success, 97 = failed, 98 = invalid parent)
     */
    public function saveDivision(array $divisionData): int
    {
        $params = [
            $divisionData['CompanyID'] ?? 0,
            $divisionData['DivisionID'] ?? 0,
            $divisionData['Name'] ?? null,
            $divisionData['Address'] ?? null,
            $divisionData['City'] ?? null,
            $divisionData['State'] ?? null,
            $divisionData['Zip'] ?? null,
            $divisionData['Phone'] ?? null,
            $divisionData['Fax'] ?? null,
            $divisionData['MainContact'] ?? null,
            $divisionData['SafetyContact'] ?? null,
            isset($divisionData['Active']) ? ($divisionData['Active'] ? 1 : 0) : 1,
            $divisionData['AccountingContact'] ?? null
        ];

        return $this->callStoredProcedureWithReturn('spDivision_Save', $params);
    }

    /**
     * Get next available DivisionID for a company
     *
     * @param int $companyID The company ID
     * @return int Next division ID
     */
    public function getNextDivisionID(int $companyID): int
    {
        // Get all divisions for this company and find max DivisionID
        $divisions = $this->getDivisionsByCompany($companyID, true);

        if (empty($divisions)) {
            return 1; // First division
        }

        $maxID = 0;
        foreach ($divisions as $division) {
            if ($division['DivisionID'] > $maxID) {
                $maxID = $division['DivisionID'];
            }
        }

        return $maxID + 1;
    }
}
