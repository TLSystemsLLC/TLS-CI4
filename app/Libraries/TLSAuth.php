<?php

namespace App\Libraries;

use App\Models\BaseModel;
use Config\Services;

/**
 * TLS Authentication Library
 *
 * Handles user login, logout, and session management using CI4's session library
 * and stored procedures for authentication logic
 */
class TLSAuth
{
    protected $session;
    protected $db;

    public function __construct()
    {
        $this->session = Services::session();
        $this->db = \Config\Database::connect();
    }

    /**
     * Authenticate user login
     *
     * @param string $customer Database name (customer)
     * @param string $userId User ID
     * @param string $password User password
     * @return array Login result with success status and user data
     */
    public function login(string $customer, string $userId, string $password): array
    {
        try {
            // Input validation
            if (empty($customer) || empty($userId) || empty($password)) {
                return [
                    'success' => false,
                    'message' => 'All fields are required'
                ];
            }

            // Validate customer ID against master database
            if (!$this->isValidCustomerId($customer)) {
                return [
                    'success' => false,
                    'message' => 'Invalid customer ID specified'
                ];
            }

            // Switch to customer database
            $this->db->setDatabase($customer);

            // Execute login procedure using BaseModel pattern
            $sql = "DECLARE @ReturnValue INT; EXEC @ReturnValue = spUser_Login ?, ?; SELECT @ReturnValue as ReturnValue";
            $query = $this->db->query($sql, [$userId, $password]);
            $result = $query->getRowArray();
            $returnCode = (int)($result['ReturnValue'] ?? -1);

            if ($returnCode === 0) {
                // Login successful - get user menus, details, and company info
                $menus = $this->getUserMenus($userId);
                $userDetails = $this->getUserDetails($userId);
                $companyInfo = $this->getCompanyInfo();

                // Load and cache validation table for entire session
                $validationTable = $this->loadValidationTable();

                // Create user session using CI4's session library
                $sessionData = [
                    'user_id' => $userId,
                    'customer_db' => $customer,
                    'user_menus' => $menus,
                    'user_details' => $userDetails,
                    'company_info' => $companyInfo,
                    'validation_table' => $validationTable,
                    'login_time' => time(),
                    'logged_in' => true
                ];

                $this->session->set($sessionData);

                // Log successful login
                log_message('info', "Successful login: User '{$userId}' to database '{$customer}' - Cached " . count($validationTable) . " validation entries");

                return [
                    'success' => true,
                    'message' => 'Login successful',
                    'user_id' => $userId,
                    'customer' => $customer,
                    'menus' => $menus,
                    'user_details' => $userDetails
                ];
            } else {
                // Login failed
                log_message('warning', "Failed login attempt: User '{$userId}' to database '{$customer}' - Code: {$returnCode}");

                return [
                    'success' => false,
                    'message' => 'Invalid username or password'
                ];
            }

        } catch (\Exception $e) {
            log_message('error', 'Login error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred during login. Please try again.'
            ];
        }
    }

