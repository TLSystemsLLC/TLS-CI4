<?= $this->extend('layouts/main') ?>

<?= $this->section('css') ?>
<style>
    .readonly-field {
        background-color: #f8f9fa;
    }
    .tls-pii-section {
        border: 2px solid #ffc107;
    }
    .tls-pii-section .card-header {
        background-color: #fff3cd;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
/**
 * Base Entity Maintenance View Template
 *
 * Works for ALL entity types (Driver, Agent, Owner, Customer, Unit, etc.)
 * Configured via variables passed from controller
 *
 * Required variables from controller:
 * - $entityName (string): 'Driver', 'Agent', 'Owner', etc.
 * - $entityKey (string): 'DriverKey', 'AgentKey', etc.
 * - $formFields (array): Field definitions from controller
 * - ${entityVarName} (array): Current entity data (e.g., $driver, $agent)
 * - $isNew{EntityName} (bool): Whether this is a new entity
 * - $hasTaxIdField (bool): Whether to show Tax ID card
 * - $taxIdConfig (array): Tax ID configuration
 */

$entityVar = strtolower($entityName);
$entityKeyLower = strtolower($entityKey);
$isNew = ${'isNew' . $entityName} ?? false;
$entity = ${$entityVar} ?? null;
// Note: $baseUrl is passed from controller, don't override it here

// Section icon mapping
$sectionIcons = [
    'basic' => 'bi-person',
    'employment' => 'bi-briefcase',
    'license' => 'bi-card-text',
    'certification' => 'bi-patch-check',
    'pay' => 'bi-cash-coin',
    'company' => 'bi-building',
    'characteristics' => 'bi-list-check',
    'default' => 'bi-info-circle'
];

// Group fields by section
$sections = [];
foreach ($formFields as $fieldName => $fieldConfig) {
    $section = $fieldConfig['section'] ?? 'default';
    if (!isset($sections[$section])) {
        $sections[$section] = [];
    }
    $sections[$section][$fieldName] = $fieldConfig;
}
?>

<!-- Standardized Page Header -->
<div class="tls-page-header">
    <h2 class="tls-page-title">
        <i class="bi-person-badge me-2"></i><?= esc($entityName) ?> Maintenance
    </h2>
    <div class="tls-top-actions d-flex gap-2">
        <!-- New Entity Button (always visible) -->
        <button type="button" class="btn tls-btn-primary" onclick="newEntity()">
            <i class="bi-plus me-1"></i> New <?= esc($entityName) ?>
        </button>

        <?php if ($entity): ?>
        <!-- Form Action Buttons (shown when entity is loaded) -->
        <button type="submit" form="entity-form" class="btn tls-btn-primary" id="tls-save-btn" disabled>
            <i class="bi-check-circle me-1"></i> Save <?= esc($entityName) ?>
        </button>
        <button type="button" class="btn tls-btn-secondary" id="tls-reset-btn" disabled>
            <i class="bi-arrow-clockwise me-1"></i> Reset
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- Save Indicator Banner -->
<div class="tls-save-indicator" id="unsaved-changes-alert" style="display: none;">
    <i class="bi bi-exclamation-triangle me-2"></i>
    You have <span id="tls-change-counter">0</span> unsaved changes. Remember to save before navigating away.
</div>

<!-- Entity Search Section (reusable partial) -->
<?= view('partials/entity_search', [
    'entityName' => $entityName,
    'entityKey' => $entityKey,
    'baseUrl' => $baseUrl
]) ?>

<?php if ($entity): ?>
<!-- Entity Form -->
<form id="entity-form" method="POST" action="<?= base_url($baseUrl . '/save') ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="<?= $entityKeyLower ?>" value="<?= esc($entity[$entityKey] ?? 0) ?>">

    <!-- Two-column layout starting immediately after search -->
    <div class="row">
        <!-- LEFT COLUMN: Entity Information Cards -->
        <div class="col-lg-6">
            <?php
            // Render each section as a separate card
            $firstCard = true;
            foreach ($sections as $sectionName => $sectionFields):
                $icon = $sectionIcons[$sectionName] ?? $sectionIcons['default'];
                $cardTitle = ucwords(str_replace('_', ' ', $sectionName));
            ?>
                <div class="tls-form-card<?= $firstCard ? '' : ' mt-3' ?>">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi <?= $icon ?> me-2"></i><?= esc($cardTitle) ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <?php
                            // Render fields in this section
                            foreach ($sectionFields as $fieldName => $fieldConfig):
                                echo view('partials/form_field_renderer', [
                                    'name' => $fieldName,
                                    'config' => $fieldConfig,
                                    'value' => $entity[$fieldName] ?? null,
                                    'entity' => $entity
                                ]);
                            endforeach;
                            ?>
                        </div>
                    </div>
                </div>
            <?php
                $firstCard = false;
            endforeach;
            ?>

            <?php if ($hasTaxIdField): ?>
                <!-- Tax ID Card (optional, with PII protection) -->
                <?= view('partials/entity_tax_id', [
                    'entity' => $entity,
                    'entityKey' => $entityKey,
                    'taxIdConfig' => $taxIdConfig
                ]) ?>
            <?php endif; ?>
        </div>

        <!-- RIGHT COLUMN: Address, Contacts, Comments -->
        <div class="col-lg-6">
            <?= view('partials/entity_address', [
                'entityName' => $entityName,
                'entityKey' => $entityKey,
                'entity' => $entity
            ]) ?>

            <?= view('partials/entity_contacts', [
                'entityName' => $entityName,
                'entityKey' => $entityKey,
                'entity' => $entity
            ]) ?>

            <?= view('partials/entity_comments', [
                'entityName' => $entityName,
                'entityKey' => $entityKey,
                'entity' => $entity
            ]) ?>
        </div>
    </div>
</form>

<?php endif; // if ($entity) ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Common TLS JavaScript Libraries -->
<script src="<?= base_url('js/tls-autocomplete.js') ?>"></script>
<script src="<?= base_url('js/tls-form-tracker.js') ?>"></script>
<script src="<?= base_url('js/tls-entity-maintenance.js') ?>"></script>

<!-- Initialize Entity Maintenance -->
<script>
TLSEntityMaintenance.init({
    entityName: '<?= $entityName ?>',
    entityKey: '<?= $entityKey ?>',
    baseUrl: '<?= base_url($baseUrl) ?>',
    apiType: '<?= $apiType ?>'
});
</script>
<?= $this->endSection() ?>
