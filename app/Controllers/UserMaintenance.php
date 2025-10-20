<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;

/**
 * User Maintenance Controller
 *
 * Handles user creation, editing, and search.
 * NOTE: Uses direct SQL queries instead of stored procedures (non-standard pattern).
 *
 * @author Tony Lyle
 * @version 1.0 - CI4 Migration
 */
class UserMaintenance extends BaseController
{
    private ?UserModel $userModel = null;

    /**
     * Get UserModel instance with correct database context
     * Lazy initialization ensures database context is available
     */
    private function getUserModel(): UserModel
    {
        if ($this->userModel === null) {
            $this->userModel = new UserModel();
        }

        // Ensure the model's database is set to the current customer database
        // Use the database NAME string, not the connection object
        $customerDb = $this->getCurrentDatabase();
        if ($customerDb && $this->userModel->db) {
            $this->userModel->db->setDatabase($customerDb);
        }

        return $this->userModel;
    }

    /**
     * Display user maintenance page
     */
    public function index()
    {
        // Require authentication and permission
        $this->requireAuth();
        $this->requireMenuPermission('mnuUserMaint');

        // Get database with guaranteed tenant context
        $db = $this->getCustomerDb();

        // Note: Model inherits the database connection from BaseController
        // which already has the correct tenant context set

        // Initialize variables
        $user = null;
        $isNewUser = false;

        // Check if this is a new user request
        if ($this->request->getGet('new') == '1') {
            $isNewUser = true;
            $user = $this->getNewUserTemplate();
        }

        // Load lookup tables for dropdowns
        $lookups = $this->getUserModel()->getLookupTables();

        // Prepare view data
        $data = [
            'pageTitle' => 'User Maintenance - TLS Operations',
            'user' => $user,
            'isNewUser' => $isNewUser,
            'userTypes' => $lookups['userTypes'],
            'companies' => $lookups['companies'],
            'divisions' => $lookups['divisions'],
            'departments' => $lookups['departments'],
            'teams' => $lookups['teams']
        ];

        return $this->renderView('systems/user_maintenance', $data);
    }

    /**
     * Handle user search
     */
    public function search()
    {
        // Require authentication and permission
        $this->requireAuth();
        $this->requireMenuPermission('mnuUserMaint');

        // Get database with guaranteed tenant context
        $db = $this->getCustomerDb();

        $searchTerm = trim($this->request->getPost('user_search') ?? '');

        if (empty($searchTerm)) {
            return redirect()->to('/systems/user-maintenance')
                ->with('error', 'User ID is required.');
        }

        // Search for user
        $user = $this->getUserModel()->searchUser($searchTerm);

        if ($user) {
            // Load lookup tables
            $lookups = $this->getUserModel()->getLookupTables();

            // Set flash message
            $this->session->setFlashdata('success', 'User loaded successfully.');

            // Prepare view data
            $data = [
                'pageTitle' => 'User Maintenance - TLS Operations',
                'user' => $user,
                'isNewUser' => false,
                'userTypes' => $lookups['userTypes'],
                'companies' => $lookups['companies'],
                'divisions' => $lookups['divisions'],
                'departments' => $lookups['departments'],
                'teams' => $lookups['teams']
            ];

            return $this->renderView('systems/user_maintenance', $data);
        } else {
            return redirect()->to('/systems/user-maintenance')
                ->with('warning', 'User not found.');
        }
    }

