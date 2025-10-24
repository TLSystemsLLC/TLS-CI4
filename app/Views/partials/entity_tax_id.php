<?php
/**
 * Tax ID / PII Card Partial
 *
 * Generic Tax ID card with PII protection for entity maintenance screens
 * Can be used with any entity (Driver, Agent, Owner, etc.)
 *
 * Required variables:
 * - $entity - Current entity data array
 * - $entityKey - Entity key field name (e.g., 'DriverKey', 'AgentKey')
 * - $taxIdConfig - Configuration array with:
 *   - types: array of ID types ['S' => 'SSN', 'E' => 'EIN', 'O' => 'Other']
 *   - field_name: Name of Tax ID field in database
 *   - type_field_name: Name of ID Type field in database
 */

$idType = isset($taxIdConfig['type_field_name']) && $taxIdConfig['type_field_name']
    ? ($entity[$taxIdConfig['type_field_name']] ?? '')
    : '';
$taxId = $entity[$taxIdConfig['field_name']] ?? '';
$hasTypeField = !empty($taxIdConfig['type_field_name']);
$typeCount = count($taxIdConfig['types'] ?? []);

// Auto-select first type if only one option and no type field
if (!$hasTypeField && $typeCount === 1 && empty($idType)) {
    $idType = array_key_first($taxIdConfig['types']);
}
?>

<div class="tls-form-card tls-pii-section mt-3">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="bi bi-shield-lock me-2"></i>Tax Information (Protected)
        </h5>
    </div>
    <div class="card-body">
        <!-- PII Warning -->
        <div class="alert alert-warning" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            This section contains Personally Identifiable Information (PII). Access is logged and monitored.
        </div>

        <!-- Show PII Button (default state) -->
        <div id="show_pii_section">
            <div class="text-center py-3">
                <button type="button" class="btn btn-outline-warning" onclick="TLSEntityMaintenance.showPII()">
                    <i class="bi bi-eye me-2"></i>Show Tax ID Information
                </button>
                <p class="small text-muted mt-2">Tax ID information is protected. Click to reveal.</p>
            </div>
        </div>

        <!-- Tax ID Section (hidden by default) -->
        <div id="tax_id_section" style="display: none;">
            <div class="row g-3">
                <!-- ID Type (always shown, even if only one option) -->
                <div class="col-md-4">
                    <label for="id_type" class="form-label">ID Type</label>
                    <select class="form-select" id="id_type"
                            <?php if ($hasTypeField): ?>
                                name="<?= esc($taxIdConfig['type_field_name']) ?>"
                            <?php endif; ?>
                            <?= $typeCount === 1 ? 'disabled' : '' ?>>
                        <?php if ($typeCount > 1): ?>
                            <option value="">Select Type...</option>
                        <?php endif; ?>
                        <?php foreach ($taxIdConfig['types'] as $typeKey => $typeLabel): ?>
                            <option value="<?= esc($typeKey) ?>" <?= $idType == $typeKey ? 'selected' : '' ?>>
                                <?= esc($typeLabel) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!$hasTypeField): ?>
                        <input type="hidden" name="id_type_display_only" value="<?= esc($idType) ?>">
                    <?php endif; ?>
                </div>

                <!-- Tax ID -->
                <div class="col-md-5">
                    <label for="tax_id" class="form-label">Tax ID</label>
                    <input type="text"
                           class="form-control"
                           id="tax_id"
                           name="<?= esc($taxIdConfig['field_name']) ?>"
                           value="<?= esc($taxId) ?>"
                           maxlength="20"
                           placeholder="Enter Tax ID">
                    <small class="form-text text-muted">
                        Format: SSN (XXX-XX-XXXX) or EIN (XX-XXXXXXX)
                    </small>
                </div>

                <!-- Hide PII Button -->
                <div class="col-md-3 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-secondary w-100" onclick="TLSEntityMaintenance.hidePII()">
                        <i class="bi bi-eye-slash me-2"></i>Hide
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
