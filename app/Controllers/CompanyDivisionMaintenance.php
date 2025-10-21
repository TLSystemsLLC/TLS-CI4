<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CompanyModel;
use App\Models\DivisionModel;
use App\Models\DepartmentModel;
use App\Models\TeamModel;

class CompanyDivisionMaintenance extends BaseController
{
    private $companyModel;
    private $divisionModel;
    private $departmentModel;
    private $teamModel;

    public function index()
    {
        $this->requireAuth();
        $this->requireMenuPermission('mnuCompanyDivisionMaint');

        // Get all companies for the grid
        $companies = $this->getCompanyModel()->getAllCompanies();

        $data = [
            'pageTitle' => 'Company & Division Maintenance',
            'companies' => $companies,
            'user' => $this->getCurrentUser()
        ];

        return $this->renderView('systems/company_division_maintenance', $data);
    }

    // ====================
    // Company Endpoints
    // ====================

    public function loadCompany(int $companyID)
    {
        $this->requireAuth();
        $this->requireMenuPermission('mnuCompanyDivisionMaint');

        $company = $this->getCompanyModel()->getCompany($companyID);

        if (!$company) {
            return $this->response->setJSON(['success' => false, 'message' => 'Company not found']);
        }

        // Clean UTF-8 encoding issues from database
        array_walk_recursive($company, function(&$value) {
            if (is_string($value)) {
                $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
            }
        });

        return $this->response->setJSON(['success' => true, 'company' => $company]);
    }

