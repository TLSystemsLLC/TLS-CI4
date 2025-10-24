# Base Template Layout Fix - Plan

**Date:** 2025-10-24
**Status:** 🔴 Planning Phase

---

## Problem Statement

The current `base_entity_maintenance.php` template has an incorrect layout structure that doesn't match the legacy tls-web application layout.

### Current (WRONG) Layout:
```
1. Search Section (full width)
2. Entity Form Card (FULL WIDTH) ← PROBLEM: Contains all fields in one card
3. Two-Column Section:
   - Left: Address card
   - Right: Contacts + Comments cards
```

### Expected (CORRECT) Layout (from legacy tls-web Driver Maintenance):
```
1. Search Section (full width)
2. Two-Column Layout (starts immediately after search):
   - Left Column:
     * Basic Information Card
     * Employment Details Card
     * License & Certification Card
     * Pay Information Card
     * Tax ID/PII Card (protected - NOT shown in legacy but should be added)
   - Right Column:
     * Address Card
     * Contacts Card
     * Comments Card
```

**Note:** The legacy tls-web application does NOT have the Tax ID/PII card visible. This needs to be added as a new feature with proper PII protection.

---

## Key Issues

### Issue #1: Single Full-Width Form Card
**Current:** All form fields are auto-generated into ONE full-width card.
**Problem:** This doesn't match the two-column layout standard.
**Fix:** Need to split fields into logical card sections in the left column.

### Issue #2: Missing Tax ID/PII Card
**Current:** No Tax ID card exists in base template.
**Problem:** Tax ID is a standard component that should be available to all entities.
**Fix:** Add Tax ID card with show/hide PII protection to base template.

### Issue #3: Two-Column Layout Starts Too Late
**Current:** Two-column layout only wraps Address/Contacts/Comments.
**Problem:** Form fields are above the two-column section.
**Fix:** Two-column `<div class="row">` should start immediately after search section.

---

## Legacy tls-web Driver Maintenance Structure

### Left Column Structure (from /Applications/MAMP/htdocs/tls/safety/driver-maintenance.php):
```html
<div class="col-lg-6">
    <!-- Card 1: Basic Information -->
    <div class="tls-form-card">
        <div class="card-header">
            <i class="bi-person"></i>Basic Information
        </div>
        <div class="card-body">
            - DriverKey (readonly, col-md-3)
            - First Name (required, col-md-3)
            - Middle Name (col-md-3)
            - Last Name (required, col-md-3)
            - Birth Date (col-md-3)
            - Email (col-md-6)
            - Active checkbox (col-md-3)
        </div>
    </div>

    <!-- Card 2: Employment Details -->
    <div class="tls-form-card mt-3">
        <div class="card-header">
            <i class="bi-briefcase"></i>Employment Details
        </div>
        <div class="card-body">
            - Start Date (col-md-3)
            - End Date (col-md-3)
            - Company dropdown (col-md-3)
            - Company Driver checkbox (col-md-3)
            - Driver ID (col-md-3)
            - Driver Type dropdown (col-md-3)
            - Driver Spec dropdown (col-md-6)
        </div>
    </div>

    <!-- Card 3: License & Certification -->
    <div class="tls-form-card mt-3">
        <div class="card-header">
            <i class="bi-card-text"></i>License & Certification
        </div>
        <div class="card-body">
            - License Number (col-md-4)
            - License State (col-md-4)
            - License Expires (col-md-4)
            - Physical Date (col-md-4)
            - Physical Expires (col-md-4)
            - MVR Due (col-md-4)
            - TWIC checkbox
            - Coil Cert checkbox
            - Medical Verification checkbox
            - EOBR checkbox
            - EOBR Start Date
        </div>
    </div>

    <!-- Card 4: Tax Information (NOT IN LEGACY - TO BE ADDED) -->
    <div class="tls-form-card tls-pii-section mt-3">
        <div class="card-header">
            <i class="bi-shield-lock"></i>Tax Information (Protected)
        </div>
        <div class="card-body">
            - PII Warning Alert
            - Show/Hide Button
            - ID Type dropdown (SSN/EIN/Other)
            - Tax ID input (masked)
        </div>
    </div>
</div>
```

