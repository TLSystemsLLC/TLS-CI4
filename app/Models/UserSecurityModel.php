<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * User Security Model
 *
 * Handles user permission management using stored procedures.
 *
 * Key Stored Procedures:
 * - spUsers_GetAll: Get all users for dropdown
 * - spUser_Menu: Check if user has permission (0 = granted, non-zero = denied)
 * - spUser_Menu_Save: Save user permission
 *
 * @author Tony Lyle
 * @version 1.0 - CI4 Migration
 */
class UserSecurityModel extends BaseModel
{
    /**
     * Get all users for dropdown selection
     *
     * @return array Array of users with UserID and UserName
     */
    public function getAllUsers(): array
    {
        $results = $this->callStoredProcedure('spUsers_GetAll', []);

        $users = [];
        foreach ($results as $row) {
            $users[] = [
                'user_id' => trim($row['UserID']),
                'user_name' => trim($row['UserName'] ?? $row['UserID'])
            ];
        }

        return $users;
    }

    /**
     * Get all available security items from tSecurity table
     *
     * @return array Array of menu keys with descriptions
     */
    public function getAllSecurityItems(): array
    {
        $query = "SELECT DISTINCT Menu, Description FROM dbo.tSecurity ORDER BY Menu";
        $results = $this->db->query($query)->getResultArray();

        $securityItems = [];
        foreach ($results as $row) {
            $menuKey = trim($row['Menu']);
            if (!empty($menuKey)) {
                $securityItems[$menuKey] = trim($row['Description']) ?: "Access to {$menuKey} functionality";
            }
        }

        return $securityItems;
    }

    /**
     * Get user permissions for all menu items
     *
     * @param string $userId User ID to check permissions for
     * @return array Array of menu keys => boolean (true = granted, false = denied)
     */
    public function getUserPermissions(string $userId): array
    {
        // Get all available security items
        $securityItems = $this->getAllSecurityItems();

        // Default all permissions to false (denied)
        $permissions = [];
        foreach ($securityItems as $menuKey => $description) {
            $permissions[$menuKey] = false;
        }

        // Get user's granted permissions using spUser_Menus (ONE call instead of 150+)
        try {
            $grantedMenus = $this->callStoredProcedure('spUser_Menus', [$userId]);

            // Mark granted permissions as true
            foreach ($grantedMenus as $row) {
                $menuKey = trim($row['MenuName']);
                if (!empty($menuKey) && isset($permissions[$menuKey])) {
                    $permissions[$menuKey] = true;
                }
            }
        } catch (\Exception $e) {
            log_message('error', "Error loading user permissions via spUser_Menus for user {$userId}: " . $e->getMessage());
        }

        return $permissions;
    }

    /**
     * Save single user permission
     *
     * @param string $userId User ID
     * @param string $menuKey Menu key (permission)
     * @param bool $granted True to grant, false to deny
     * @return bool True on success
     */
    public function savePermission(string $userId, string $menuKey, bool $granted): bool
    {
        try {
            $this->callStoredProcedureWithReturn('spUser_Menu_Save', [$userId, $menuKey, $granted ? 1 : 0]);
            return true;
        } catch (\Exception $e) {
            log_message('error', "Error saving permission {$menuKey} for user {$userId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Save multiple permission changes
     *
     * @param string $userId User ID
     * @param array $changes Array of ['menu' => menuKey, 'granted' => boolean]
     * @return int Number of permissions successfully saved
     */
    public function savePermissionChanges(string $userId, array $changes): int
    {
        $savedCount = 0;

        foreach ($changes as $change) {
            $menuKey = $change['menu'] ?? '';
            $granted = $change['granted'] ?? false;

            if (empty($menuKey)) continue;

            if ($this->savePermission($userId, $menuKey, $granted)) {
                $savedCount++;
            }
        }

        return $savedCount;
    }

    /**
     * Get role template permissions from tSecurityGroups
     *
     * @param string $role Role name (dispatch, broker, accounting, etc.)
     * @return array Array of menu keys => true for permissions in this role
     */
    public function getRoleTemplate(string $role): array
    {
        $permissions = [];

        try {
            // Normalize role name (capitalize first letter)
            $roleName = ucfirst(strtolower($role));

            $query = "SELECT Menu FROM dbo.tSecurityGroups WHERE UserGroup = ? ORDER BY Menu";
            $results = $this->db->query($query, [$roleName])->getResultArray();

            foreach ($results as $row) {
                $menuKey = trim($row['Menu']);
                if (!empty($menuKey)) {
                    $permissions[$menuKey] = true;
                }
            }
        } catch (\Exception $e) {
            log_message('error', "Error loading role template '{$role}': " . $e->getMessage());
        }

        return $permissions;
    }

    /**
     * Organize permissions by category using Menus config
     *
     * @return array Array of categories with organized menu items
     */
    public function organizePermissionsByCategory(): array
    {
        $menusConfig = new \App\Config\Menus();
        $securityItems = $this->getAllSecurityItems();
        $organized = [];

        // Process each top-level category from Menus config
        foreach ($menusConfig->structure as $categoryKey => $categoryData) {
            if (isset($categoryData['separator'])) continue;

            $category = [
                'key' => $categoryKey,
                'label' => $categoryData['label'],
                'icon' => $categoryData['icon'] ?? 'bi-gear',
                'items' => []
            ];

            if (isset($categoryData['items'])) {
                $category['items'] = $this->extractMenuItemsFlat($categoryData['items'], $securityItems);
            }

            // Only include categories that have items with database permissions
            if (!empty($category['items'])) {
                $organized[] = $category;
            }
        }

        // Add Security category for 'sec' prefixed items not in regular menus
        $securityOnlyItems = [];
        foreach ($securityItems as $menuKey => $description) {
            if (str_starts_with($menuKey, 'sec')) {
                $securityOnlyItems[] = [
                    'key' => $menuKey,
                    'label' => $description,
                    'description' => $description
                ];
            }
        }

        if (!empty($securityOnlyItems)) {
            $organized[] = [
                'key' => 'security',
                'label' => 'Security',
                'icon' => 'bi-shield-lock',
                'items' => $securityOnlyItems
            ];
        }

        return $organized;
    }

    /**
     * Extract menu items in flat structure for display
     *
     * @param array $items Menu items from config
     * @param array $securityItems Available security items from database
     * @param string $prefix Label prefix for nested items
     * @return array Flat array of menu items
     */
    private function extractMenuItemsFlat(array $items, array $securityItems, string $prefix = ''): array
    {
        $result = [];

        foreach ($items as $key => $data) {
            if (isset($data['separator'])) continue;

            if (isset($data['items'])) {
                // This is a subcategory - recursively extract items
                $subcategoryItems = $this->extractMenuItemsFlat(
                    $data['items'],
                    $securityItems,
                    $data['label'] . ' - '
                );
                $result = array_merge($result, $subcategoryItems);
            } else {
                // Only include menu items that exist in the database security table
                if (isset($securityItems[$key])) {
                    $result[] = [
                        'key' => $key,
                        'label' => $prefix . $data['label'],
                        'description' => $securityItems[$key]
                    ];
                }
            }
        }

        return $result;
    }
}