    public function saveCompany()
    {
        $this->requireAuth();
        $this->requireMenuPermission('mnuCompanyDivisionMaint');

        $companyData = $this->request->getPost();

        // Server-side validation
        $validation = \Config\Services::validation();
        $validation->setRules([
            'CompanyName' => 'required|min_length[3]',
            'ShortName' => 'permit_empty|max_length[20]'
        ]);

        if (!$validation->run($companyData)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validation->getErrors()
            ]);
        }

        $returnCode = $this->getCompanyModel()->saveCompany($companyData);

        if ($returnCode === BaseModel::SRV_NORMAL) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Company saved successfully',
                'companyID' => $companyData['CompanyID']
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to save company. Error code: ' . $returnCode
            ]);
        }
    }

    public function createNewCompany()
    {
        $this->requireAuth();
        $this->requireMenuPermission('mnuCompanyDivisionMaint');

        // Get next CompanyID
        $companyID = $this->getCompanyModel()->getNextCompanyID();

        // Create minimal company record
        $companyData = [
            'CompanyID' => $companyID,
            'CompanyName' => 'New Company',
            'Active' => 1
        ];

        $returnCode = $this->getCompanyModel()->saveCompany($companyData);

        if ($returnCode === BaseModel::SRV_NORMAL) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'New company created successfully',
                'companyID' => $companyID
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to create new company. Error code: ' . $returnCode
            ]);
        }
    }

    // ====================
    // Division Endpoints
    // ====================

    public function getDivisions(int $companyID)
    {
        $this->requireAuth();
        $this->requireMenuPermission('mnuCompanyDivisionMaint');

        $includeInactive = $this->request->getGet('includeInactive') === '1';
        $divisions = $this->getDivisionModel()->getDivisionsByCompany($companyID, $includeInactive);

        return $this->response->setJSON(['success' => true, 'divisions' => $divisions]);
    }

    public function loadDivision(int $companyID, int $divisionID)
    {
        $this->requireAuth();
        $this->requireMenuPermission('mnuCompanyDivisionMaint');

        $division = $this->getDivisionModel()->getDivision($companyID, $divisionID);

        if (!$division) {
            return $this->response->setJSON(['success' => false, 'message' => 'Division not found']);
        }

        return $this->response->setJSON(['success' => true, 'division' => $division]);
    }

    public function saveDivision()
    {
        $this->requireAuth();
        $this->requireMenuPermission('mnuCompanyDivisionMaint');

        $divisionData = $this->request->getPost();

        // Server-side validation
        $validation = \Config\Services::validation();
        $validation->setRules([
            'CompanyID' => 'required|integer',
            'DivisionID' => 'required|integer',
            'Name' => 'required|min_length[3]'
        ]);

        if (!$validation->run($divisionData)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validation->getErrors()
            ]);
        }

        $returnCode = $this->getDivisionModel()->saveDivision($divisionData);

        if ($returnCode === BaseModel::SRV_NORMAL) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Division saved successfully'
            ]);
        } elseif ($returnCode === BaseModel::SRV_INVALIDPARENT) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid company. Please ensure the company exists.'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to save division. Error code: ' . $returnCode
            ]);
        }
    }

    public function createNewDivision()
    {
        $this->requireAuth();
        $this->requireMenuPermission('mnuCompanyDivisionMaint');

        $companyID = $this->request->getPost('CompanyID');

        if (!$companyID) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'CompanyID is required'
            ]);
        }

        // Get next DivisionID
        $divisionID = $this->getDivisionModel()->getNextDivisionID($companyID);

        // Create minimal division record
        $divisionData = [
            'CompanyID' => $companyID,
            'DivisionID' => $divisionID,
            'Name' => 'New Division',
            'Active' => 1
        ];

        $returnCode = $this->getDivisionModel()->saveDivision($divisionData);

        if ($returnCode === BaseModel::SRV_NORMAL) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'New division created successfully',
                'divisionID' => $divisionID
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to create new division. Error code: ' . $returnCode
            ]);
        }
    }

    // ====================
    // Department Endpoints
    // ====================

    public function getDepartments(int $companyID, int $divisionID)
    {
        $this->requireAuth();
        $this->requireMenuPermission('mnuCompanyDivisionMaint');

        $includeInactive = $this->request->getGet('includeInactive') === '1';
        $departments = $this->getDepartmentModel()->getDepartmentsByDivision($companyID, $divisionID, $includeInactive);

        return $this->response->setJSON(['success' => true, 'departments' => $departments]);
    }

    public function saveDepartment()
    {
        $this->requireAuth();
        $this->requireMenuPermission('mnuCompanyDivisionMaint');

        $departmentData = $this->request->getPost();

        // Server-side validation
        $validation = \Config\Services::validation();
        $validation->setRules([
            'CompanyID' => 'required|integer',
            'DivisionID' => 'required|integer',
            'DepartmentID' => 'required|integer',
            'Description' => 'required|min_length[3]'
        ]);

        if (!$validation->run($departmentData)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validation->getErrors()
            ]);
        }

        $returnCode = $this->getDepartmentModel()->saveDepartment($departmentData);

        if ($returnCode === BaseModel::SRV_NORMAL) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Department saved successfully'
            ]);
        } elseif ($returnCode === BaseModel::SRV_INVALIDPARENT) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid division. Please ensure the division exists.'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to save department. Error code: ' . $returnCode
            ]);
        }
    }

    public function deleteDepartment(int $companyID, int $divisionID, int $departmentID)
    {
        $this->requireAuth();
        $this->requireMenuPermission('mnuCompanyDivisionMaint');

        $returnCode = $this->getDepartmentModel()->deleteDepartment($companyID, $divisionID, $departmentID);

        if ($returnCode === BaseModel::SRV_NORMAL) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Department deleted successfully'
            ]);
        } elseif ($returnCode === BaseModel::SRV_NOTFOUND) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Department not found'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to delete department. Error code: ' . $returnCode
            ]);
        }
    }

    // ====================
    // Team Endpoints
    // ====================

    public function getTeams(int $companyID, int $divisionID)
    {
        $this->requireAuth();
        $this->requireMenuPermission('mnuCompanyDivisionMaint');

        $teams = $this->getTeamModel()->getTeamsByDivision($companyID, $divisionID);

        return $this->response->setJSON(['success' => true, 'teams' => $teams]);
    }

    public function saveTeam()
    {
        $this->requireAuth();
        $this->requireMenuPermission('mnuCompanyDivisionMaint');

        $teamData = $this->request->getPost();

        // Server-side validation
        $validation = \Config\Services::validation();
        $validation->setRules([
            'TeamKey' => 'required|integer',
            'TeamName' => 'required|min_length[3]'
        ]);

        if (!$validation->run($teamData)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validation->getErrors()
            ]);
        }

        $returnCode = $this->getTeamModel()->saveTeam($teamData);

        if ($returnCode === BaseModel::SRV_NORMAL) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Team saved successfully'
            ]);
        } elseif ($returnCode === BaseModel::SRV_INVALIDPARENT) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid division. Please ensure the division exists.'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to save team. Error code: ' . $returnCode
            ]);
        }
    }

    public function deleteTeam(int $teamKey)
    {
        $this->requireAuth();
        $this->requireMenuPermission('mnuCompanyDivisionMaint');

        $returnCode = $this->getTeamModel()->deleteTeam($teamKey);

        if ($returnCode === BaseModel::SRV_NORMAL) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Team deleted successfully. Note: TeamKey has been set to NULL in related User and Unit records.'
            ]);
        } elseif ($returnCode === BaseModel::SRV_NOTFOUND) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Team not found'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to delete team. Error code: ' . $returnCode
            ]);
        }
    }

    // ====================
    // Model Helpers (Lazy Initialization)
    // ====================

    private function getCompanyModel(): CompanyModel
    {
        if ($this->companyModel === null) {
            $this->companyModel = new CompanyModel();
        }

        // Ensure the model's database is set to the current customer database
        $customerDb = $this->getCurrentDatabase();
        if ($customerDb && $this->companyModel->db) {
            $this->companyModel->db->setDatabase($customerDb);
        }

        return $this->companyModel;
    }

    private function getDivisionModel(): DivisionModel
    {
        if ($this->divisionModel === null) {
            $this->divisionModel = new DivisionModel();
        }

        // Ensure the model's database is set to the current customer database
        $customerDb = $this->getCurrentDatabase();
        if ($customerDb && $this->divisionModel->db) {
            $this->divisionModel->db->setDatabase($customerDb);
        }

        return $this->divisionModel;
    }

    private function getDepartmentModel(): DepartmentModel
    {
        if ($this->departmentModel === null) {
            $this->departmentModel = new DepartmentModel();
        }

        // Ensure the model's database is set to the current customer database
        $customerDb = $this->getCurrentDatabase();
        if ($customerDb && $this->departmentModel->db) {
            $this->departmentModel->db->setDatabase($customerDb);
        }

        return $this->departmentModel;
    }

    private function getTeamModel(): TeamModel
    {
        if ($this->teamModel === null) {
            $this->teamModel = new TeamModel();
        }

        // Ensure the model's database is set to the current customer database
        $customerDb = $this->getCurrentDatabase();
        if ($customerDb && $this->teamModel->db) {
            $this->teamModel->db->setDatabase($customerDb);
        }

        return $this->teamModel;
    }
}
