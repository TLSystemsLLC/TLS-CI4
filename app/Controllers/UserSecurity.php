<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserSecurityModel;

/**
 * User Security Controller
 *
 * Manages user permissions and security settings.
 * Provides interface to grant/deny menu access permissions per user.
 *
 * @author Tony Lyle
 * @version 1.0 - CI4 Migration
 */
class UserSecurity extends BaseController
{
    private ?UserSecurityModel $userSecurityModel = null;

    /**
     * Get UserSecurityModel instance with correct database context
     * Lazy initialization ensures database context is available
     */
    private function getUserSecurityModel(): UserSecurityModel
    {
        if ($this->userSecurityModel === null) {
            $this->userSecurityModel = new UserSecurityModel();
        }

        // Ensure the model's database is set to the current customer database
        $customerDb = $this->getCurrentDatabase();
        if ($customerDb && $this->userSecurityModel->db) {
            $this->userSecurityModel->db->setDatabase($customerDb);
        }

        return $this->userSecurityModel;
    }

    /**
     * Display user security management page
     */
    public function index()
    {
        // Require authentication and permission
        $this->requireAuth();
        $this->requireMenuPermission('mnuUserSecurity');

        // Get database with guaranteed tenant context
        $db = $this->getCustomerDb();

        // Load users for dropdown
        $users = $this->getUserSecurityModel()->getAllUsers();

        // Load organized permission categories
        $organizedPermissions = $this->getUserSecurityModel()->organizePermissionsByCategory();

        // Prepare view data
        $data = [
            'pageTitle' => 'User Security Management - TLS Operations',
            'users' => $users,
            'organizedPermissions' => $organizedPermissions
        ];

        return $this->renderView('systems/user_security', $data);
    }

    /**
     * AJAX endpoint: Get user permissions
     */
    public function getUserPermissions()
    {
        // Require authentication and permission
        $this->requireAuth();
        $this->requireMenuPermission('mnuUserSecurity');

        // Get database with guaranteed tenant context
        $db = $this->getCustomerDb();

        $userId = $this->request->getPost('user_id') ?? '';

        if (empty($userId)) {
            log_message('error', 'getUserPermissions: User ID is empty');
            return $this->response->setJSON([
                'success' => false,
                'error' => 'User ID is required'
            ]);
        }

        log_message('info', "getUserPermissions: Loading permissions for user {$userId}");

        try {
            $permissions = $this->getUserSecurityModel()->getUserPermissions($userId);

            log_message('info', "getUserPermissions: Loaded " . count($permissions) . " permissions for user {$userId}");

            return $this->response->setJSON([
                'success' => true,
                'permissions' => $permissions
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error loading user permissions: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Failed to load user permissions: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * AJAX endpoint: Save permission changes
     */
    public function savePermissions()
    {
        // Require authentication and permission
        $this->requireAuth();
        $this->requireMenuPermission('mnuUserSecurity');

        // Get database with guaranteed tenant context
        $db = $this->getCustomerDb();

        $userId = $this->request->getPost('user_id') ?? '';
        $changesJson = $this->request->getPost('changes') ?? '[]';
        $changes = json_decode($changesJson, true);

        if (empty($userId)) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'User ID is required'
            ]);
        }

        if (empty($changes)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'No changes to save'
            ]);
        }

        try {
            $savedCount = $this->getUserSecurityModel()->savePermissionChanges($userId, $changes);

            return $this->response->setJSON([
                'success' => true,
                'message' => "Successfully saved {$savedCount} permission changes"
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error saving permissions: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Failed to save permissions'
            ]);
        }
    }

    /**
     * AJAX endpoint: Apply role template
     */
    public function applyRoleTemplate()
    {
        // Require authentication and permission
        $this->requireAuth();
        $this->requireMenuPermission('mnuUserSecurity');

        // Get database with guaranteed tenant context
        $db = $this->getCustomerDb();

        $userId = $this->request->getPost('user_id') ?? '';
        $role = $this->request->getPost('role') ?? '';

        if (empty($userId) || empty($role)) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'User ID and role are required'
            ]);
        }

        try {
            // Get role template permissions
            $rolePermissions = $this->getUserSecurityModel()->getRoleTemplate($role);

            if (empty($rolePermissions)) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => "Role template '{$role}' not found or has no permissions"
                ]);
            }

            // Get all security items to set denied permissions
            $allSecurityItems = $this->getUserSecurityModel()->getAllSecurityItems();

            // Build full permission set (grant role permissions, deny others)
            $changes = [];
            foreach ($allSecurityItems as $menuKey => $description) {
                $granted = isset($rolePermissions[$menuKey]);
                $changes[] = [
                    'menu' => $menuKey,
                    'granted' => $granted
                ];
            }

            // Save all permissions
            $savedCount = $this->getUserSecurityModel()->savePermissionChanges($userId, $changes);

            return $this->response->setJSON([
                'success' => true,
                'message' => "Applied '{$role}' role template ({$savedCount} permissions updated)",
                'permissions' => $rolePermissions
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error applying role template: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Failed to apply role template'
            ]);
        }
    }
}
