<?= $this->extend('layouts/main') ?>

<?= $this->section('css') ?>
<style>
    .readonly-field {
        background-color: #f8f9fa;
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
 */

$entityVar = strtolower($entityName);
$entityKeyLower = strtolower($entityKey);
$isNew = ${'isNew' . $entityName} ?? false;
$entity = ${$entityVar} ?? null;
$baseUrl = 'safety/' . str_replace('_', '-', strtolower($entityName)) . '-maintenance';
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

    <!-- Form Fields Card -->
    <div class="tls-form-card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="bi-info-circle me-2"></i><?= esc($entityName) ?> Information
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <!-- Auto-generated fields from controller definition -->
                <?php
                // Group fields by section if defined
                $sections = [];
                foreach ($formFields as $fieldName => $fieldConfig) {
                    $section = $fieldConfig['section'] ?? 'default';
                    if (!isset($sections[$section])) {
                        $sections[$section] = [];
                    }
                    $sections[$section][$fieldName] = $fieldConfig;
                }

                // Render each section
                foreach ($sections as $sectionName => $sectionFields):
                    if ($sectionName !== 'default'):
                ?>
                    <div class="col-12">
                        <h6 class="border-bottom pb-2 mb-3"><?= ucwords(str_replace('_', ' ', $sectionName)) ?></h6>
                    </div>
                <?php
                    endif;

                    // Render fields in this section
                    foreach ($sectionFields as $fieldName => $fieldConfig):
                        echo view('partials/form_field_renderer', [
                            'name' => $fieldName,
                            'config' => $fieldConfig,
                            'value' => $entity[$fieldName] ?? null,
                            'entity' => $entity
                        ]);
                    endforeach;
                endforeach;
                ?>
            </div>
        </div>
    </div>
</form>

<!-- Two-column layout for Address, Contacts, Comments -->
<div class="row">
    <!-- Left Column: Address -->
    <div class="col-md-6">
        <?= view('partials/entity_address', [
            'entityName' => $entityName,
            'entityKey' => $entityKey,
            'entity' => $entity
        ]) ?>
    </div>

    <!-- Right Column: Contacts and Comments -->
    <div class="col-md-6">
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
