<?php

namespace App\Libraries;

use App\Config\Menus;
use CodeIgniter\Session\Session;

/**
 * Menu Manager Library
 *
 * Pure MVC data provider for menu structure.
 * Returns filtered menu arrays based on user permissions from session.
 * DOES NOT generate HTML - that's the view layer's responsibility.
 *
 * User permissions are loaded by TLSAuth at login via spUser_Menus
 * and cached in session for performance.
 *
 * @author Tony Lyle
 * @version 2.0 - CI4 Migration (Pure MVC)
 */
class MenuManager
{
    /**
     * Menu configuration
     *
     * @var array
     */
    private array $menuConfig;

    /**
     * User's menu permissions (from session)
     *
     * @var array
     */
    private array $userMenus;

    /**
     * Session instance
     *
     * @var Session
     */
    private Session $session;

    /**
     * Constructor
     *
     * @param Session $session CodeIgniter session instance
     */
    public function __construct(Session $session)
    {
        $this->session = $session;

        // Load menu configuration
        $menusConfig = new Menus();
        $this->menuConfig = $menusConfig->structure;

        // Get user menus from session (populated by TLSAuth at login via spUser_Menus)
        $this->userMenus = $this->session->get('user_menus') ?? [];
    }

    /**
     * Get complete menu structure with permission filtering applied
     *
     * Returns array of menu items with:
     * - key: Menu key
     * - label: Display text
     * - icon: Bootstrap icon class (if set)
     * - url: Route path (if set)
     * - hasAccess: Boolean indicating user has access
     * - hasChildren: Boolean indicating sub-items exist
     * - items: Array of sub-menu items (recursive structure)
     *
     * @return array Filtered menu structure
     */
    public function getMenuStructure(): array
    {
        return $this->buildMenuStructure($this->menuConfig);
    }

    /**
     * Build menu structure recursively with permission filtering
     *
     * @param array $menuItems Menu items to process
     * @return array Processed menu structure
     */
    private function buildMenuStructure(array $menuItems): array
    {
        $result = [];

        foreach ($menuItems as $key => $data) {
            // Skip separators (handled in view layer)
            if (isset($data['separator']) && $data['separator'] === true) {
                $result[] = [
                    'key' => $key,
                    'separator' => true
                ];
                continue;
            }

            // Check if user has access to this menu or any of its children
            $hasAccess = $this->hasMenuAccess($key, $data);

            // Skip items without access
            if (!$hasAccess) {
                continue;
            }

            // Build menu item
            $item = [
                'key' => $key,
                'label' => $data['label'] ?? '',
                'icon' => $data['icon'] ?? null,
                'url' => $data['url'] ?? null,
                'hasAccess' => true,
                'hasChildren' => isset($data['items']) && !empty($data['items']),
                'items' => []
            ];

            // Process child items recursively
            if ($item['hasChildren']) {
                $item['items'] = $this->buildMenuStructure($data['items']);

                // If no children have access after filtering, don't show parent
                if (empty($item['items'])) {
                    continue;
                }
            }

            $result[] = $item;
        }

        return $result;
    }

    /**
     * Check if user has access to menu item
     *
     * Access is granted if:
     * - User has direct permission to this menu key
     * - User has permission to any child menu (for categories)
     *
     * Security permissions (keys starting with 'sec') are NEVER accessible.
     *
     * @param string $menuKey Menu key to check
     * @param array $menuData Menu data (optional, for child checking)
     * @return bool True if user has access
     */
    public function hasMenuAccess(string $menuKey, array $menuData = null): bool
    {
        // Security menus (starting with 'sec') are NEVER visible
        if (str_starts_with($menuKey, 'sec')) {
            return false;
        }

        // Check if user has direct access to this menu item
        if (in_array($menuKey, $this->userMenus)) {
            return true;
        }

        // For categories with children, check if user has access to ANY child
        if ($menuData !== null && isset($menuData['items']) && !empty($menuData['items'])) {
            return $this->hasAccessToAnyChild($menuData['items']);
        }

        // If menu data not provided, search in config
        if ($menuData === null) {
            $menuData = $this->findMenuInConfig($menuKey, $this->menuConfig);
            if ($menuData && isset($menuData['items']) && !empty($menuData['items'])) {
                return $this->hasAccessToAnyChild($menuData['items']);
            }
        }

        return false;
    }