### Right Column Structure:
```html
<div class="col-lg-6">
    <!-- Address Card -->
    <div class="tls-form-card">...</div>

    <!-- Contacts Card -->
    <div class="tls-form-card mt-3">...</div>

    <!-- Comments Card -->
    <div class="tls-form-card mt-3">...</div>
</div>
```

---

## Proposed Solution

### Approach A: Field Sections in Controller Definition

Add a `section` property to field definitions that groups fields into cards:

```php
protected function getFormFields(): array
{
    return [
        'DriverID' => [
            'type' => 'text',
            'label' => 'Driver ID',
            'section' => 'basic',      // ← Section name
            'card' => 'information',   // ← Card grouping
            'order' => 1               // ← Display order
        ],
        'FirstName' => [
            'type' => 'text',
            'label' => 'First Name',
            'required' => true,
            'section' => 'basic',
            'card' => 'information',
            'order' => 2
        ],
        'CompanyLoadedPay' => [
            'type' => 'number',
            'label' => 'Loaded Pay',
            'section' => 'pay',        // ← Different card
            'card' => 'pay_information',
            'order' => 1
        ]
    ];
}
```

**Controller provides card configuration:**
```php
protected function getCardStructure(): array
{
    return [
        'information' => [
            'title' => 'Driver Information',
            'icon' => 'bi-info-circle',
            'column' => 'left',
            'order' => 1
        ],
        'pay_information' => [
            'title' => 'Pay Information',
            'icon' => 'bi-cash-coin',
            'column' => 'left',
            'order' => 2
        ]
    ];
}
```

**Base template logic:**
```php
// Group fields by card
$cards = [];
foreach ($formFields as $fieldName => $fieldConfig) {
    $cardName = $fieldConfig['card'] ?? 'default';
    if (!isset($cards[$cardName])) {
        $cards[$cardName] = [];
    }
    $cards[$cardName][$fieldName] = $fieldConfig;
}

// Get card configuration
$cardStructure = $this->getCardStructure();

// Render left column cards
foreach ($cardStructure as $cardKey => $cardConfig) {
    if ($cardConfig['column'] === 'left') {
        // Render card with fields from $cards[$cardKey]
    }
}
```

### Approach B: Override Left Column Content

Controllers can override a method to provide custom left column HTML:

```php
protected function renderLeftColumnCards(): string
{
    return view('safety/driver_left_column', ['driver' => $this->driver]);
}
```

**Pros:** Maximum flexibility per entity.
**Cons:** Not truly template-based, requires custom views per entity.

---

## Tax ID/PII Card Implementation

### Requirements:
1. **Optional** - Entities can opt-in via controller method
2. **Protected** - Show/Hide button to reveal PII
3. **Logged** - Warning that access is monitored
4. **Flexible** - Supports SSN, EIN, or Other

### Controller Method:
```php
protected function hasTaxIdField(): bool
{
    return true; // Override in child classes
}

protected function getTaxIdConfig(): array
{
    return [
        'types' => ['S' => 'SSN', 'E' => 'EIN', 'O' => 'Other'],
        'field_name' => 'TaxID',
        'type_field_name' => 'IDType'
    ];
}
```

### View Partial:
Create `partials/entity_tax_id.php` that can be included conditionally:

```php
<?php if ($showTaxId): ?>
<div class="tls-form-card tls-pii-section mt-3">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="bi bi-shield-lock me-2"></i>Tax Information (Protected)
        </h5>
    </div>
    <div class="card-body">
        <div class="alert alert-warning" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            This section contains Personally Identifiable Information (PII). Access is logged and monitored.
        </div>

        <!-- Show/Hide PII Section -->
        <div id="show_pii_section">
            <div class="text-center py-3">
                <button type="button" class="btn btn-outline-warning" onclick="TLSEntityMaintenance.showPII()">
                    <i class="bi bi-eye me-2"></i>Show Tax ID Information
                </button>
                <p class="small text-muted mt-2">Tax ID information is protected. Click to reveal.</p>
            </div>
        </div>

        <!-- Tax ID Section -->
        <div id="tax_id_section" style="display: none;">
            <!-- ID Type and Tax ID fields -->
        </div>
    </div>
</div>
<?php endif; ?>
```

