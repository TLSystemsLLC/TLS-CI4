<?php
/**
 * Form Field Renderer Partial
 *
 * Auto-generates form fields from field definitions.
 * Supports: text, email, number, date, checkbox, select, textarea
 *
 * Required variables:
 * - $name (string): Field name
 * - $config (array): Field configuration
 * - $value (mixed): Current field value
 * - $entity (array): Full entity data (for relationships)
 */

$type = $config['type'] ?? 'text';
$label = $config['label'] ?? ucfirst($name);
$required = $config['required'] ?? false;
$maxlength = $config['maxlength'] ?? null;
$step = $config['step'] ?? null;
$help = $config['help'] ?? null;
$default = $config['default'] ?? null;
$options = $config['options'] ?? [];
$nullDate = $config['nullDate'] ?? null;
$uppercase = $config['uppercase'] ?? false;
$readonly = $config['readonly'] ?? false;

// Handle null dates (1899-12-30 convention)
if ($type === 'date' && $nullDate) {
    if (empty($value) || $value === $nullDate || strpos($value, '1899-12-30') !== false) {
        $value = '';
    } else {
        // Format date for input field
        $value = date('Y-m-d', strtotime($value));
    }
}

// Use default if value is empty
if (empty($value) && isset($default)) {
    $value = $default;
}

// Prepare common attributes
$fieldId = str_replace('_', '-', strtolower($name));
$fieldName = strtolower($name);
$cssClass = 'form-control';
if ($readonly) {
    $cssClass .= ' readonly-field';
}
if ($uppercase) {
    $cssClass .= ' text-uppercase';
}
?>

<div class="col-md-<?= $config['width'] ?? '4' ?> mb-3">
    <label for="<?= $fieldId ?>" class="form-label">
        <?= esc($label) ?>
        <?php if ($required): ?>
            <span class="text-danger">*</span>
        <?php endif; ?>
    </label>

    <?php if ($type === 'text' || $type === 'email'): ?>
        <input type="<?= $type ?>"
               class="<?= $cssClass ?>"
               id="<?= $fieldId ?>"
               name="<?= $fieldName ?>"
               value="<?= esc($value ?? '') ?>"
               <?= $required ? 'required' : '' ?>
               <?= $maxlength ? 'maxlength="' . $maxlength . '"' : '' ?>
               <?= $readonly ? 'readonly' : '' ?>>

    <?php elseif ($type === 'number'): ?>
        <input type="number"
               class="<?= $cssClass ?>"
               id="<?= $fieldId ?>"
               name="<?= $fieldName ?>"
               value="<?= esc($value ?? $default ?? '') ?>"
               <?= $step ? 'step="' . $step . '"' : '' ?>
               <?= $required ? 'required' : '' ?>
               <?= $readonly ? 'readonly' : '' ?>>

    <?php elseif ($type === 'date'): ?>
        <input type="date"
               class="<?= $cssClass ?>"
               id="<?= $fieldId ?>"
               name="<?= $fieldName ?>"
               value="<?= esc($value ?? '') ?>"
               <?= $required ? 'required' : '' ?>
               <?= $readonly ? 'readonly' : '' ?>>

    <?php elseif ($type === 'checkbox'): ?>
        <div class="form-check">
            <input type="checkbox"
                   class="form-check-input"
                   id="<?= $fieldId ?>"
                   name="<?= $fieldName ?>"
                   value="1"
                   <?= ($value == 1 || $value === true) ? 'checked' : '' ?>
                   <?= $readonly ? 'disabled' : '' ?>>
            <label class="form-check-label" for="<?= $fieldId ?>">
                <?= esc($label) ?>
            </label>
        </div>

    <?php elseif ($type === 'select'): ?>
        <select class="<?= $cssClass ?>"
                id="<?= $fieldId ?>"
                name="<?= $fieldName ?>"
                <?= $required ? 'required' : '' ?>
                <?= $readonly ? 'disabled' : '' ?>>
            <option value="">-- Select --</option>
            <?php foreach ($options as $optValue => $optLabel): ?>
                <option value="<?= esc($optValue) ?>"
                        <?= ($value == $optValue) ? 'selected' : '' ?>>
                    <?= esc($optLabel) ?>
                </option>
            <?php endforeach; ?>
        </select>

    <?php elseif ($type === 'textarea'): ?>
        <textarea class="<?= $cssClass ?>"
                  id="<?= $fieldId ?>"
                  name="<?= $fieldName ?>"
                  rows="<?= $config['rows'] ?? 3 ?>"
                  <?= $maxlength ? 'maxlength="' . $maxlength . '"' : '' ?>
                  <?= $required ? 'required' : '' ?>
                  <?= $readonly ? 'readonly' : '' ?>><?= esc($value ?? '') ?></textarea>
    <?php endif; ?>

    <?php if ($help): ?>
        <small class="form-text text-muted"><?= esc($help) ?></small>
    <?php endif; ?>
</div>