    /**
     * Check if user has access to any child menu items recursively
     *
     * @param array $menuItems Menu items to check
     * @return bool True if user has access to any child
     */
    private function hasAccessToAnyChild(array $menuItems): bool
    {
        foreach ($menuItems as $childKey => $childData) {
            // Skip separators
            if (isset($childData['separator']) && $childData['separator'] === true) {
                continue;
            }

            // Check if user has access to this specific menu item
            if (in_array($childKey, $this->userMenus)) {
                return true;
            }

            // If this child has sub-items, check recursively
            if (isset($childData['items']) && !empty($childData['items'])) {
                if ($this->hasAccessToAnyChild($childData['items'])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Find menu data by key in the menu configuration recursively
     *
     * @param string $menuKey Menu key to find
     * @param array $menuConfig Menu configuration to search
     * @return array|null Menu data if found, null otherwise
     */
    private function findMenuInConfig(string $menuKey, array $menuConfig): ?array
    {
        foreach ($menuConfig as $key => $data) {
            if ($key === $menuKey) {
                return $data;
            }

            if (isset($data['items'])) {
                $result = $this->findMenuInConfig($menuKey, $data['items']);
                if ($result !== null) {
                    return $result;
                }
            }
        }

        return null;
    }

    /**
     * Get breadcrumb path for current menu
     *
     * Returns array of breadcrumb items from root to current menu:
     * [
     *   ['key' => 'accounting', 'label' => 'Accounting', 'url' => null],
     *   ['key' => 'gl', 'label' => 'G/L', 'url' => null],
     *   ['key' => 'mnuCOAMaint', 'label' => 'Chart of Account Maintenance', 'url' => 'accounting/coa']
     * ]
     *
     * @param string $currentMenu Current menu key
     * @return array Breadcrumb path
     */
    public function getBreadcrumbPath(string $currentMenu): array
    {
        $path = $this->findMenuPath($currentMenu, $this->menuConfig, []);

        // Convert to breadcrumb format
        $breadcrumbs = [];
        foreach ($path as $item) {
            $breadcrumbs[] = [
                'key' => $item['key'] ?? '',
                'label' => $item['label'] ?? '',
                'url' => $item['url'] ?? null
            ];
        }

        return $breadcrumbs;
    }

    /**
     * Find path to menu item for breadcrumbs (recursive)
     *
     * @param string $menuKey Menu key to find
     * @param array $menuData Menu data to search
     * @param array $path Current path (for recursion)
     * @return array Path to menu item
     */
    private function findMenuPath(string $menuKey, array $menuData, array $path = []): array
    {
        foreach ($menuData as $key => $data) {
            // Skip separators
            if (isset($data['separator']) && $data['separator'] === true) {
                continue;
            }

            if ($key === $menuKey) {
                // Found it - return path including this item
                return array_merge($path, [array_merge(['key' => $key], $data)]);
            }

            if (isset($data['items']) && !empty($data['items'])) {
                // Search recursively in children
                $result = $this->findMenuPath(
                    $menuKey,
                    $data['items'],
                    array_merge($path, [array_merge(['key' => $key], $data)])
                );

                if (!empty($result)) {
                    return $result;
                }
            }
        }

        return [];
    }

    /**
     * Get count of accessible menu items (for display purposes)
     *
     * @return int Number of menu items user can access
     */
    public function getAccessibleMenuCount(): int
    {
        // Filter out security permissions
        $accessibleMenus = array_filter($this->userMenus, function($menu) {
            return !str_starts_with($menu, 'sec');
        });

        return count($accessibleMenus);
    }

    /**
     * Get list of user's accessible menu keys
     *
     * @param bool $includeSecurityMenus Include security permissions (default: false)
     * @return array Array of menu keys
     */
    public function getAccessibleMenuKeys(bool $includeSecurityMenus = false): array
    {
        if ($includeSecurityMenus) {
            return $this->userMenus;
        }

        // Filter out security permissions
        return array_filter($this->userMenus, function($menu) {
            return !str_starts_with($menu, 'sec');
        });
    }
}
