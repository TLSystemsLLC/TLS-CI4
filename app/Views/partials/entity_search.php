<!-- Entity Search Section - Reusable Partial -->
<!-- Used by all entity maintenance screens -->

<?php
/**
 * Entity Search Partial
 *
 * Required variables:
 * - $entityName (string): e.g., 'Agent', 'Driver', 'Owner'
 * - $entityKey (string): e.g., 'AgentKey', 'DriverKey'
 * - $baseUrl (string): Base URL for form action
 */
$entityLower = strtolower($entityName);
$entityKeyLower = strtolower($entityKey);
$apiType = $entityLower . 's';
?>

<div class="tls-form-card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="bi-search me-2"></i><?= esc($entityName) ?> Search
        </h5>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= base_url($baseUrl . '/search') ?>" id="searchForm">
            <?= csrf_field() ?>

            <!-- Row 1: Label -->
            <div class="row mb-2">
                <div class="col-12">
                    <label for="<?= $entityKeyLower ?>" class="form-label">Search <?= esc($entityName) ?>:</label>
                </div>
            </div>

            <!-- Row 2: Search input and button -->
            <div class="row g-3">
                <div class="col-md-6">
                    <input type="text"
                           class="form-control"
                           id="<?= $entityKeyLower ?>"
                           name="<?= $entityLower ?>_key"
                           placeholder="Type <?= strtolower($entityName) ?> name or <?= $entityKey ?>..."
                           autocomplete="off">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn tls-btn-primary w-100">
                        <i class="bi-search"></i> Load <?= esc($entityName) ?>
                    </button>
                </div>
                <div class="col-md-3">
                    <!-- Placeholder for alignment -->
                </div>
            </div>

            <!-- Row 3: Status and Include Inactive -->
            <div class="row mt-2">
                <div class="col-12">
                    <div class="d-flex justify-content-between">
                        <div id="search-status" class="form-text text-muted">
                            Type <?= strtolower($entityName) ?> name or enter <?= $entityKey ?> directly
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="includeInactive" name="include_inactive">
                            <label class="form-check-label form-text text-muted" for="includeInactive">
                                Include Inactive <?= esc($entityName) ?>s
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