    /**
     * Handle user save (create or update)
     */
    public function save()
    {
        // Require authentication and permission
        $this->requireAuth();
        $this->requireMenuPermission('mnuUserMaint');

        // Get database with guaranteed tenant context
        $db = $this->getCustomerDb();

        // Validate input using CI4 validation
        $validation = \Config\Services::validation();
        $validation->setRules([
            'user_id' => 'required|max_length[15]',
            'user_name' => 'required|max_length[35]',
            'first_name' => 'required|max_length[50]',
            'last_name' => 'required|max_length[50]',
            'email' => 'permit_empty|valid_email|max_length[50]',
            'password' => 'permit_empty|max_length[50]',
            'phone' => 'permit_empty|max_length[20]',
            'fax' => 'permit_empty|max_length[20]',
            'title' => 'permit_empty|max_length[50]',
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            // Validation failed - reload form with errors
            $user = $this->buildUserFromPost();
            $lookups = $this->getUserModel()->getLookupTables();

            $data = [
                'pageTitle' => 'User Maintenance - TLS Operations',
                'user' => $user,
                'isNewUser' => empty($this->request->getPost('original_user_id')),
                'userTypes' => $lookups['userTypes'],
                'companies' => $lookups['companies'],
                'divisions' => $lookups['divisions'],
                'departments' => $lookups['departments'],
                'teams' => $lookups['teams']
            ];

            return $this->renderView('systems/user_maintenance', $data);
        }

        // Get form data
        $userId = trim($this->request->getPost('user_id'));
        $isNewUser = empty($this->request->getPost('original_user_id'));

        try {
            // Prepare data array
            $userData = [
                'UserID' => $userId,
                'UserName' => $this->request->getPost('user_name'),
                'FirstName' => $this->request->getPost('first_name'),
                'LastName' => $this->request->getPost('last_name'),
                'Email' => $this->request->getPost('email'),
                'Password' => $this->request->getPost('password'),
                'Active' => $this->request->getPost('active') ? 1 : 0,
                'TeamKey' => $this->request->getPost('team_key'),
                'Extension' => $this->request->getPost('extension'),
                'HireDate' => $this->request->getPost('hire_date'),
                'TermDate' => $this->request->getPost('term_date'),
                'DepartmentID' => $this->request->getPost('department_id'),
                'Phone' => $this->request->getPost('phone'),
                'Title' => $this->request->getPost('title'),
                'CompanyID' => $this->request->getPost('company_id'),
                'DivisionID' => $this->request->getPost('division_id'),
                'UserType' => $this->request->getPost('user_type'),
                'Fax' => $this->request->getPost('fax'),
                'CommissionPct' => $this->request->getPost('commission_pct'),
                'CommissionMinProfit' => $this->request->getPost('commission_min_profit'),
                'RapidLogUser' => $this->request->getPost('rapid_log_user') ? 1 : 0,
                'SatelliteInstalls' => $this->request->getPost('satellite_installs') ? 1 : 0,
                'PrimaryAccount' => $this->request->getPost('primary_account') ? 1 : 0
            ];

            if ($isNewUser) {
                // Create new user
                if ($this->getUserModel()->createUser($userData)) {
                    return redirect()->to('/systems/user-maintenance')
                        ->with('success', 'User created successfully.');
                } else {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'User ID already exists or creation failed.');
                }
            } else {
                // Update existing user
                if ($this->getUserModel()->updateUser($userId, $userData)) {
                    // Reload the user to show updated data
                    return redirect()->to('/systems/user-maintenance/load/' . urlencode($userId))
                        ->with('success', 'User updated successfully.');
                } else {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'User update failed.');
                }
            }

        } catch (\Exception $e) {
            log_message('error', 'User maintenance save error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Database error occurred.');
        }
    }

    /**
     * Load user by UserID (for redirects after save)
     */
    public function load(string $userId)
    {
        // Require authentication and permission
        $this->requireAuth();
        $this->requireMenuPermission('mnuUserMaint');

        // Get database with guaranteed tenant context
        $db = $this->getCustomerDb();

        // Search for user
        $user = $this->getUserModel()->getUserByUserId($userId);

        if ($user) {
            // Load lookup tables
            $lookups = $this->getUserModel()->getLookupTables();

            // Prepare view data
            $data = [
                'pageTitle' => 'User Maintenance - TLS Operations',
                'user' => $user,
                'isNewUser' => false,
                'userTypes' => $lookups['userTypes'],
                'companies' => $lookups['companies'],
                'divisions' => $lookups['divisions'],
                'departments' => $lookups['departments'],
                'teams' => $lookups['teams']
            ];

            return $this->renderView('systems/user_maintenance', $data);
        } else {
            return redirect()->to('/systems/user-maintenance')
                ->with('warning', 'User not found.');
        }
    }

    /**
     * Autocomplete API endpoint for user search
     */
    public function autocomplete()
    {
        // Require authentication
        $this->requireAuth();
        $this->requireMenuPermission('mnuUserMaint');

        // Get database with guaranteed tenant context
        $db = $this->getCustomerDb();

        $searchTerm = $this->request->getGet('term') ?? '';
        $includeInactive = $this->request->getGet('include_inactive') == '1';

        if (strlen($searchTerm) < 2) {
            return $this->response->setJSON([]);
        }

        $users = $this->getUserModel()->searchUsersForAutocomplete($searchTerm, $includeInactive);

        // Format for autocomplete
        $results = [];
        foreach ($users as $user) {
            $results[] = [
                'id' => $user['UserID'],
                'label' => $user['UserName'] . ' (' . $user['UserID'] . ')',
                'value' => $user['UserID'],
                'active' => $user['Active']
            ];
        }

        return $this->response->setJSON($results);
    }

    /**
     * Get new user template
     *
     * @return array Default values for new user
     */
    private function getNewUserTemplate(): array
    {
        return [
            'UserID' => '',
            'UserName' => '',
            'FirstName' => '',
            'LastName' => '',
            'Email' => '',
            'Password' => '',
            'Active' => 1,
            'TeamKey' => null,
            'Extension' => null,
            'PasswordChanged' => null,
            'HireDate' => null,
            'TermDate' => null,
            'LastLogin' => null,
            'Department' => '',
            'Phone' => '',
            'Title' => '',
            'Fax' => '',
            'DepartmentID' => null,
            'CompanyID' => null,
            'DivisionID' => null,
            'UserType' => null,
            'CommissionPct' => null,
            'CommissionMinProfit' => null,
            'RapidLogUser' => 0,
            'SatelliteInstalls' => 0,
            'PrimaryAccount' => 0,
            'LastUpdate' => null,
            'LastWebAccess' => null
        ];
    }

    /**
     * Build user array from POST data (for validation failure reload)
     *
     * @return array User data from POST
     */
    private function buildUserFromPost(): array
    {
        return [
            'UserID' => $this->request->getPost('user_id'),
            'UserName' => $this->request->getPost('user_name'),
            'FirstName' => $this->request->getPost('first_name'),
            'LastName' => $this->request->getPost('last_name'),
            'Email' => $this->request->getPost('email'),
            'Password' => '', // Don't repopulate password
            'Active' => $this->request->getPost('active') ? 1 : 0,
            'TeamKey' => $this->request->getPost('team_key'),
            'Extension' => $this->request->getPost('extension'),
            'HireDate' => $this->request->getPost('hire_date'),
            'TermDate' => $this->request->getPost('term_date'),
            'DepartmentID' => $this->request->getPost('department_id'),
            'Phone' => $this->request->getPost('phone'),
            'Title' => $this->request->getPost('title'),
            'CompanyID' => $this->request->getPost('company_id'),
            'DivisionID' => $this->request->getPost('division_id'),
            'UserType' => $this->request->getPost('user_type'),
            'Fax' => $this->request->getPost('fax'),
            'CommissionPct' => $this->request->getPost('commission_pct'),
            'CommissionMinProfit' => $this->request->getPost('commission_min_profit'),
            'RapidLogUser' => $this->request->getPost('rapid_log_user') ? 1 : 0,
            'SatelliteInstalls' => $this->request->getPost('satellite_installs') ? 1 : 0,
            'PrimaryAccount' => $this->request->getPost('primary_account') ? 1 : 0
        ];
    }
}
