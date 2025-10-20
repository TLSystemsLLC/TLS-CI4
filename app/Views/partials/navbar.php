<?php
/**
 * Navigation Bar Partial
 *
 * Renders Bootstrap 5 responsive navbar based on menu structure data.
 * Receives $menuStructure array from MenuManager (via BaseController).
 *
 * Expected variables:
 * - $menuStructure: Array of menu items from MenuManager->getMenuStructure()
 * - $currentUser: Current user data from session
 */
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top mb-4">
    <div class="container-fluid">
        <!-- Brand -->
        <a class="navbar-brand" href="<?= base_url('/') ?>">
            <i class="bi bi-truck me-2"></i>TLS Operations
        </a>

        <!-- Mobile toggle button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar content -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Main menu items -->
            <ul class="navbar-nav me-auto">
                <?php if (isset($menuStructure) && !empty($menuStructure)): ?>
                    <?php foreach ($menuStructure as $menuItem): ?>
                        <?php if ($menuItem['hasChildren']): ?>
                            <!-- Dropdown menu -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle text-light" href="#" role="button"
                                   data-bs-toggle="dropdown" aria-expanded="false">
                                    <?php if ($menuItem['icon']): ?>
                                        <i class="<?= esc($menuItem['icon']) ?> me-2"></i>
                                    <?php endif; ?>
                                    <?= esc($menuItem['label']) ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-scrollable">
                                    <?php echo renderDropdownItems($menuItem['items']); ?>
                                </ul>
                            </li>
                        <?php else: ?>
                            <!-- Simple link -->
                            <li class="nav-item">
                                <a class="nav-link text-light" href="<?= base_url($menuItem['url'] ?? '#') ?>">
                                    <?php if ($menuItem['icon']): ?>
                                        <i class="<?= esc($menuItem['icon']) ?> me-2"></i>
                                    <?php endif; ?>
                                    <?= esc($menuItem['label']) ?>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>

            <!-- User dropdown (right side) -->
            <?php if (isset($currentUser)): ?>
                <div class="navbar-nav">
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-light" href="#" role="button"
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-2"></i><?= esc($currentUser['user_name'] ?? 'User') ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <h6 class="dropdown-header">
                                    <i class="bi bi-building me-2"></i><?= esc($currentUser['company_name'] ?? 'N/A') ?>
                                </h6>
                            </li>
                            <li>
                                <h6 class="dropdown-header">
                                    Customer ID: <?= esc($currentUser['customer_db'] ?? 'N/A') ?>
                                </h6>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="<?= base_url('logout') ?>">
                                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>

<?php
/**
 * Helper function to render dropdown items recursively
 *
 * Handles nested menu structures by:
 * - Flattening nested categories into section headers
 * - Rendering separators as dividers
 * - Creating clickable links for leaf items
 *
 * @param array $items Menu items to render
 * @return string HTML output
 */
function renderDropdownItems(array $items): string
{
    $html = '';

    foreach ($items as $item) {
        // Handle separators
        if (isset($item['separator']) && $item['separator'] === true) {
            $html .= '<li><hr class="dropdown-divider"></li>';
            continue;
        }

        // Handle nested categories (flatten as section headers)
        if ($item['hasChildren']) {
            $html .= '<li><h6 class="dropdown-header">' . esc($item['label']) . '</h6></li>';

            // Render child items
            foreach ($item['items'] as $childItem) {
                if (isset($childItem['separator']) && $childItem['separator'] === true) {
                    $html .= '<li><hr class="dropdown-divider"></li>';
                } else {
                    $url = $childItem['url'] ?? '#';
                    $html .= '<li><a class="dropdown-item" href="' . base_url($url) . '">';
                    $html .= esc($childItem['label']);
                    $html .= '</a></li>';
                }
            }

            // Add divider after section
            $html .= '<li><hr class="dropdown-divider"></li>';
        } else {
            // Simple dropdown link
            $url = $item['url'] ?? '#';
            $html .= '<li><a class="dropdown-item" href="' . base_url($url) . '">';
            $html .= esc($item['label']);
            $html .= '</a></li>';
        }
    }

    return $html;
}
?>