    /**
     * Get user menu permissions
     *
     * @param string $userId User ID
     * @return array User menu permissions
     */
    private function getUserMenus(string $userId): array
    {
        try {
            $query = $this->db->query('EXEC spUser_Menus ?', [$userId]);
            $results = $query->getResultArray();

            $menus = [];
            foreach ($results as $row) {
                $menus[] = trim($row['MenuName']);
            }

            return $menus;

        } catch (\Exception $e) {
            log_message('error', "Error getting user menus for '{$userId}': " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user details from spUser_GetUser
     *
     * @param string $userId User ID
     * @return array User details including UserName (display name)
     */
    private function getUserDetails(string $userId): array
    {
        try {
            // spUser_GetUser uses OUTPUT parameters
            $sql = "DECLARE @TeamKey INT, @UserName VARCHAR(35), @PasswordChanged DATETIME, @Extension INT, @Email VARCHAR(50); " .
                   "EXEC spUser_GetUser ?, @TeamKey OUTPUT, @UserName OUTPUT, @PasswordChanged OUTPUT, @Extension OUTPUT, @Email OUTPUT; " .
                   "SELECT @TeamKey as TeamKey, @UserName as UserName, @PasswordChanged as PasswordChanged, @Extension as Extension, @Email as Email";

            $query = $this->db->query($sql, [$userId]);
            $result = $query->getRowArray();

            return $result ?: [];

        } catch (\Exception $e) {
            log_message('error', "Error getting user details for '{$userId}': " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get company information using spCompany_Get
     *
     * @return array Company details
     */
    private function getCompanyInfo(): array
    {
        try {
            $query = $this->db->query('EXEC spCompany_Get ?', [1]);
            $result = $query->getRowArray();

            return $result ?: [];

        } catch (\Exception $e) {
            log_message('error', "Error getting company info: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if user has access to specific menu
     *
     * @param string $menuName Menu name to check
     * @return bool True if user has access
     */
    public function hasMenuAccess(string $menuName): bool
    {
        if (!$this->isLoggedIn()) {
            return false;
        }

        $userId = $this->session->get('user_id');
        $customerDb = $this->session->get('customer_db');

        try {
            $this->db->setDatabase($customerDb);

            $sql = "DECLARE @ReturnValue INT; EXEC @ReturnValue = spUser_Menu ?, ?; SELECT @ReturnValue as ReturnValue";
            $query = $this->db->query($sql, [$userId, $menuName]);
            $result = $query->getRowArray();
            $returnCode = (int)($result['ReturnValue'] ?? -1);

            return $returnCode === 0;

        } catch (\Exception $e) {
            log_message('error', "Error checking menu access for '{$userId}', menu '{$menuName}': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if user is logged in
     *
     * @return bool True if user is logged in
     */
    public function isLoggedIn(): bool
    {
        if (!$this->session->has('logged_in') || !$this->session->get('logged_in')) {
            return false;
        }

        if (!$this->session->has('user_id') || !$this->session->has('customer_db')) {
            return false;
        }

        // Check session timeout
        $loginTime = $this->session->get('login_time', 0);
        $sessionTimeout = (int)env('SESSION_TIMEOUT', 3600);

        if ((time() - $loginTime) > $sessionTimeout) {
            $this->logout();
            return false;
        }

        return true;
    }

    /**
     * Get current user information
     *
     * @return array|null User information or null if not logged in
     */
    public function getCurrentUser(): ?array
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        $userDetails = $this->session->get('user_details') ?: [];
        $companyInfo = $this->session->get('company_info') ?: [];

        return [
            'user_id' => $this->session->get('user_id'),
            'customer_db' => $this->session->get('customer_db'),
            'menus' => $this->session->get('user_menus') ?: [],
            'login_time' => $this->session->get('login_time'),
            'user_name' => $userDetails['UserName'] ?? $this->session->get('user_id'),
            'user_details' => $userDetails,
            'company_info' => $companyInfo,
            'company_name' => $companyInfo['CompanyName'] ?? $this->session->get('customer_db')
        ];
    }

    /**
     * Logout user
     */
    public function logout(): void
    {
        $userId = $this->session->get('user_id', 'unknown');
        $customerDb = $this->session->get('customer_db', 'unknown');

        $this->session->destroy();

        log_message('info', "User logout: '{$userId}' from database '{$customerDb}'");
    }

    /**
     * Validate customer ID against master database operations list
     *
     * @param string $customerId Customer ID
     * @return bool True if valid customer ID
     */
    private function isValidCustomerId(string $customerId): bool
    {
        // Basic validation first - alphanumeric, underscore, hyphen only
        if (preg_match('/^[a-zA-Z0-9_-]+$/', $customerId) !== 1) {
            return false;
        }

        try {
            // Switch to master database for validation
            $this->db->setDatabase('master');

            // Get valid operations databases
            $query = $this->db->query('EXEC spGetOperationsDB');
            $validDatabases = $query->getResultArray();

            // Check if customer ID is in the valid list
            foreach ($validDatabases as $db) {
                if (strtoupper($db['name']) === strtoupper($customerId)) {
                    return true;
                }
            }

            return false;

        } catch (\Exception $e) {
            log_message('error', "Customer ID validation error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Require authentication - redirect if not logged in
     *
     * @param string $redirectUrl URL to redirect to for login
     */
    public function requireAuth(string $redirectUrl = '/login'): void
    {
        if (!$this->isLoggedIn()) {
            redirect()->to($redirectUrl)->send();
            exit;
        }
    }

    /**
     * Load validation table from database
     * Called once during login to cache for entire session
     *
     * @return array Complete validation table
     */
    private function loadValidationTable(): array
    {
        try {
            // Execute spGetValidationTable
            $sql = "EXEC spGetValidationTable";
            $query = $this->db->query($sql);
            $results = $query->getResultArray();

            return $results ?? [];
        } catch (\Exception $e) {
            log_message('error', 'Error loading validation table: ' . $e->getMessage());
            return [];
        }
    }
}
