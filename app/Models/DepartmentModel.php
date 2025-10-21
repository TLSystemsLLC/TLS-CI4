<?php

namespace App\Models;

use App\Models\BaseModel;

class DepartmentModel extends BaseModel
{
    protected $table = 'tDepartment';
    protected $primaryKey = ['CompanyID', 'DivisionID', 'DepartmentID'];

    /**
     * Get all departments for a division
     *
     * @param int $companyID The company ID
     * @param int $divisionID The division ID
     * @param bool $includeInactive Include inactive departments
     * @return array Array of departments
     */
    public function getDepartmentsByDivision(int $companyID, int $divisionID, bool $includeInactive = false): array
    {
        $results = $this->callStoredProcedure('spDepartments_GetByDivision', [$companyID, $divisionID, $includeInactive ? 1 : 0]);
        return $results ?? [];
    }

    /**
     * Get a single department by CompanyID, DivisionID, and DepartmentID
     *
     * @param int $companyID The company ID
     * @param int $divisionID The division ID
     * @param int $departmentID The department ID
     * @return array|null Department data or null if not found
     */
    public function getDepartment(int $companyID, int $divisionID, int $departmentID): ?array
    {
        $results = $this->callStoredProcedure('spDepartment_Get', [$companyID, $divisionID, $departmentID]);
        return !empty($results) ? $results[0] : null;
    }

    /**
     * Save (create or update) a department
     *
     * @param array $departmentData Department data array
     * @return int Return code (0 = success, 97 = failed, 98 = invalid parent)
     */
    public function saveDepartment(array $departmentData): int
    {
        $params = [
            $departmentData['CompanyID'] ?? 0,
            $departmentData['DivisionID'] ?? 0,
            $departmentData['DepartmentID'] ?? 0,
            $departmentData['Description'] ?? '',
            isset($departmentData['Active']) ? ($departmentData['Active'] ? 1 : 0) : 1
        ];

        return $this->callStoredProcedureWithReturn('spDepartment_Save', $params);
    }

    /**
     * Delete a department
     *
     * @param int $companyID The company ID
     * @param int $divisionID The division ID
     * @param int $departmentID The department ID
     * @return int Return code (0 = success, 99 = not found)
     */
    public function deleteDepartment(int $companyID, int $divisionID, int $departmentID): int
    {
        return $this->callStoredProcedureWithReturn('spDepartment_Delete', [$companyID, $divisionID, $departmentID]);
    }

    /**
     * Get next available DepartmentID for a division
     *
     * @param int $companyID The company ID
     * @param int $divisionID The division ID
     * @return int Next department ID
     */
    public function getNextDepartmentID(int $companyID, int $divisionID): int
    {
        // Get all departments for this division and find max DepartmentID
        $departments = $this->getDepartmentsByDivision($companyID, $divisionID, true);

        if (empty($departments)) {
            return 1; // First department
        }

        $maxID = 0;
        foreach ($departments as $department) {
            if ($department['DepartmentID'] > $maxID) {
                $maxID = $department['DepartmentID'];
            }
        }

        return $maxID + 1;
    }
}
