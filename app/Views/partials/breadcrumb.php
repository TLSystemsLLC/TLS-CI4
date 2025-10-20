<?php
/**
 * Breadcrumb Navigation Partial
 *
 * Renders Bootstrap 5 breadcrumb navigation based on current menu path.
 * Receives $breadcrumbPath array from MenuManager (via controller).
 *
 * Expected variables:
 * - $breadcrumbPath: Array of breadcrumb items from MenuManager->getBreadcrumbPath()
 *
 * Example usage in controller:
 * $data['breadcrumbPath'] = $this->menuManager->getBreadcrumbPath('mnuDriverMaint');
 */
?>
<?php if (isset($breadcrumbPath) && !empty($breadcrumbPath)): ?>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <!-- Home/Dashboard link -->
            <li class="breadcrumb-item">
                <a href="<?= base_url('/') ?>">
                    <i class="bi bi-house-door me-1"></i>Dashboard
                </a>
            </li>

            <!-- Breadcrumb items -->
            <?php foreach ($breadcrumbPath as $index => $item): ?>
                <?php
                $isLast = ($index === count($breadcrumbPath) - 1);
                ?>
                <?php if ($isLast): ?>
                    <!-- Last item (current page) - not clickable -->
                    <li class="breadcrumb-item active" aria-current="page">
                        <?= esc($item['label']) ?>
                    </li>
                <?php else: ?>
                    <!-- Parent items - clickable if URL exists -->
                    <li class="breadcrumb-item">
                        <?php if (!empty($item['url'])): ?>
                            <a href="<?= base_url($item['url']) ?>">
                                <?= esc($item['label']) ?>
                            </a>
                        <?php else: ?>
                            <?= esc($item['label']) ?>
                        <?php endif; ?>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ol>
    </nav>
<?php endif; ?>