---

## Implementation Plan

### Step 1: Add Card Structure to BaseEntityMaintenance
- Add abstract method `getCardStructure(): array`
- Define card grouping logic
- Add optional `hasTaxIdField(): bool` (default: false)
- Add optional `getTaxIdConfig(): array`

### Step 2: Update DriverMaintenance to Define Cards
- Implement `getCardStructure()` returning card definitions
- Add `card` property to each field in `getFormFields()`
- Set `hasTaxIdField()` to true (drivers need tax IDs)

### Step 3: Refactor base_entity_maintenance.php View
- Move `<div class="row">` to start immediately after search
- Create left column `<div class="col-lg-6">`
- Loop through cards and render each in left column
- Keep right column with Address/Contacts/Comments

### Step 4: Create entity_tax_id.php Partial
- Extract Tax ID card from Agent Maintenance
- Make it generic (use $entity instead of $agent)
- Add to base template conditionally

### Step 5: Add PII Show/Hide JavaScript
- Add `showPII()` and `hidePII()` methods to tls-entity-maintenance.js
- Handle input masking for SSN/EIN

### Step 6: Test with Driver Maintenance
- Verify two-column layout renders correctly
- Verify all driver fields appear in correct cards
- Verify Tax ID card shows/hides properly
- Verify Address/Contacts/Comments in right column

---

## Files to Modify

### Controllers:
- `app/Controllers/BaseEntityMaintenance.php`
  - Add `getCardStructure()` abstract method
  - Add `hasTaxIdField()` optional method
  - Add `getTaxIdConfig()` optional method
  - Pass card structure to view

- `app/Controllers/DriverMaintenance.php`
  - Implement `getCardStructure()`
  - Add `card` property to all fields in `getFormFields()`
  - Set `hasTaxIdField()` = true

### Views:
- `app/Views/safety/base_entity_maintenance.php`
  - Restructure to two-column layout from start
  - Add card rendering logic for left column
  - Include Tax ID partial conditionally

- `app/Views/partials/entity_tax_id.php` (NEW)
  - Extract from Agent Maintenance
  - Make generic for all entities

### JavaScript:
- `public/js/tls-entity-maintenance.js`
  - Add `showPII()` method
  - Add `hidePII()` method
  - Add input masking for SSN/EIN

---

## Testing Checklist

- [ ] Driver Maintenance renders in two-column layout
- [ ] Left column shows Driver Information card
- [ ] Left column shows Pay Information card (if defined)
- [ ] Left column shows Tax ID card with PII protection
- [ ] Right column shows Address card
- [ ] Right column shows Contacts card
- [ ] Right column shows Comments card
- [ ] Tax ID Show/Hide button works
- [ ] Tax ID input masking works for SSN/EIN
- [ ] All AJAX operations still work
- [ ] Form submission saves all fields
- [ ] Responsive layout works on mobile

---

## Risk Assessment

### High Risk:
- Breaking existing Driver Maintenance functionality
- Card structure being too rigid for complex entities

### Medium Risk:
- Tax ID JavaScript conflicts with existing code
- Card rendering performance with many fields

### Low Risk:
- Visual styling inconsistencies

---

## Rollback Plan

If issues arise:
1. Revert base_entity_maintenance.php to current version
2. Keep DriverMaintenance using old template temporarily
3. Fix issues in isolation
4. Re-apply changes

---

## Success Criteria

✅ Driver Maintenance uses two-column layout matching Agent Maintenance
✅ Tax ID card is present with PII protection
✅ All form fields render in correct card sections
✅ All existing functionality continues to work
✅ Code remains DRY (no duplication)
✅ Other entities can easily adopt same pattern

---

## Next Steps

1. Review and approve this plan
2. Decide on Approach A (field sections) vs Approach B (override method)
3. Begin implementation in order listed above
4. Test thoroughly at each step
5. Update documentation when complete
