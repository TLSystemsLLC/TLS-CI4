# Base Entity Maintenance Template Design

**Created:** 2025-10-24
**Purpose:** Define a reusable template for all entity maintenance screens

---

## The Problem We're Solving

Currently, creating a new entity maintenance screen requires:
1. Copying Agent Maintenance (1,366 lines)
2. Find/replace 50+ variations of "agent" → "entity"
3. Missing replacements cause bugs (we had 6+ bugs in Driver)
4. No guarantee of consistency
5. Maintenance nightmare - fix in Agent doesn't propagate

---

## The Solution: Base Template Pattern

### Architecture Overview

```
BaseEntityMaintenance (abstract controller)
    ├── All common CRUD logic (15 standard endpoints)
    ├── Abstract methods for entity-specific logic
    └── Template method pattern

AgentMaintenance extends BaseEntityMaintenance
    ├── Define entity-specific fields
    ├── Define stored procedure names
    └── Override only what's different

DriverMaintenance extends BaseEntityMaintenance
    ├── Define entity-specific fields
    ├── Define stored procedure names
    └── Override only what's different
```

---

## Base Controller Structure

```php
<?php
namespace App\Controllers;

abstract class BaseEntityMaintenance extends BaseController
{
    // Abstract methods - MUST be implemented by child
    abstract protected function getEntityName(): string;          // 'Agent', 'Driver', 'Owner'
    abstract protected function getEntityKey(): string;           // 'AgentKey', 'DriverKey', 'OwnerKey'
    abstract protected function getMenuPermission(): string;      // 'mnuAgentMaint', etc.
    abstract protected function getEntityModel();                 // Return AgentModel, etc.
    abstract protected function getFormFields(): array;           // Return form field definitions
    abstract protected function getNewEntityTemplate(): array;    // Return default values

    // Optional overrides - have sensible defaults
    protected function getTableName(): string {
        return 't' . $this->getEntityName() . 's';
    }

    protected function getSpPrefix(): string {
        return 'sp' . $this->getEntityName();
    }

    protected function getViewPath(): string {
        return 'safety/' . strtolower($this->getEntityName()) . '_maintenance';
    }

    // Standard endpoints - implemented once, work for all entities
    public function index() { /* common logic */ }
    public function search() { /* common logic */ }
    public function autocomplete() { /* common logic */ }
    public function save() { /* common logic */ }
    public function load($key) { /* common logic */ }
    public function createNew() { /* common logic */ }
    public function getAddress() { /* common logic */ }
    public function saveAddress() { /* common logic */ }
    public function getContacts() { /* common logic */ }
    public function saveContact() { /* common logic */ }
    public function deleteContact() { /* common logic */ }
    public function getContactFunctionOptions() { /* common logic */ }
    public function getComments() { /* common logic */ }
    public function saveComment() { /* common logic */ }
    public function deleteComment() { /* common logic */ }
}
```

---

## Child Implementation Example

```php
<?php
namespace App\Controllers;

class DriverMaintenance extends BaseEntityMaintenance
{
    protected function getEntityName(): string { return 'Driver'; }
    protected function getEntityKey(): string { return 'DriverKey'; }
    protected function getMenuPermission(): string { return 'mnuDriverMaint'; }

    protected function getEntityModel() {
        if (!$this->entityModel) {
            $this->entityModel = new DriverModel();
            $this->entityModel->db->setDatabase($this->getCurrentDatabase());
        }
        return $this->entityModel;
    }

    protected function getFormFields(): array {
        return [
            'DriverID' => ['type' => 'text', 'label' => 'Driver ID', 'maxlength' => 9],
            'FirstName' => ['type' => 'text', 'label' => 'First Name', 'required' => true, 'maxlength' => 15],
            'MiddleName' => ['type' => 'text', 'label' => 'Middle Name', 'maxlength' => 15],
            'LastName' => ['type' => 'text', 'label' => 'Last Name', 'required' => true, 'maxlength' => 15],
            'Email' => ['type' => 'email', 'label' => 'Email', 'maxlength' => 50],
            'BirthDate' => ['type' => 'date', 'label' => 'Birth Date'],
            'StartDate' => ['type' => 'date', 'label' => 'Start Date'],
            'EndDate' => ['type' => 'date', 'label' => 'End Date'],
            'Active' => ['type' => 'checkbox', 'label' => 'Active'],
            // ... 25 more fields
        ];
    }

    protected function getNewEntityTemplate(): array {
        return [
            'DriverKey' => 0,
            'DriverID' => '',
            'FirstName' => '',
            'MiddleName' => '',
            'LastName' => '',
            // ... default values for all fields
        ];
    }
}
```

**That's it!** 50 lines instead of 930 lines. No find/replace needed.

---

## View Template Structure

```php
<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="tls-page-header">
    <h2><?= esc($entityName) ?> Maintenance</h2>
    <div class="tls-top-actions">
        <button onclick="new<?= $entityName ?>()">New <?= $entityName ?></button>
        <?php if ($entity): ?>
            <button type="submit" form="entity-form">Save</button>
        <?php endif; ?>
    </div>
</div>

<!-- Search Section - COMMON -->
<?= $this->include('partials/entity_search', [
    'entityName' => $entityName,
    'entityKey' => $entityKey
]) ?>

<?php if ($entity): ?>
<!-- Form Section - GENERATED FROM FIELD DEFINITIONS -->
<form id="entity-form">
    <?php foreach ($formFields as $fieldName => $fieldConfig): ?>
        <?= $this->include('partials/form_field', [
            'name' => $fieldName,
            'config' => $fieldConfig,
            'value' => $entity[$fieldName] ?? null
        ]) ?>
    <?php endforeach; ?>
</form>

<!-- Address Section - COMMON -->
<?= $this->include('partials/entity_address') ?>

<!-- Contacts Section - COMMON -->
<?= $this->include('partials/entity_contacts') ?>

<!-- Comments Section - COMMON -->
<?= $this->include('partials/entity_comments') ?>

<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Common JavaScript - works for ALL entities -->
<script src="<?= base_url('js/tls-entity-maintenance.js') ?>"></script>
<script>
    TLSEntityMaintenance.init({
        entityName: '<?= $entityName ?>',
        entityKey: '<?= $entityKey ?>',
        baseUrl: '<?= base_url() ?>'
    });
</script>
<?= $this->endSection() ?>
```

---

## Benefits of This Approach

### 1. **Consistency Guaranteed**
- All entities use the exact same code
- Fix a bug once, it's fixed everywhere
- No more find/replace errors

### 2. **Dramatically Less Code**
- Agent: 930 lines → **50 lines**
- Driver: 930 lines → **70 lines** (more fields)
- Owner: 930 lines → **60 lines**

### 3. **Easy to Add New Entities**
```php
// New entity in 10 minutes:
class CustomerMaintenance extends BaseEntityMaintenance {
    // Just define these 5 methods
    // Done!
}
```

### 4. **Type Safety**
- Abstract methods enforced by PHP
- Can't forget to implement required methods
- IDE autocomplete works

### 5. **Testability**
- Test base class once
- Child classes just test field definitions
- Much less testing needed

### 6. **Maintainability**
- Add feature to base → all entities get it
- Change pattern → change once
- Documentation in one place

---

## Implementation Plan

### Phase 1: Create Base Template
1. Create `BaseEntityMaintenance.php` abstract controller
2. Create `tls-entity-maintenance.js` common JavaScript
3. Create view partials:
   - `partials/entity_search.php`
   - `partials/form_field.php`
   - `partials/entity_address.php`
   - `partials/entity_contacts.php`
   - `partials/entity_comments.php`

### Phase 2: Refactor Existing Entities
1. Refactor Agent to extend base (verify it still works)
2. Refactor Driver to extend base (much simpler)
3. Document any issues found

### Phase 3: New Entities
1. Create Owner using base template
2. Verify it takes < 30 minutes
3. Confirm consistency

---

## File Structure

```
app/
├── Controllers/
│   ├── BaseEntityMaintenance.php     ← Base template (write once)
│   ├── AgentMaintenance.php          ← 50 lines (field definitions)
│   ├── DriverMaintenance.php         ← 70 lines (field definitions)
│   └── OwnerMaintenance.php          ← 60 lines (field definitions)
├── Views/
│   ├── partials/
│   │   ├── entity_search.php         ← Common search UI
│   │   ├── form_field.php            ← Common field renderer
│   │   ├── entity_address.php        ← Common address UI
│   │   ├── entity_contacts.php       ← Common contacts UI
│   │   └── entity_comments.php       ← Common comments UI
│   └── safety/
│       ├── base_entity_maintenance.php  ← Base template
│       ├── agent_maintenance.php     ← Extends base (minimal)
│       ├── driver_maintenance.php    ← Extends base (minimal)
│       └── owner_maintenance.php     ← Extends base (minimal)
└── public/js/
    └── tls-entity-maintenance.js      ← Common JavaScript for all entities
```

---

## Comparison: Old vs New Approach

### Old Approach (Current):
```
Create new entity:
1. Copy agent_maintenance.php (1,366 lines)
2. Find/replace 50+ variations
3. Miss some replacements → bugs
4. Test everything
5. Total time: 2-4 hours
6. Risk: High (6+ bugs in Driver)
```

### New Approach (Template):
```
Create new entity:
1. Create child class (50-70 lines)
2. Define 5 methods (entity name, fields, etc.)
3. Test field-specific logic only
4. Total time: 15-30 minutes
5. Risk: Low (base is tested once)
```

---

## Example: Creating Owner in 15 Minutes

```php
<?php
class OwnerMaintenance extends BaseEntityMaintenance {
    protected function getEntityName(): string { return 'Owner'; }
    protected function getEntityKey(): string { return 'OwnerKey'; }
    protected function getMenuPermission(): string { return 'mnuOwnerMaint'; }
    protected function getEntityModel() {
        return $this->getOrCreateModel(OwnerModel::class);
    }
    protected function getFormFields(): array {
        return [
            'OwnerName' => ['type' => 'text', 'label' => 'Owner Name', 'required' => true],
            'OwnerID' => ['type' => 'text', 'label' => 'Owner ID'],
            'Email' => ['type' => 'email', 'label' => 'Email'],
            'StartDate' => ['type' => 'date', 'label' => 'Start Date'],
            'EndDate' => ['type' => 'date', 'label' => 'End Date'],
            'Active' => ['type' => 'checkbox', 'label' => 'Active'],
        ];
    }
    protected function getNewEntityTemplate(): array {
        return ['OwnerKey' => 0, 'OwnerName' => '', /* defaults */];
    }
}
```

**Done!** Everything else is inherited from base.

---

## This is Industry Standard

This pattern is used by:
- **Laravel**: Eloquent models extend base Model
- **Symfony**: Controllers extend AbstractController
- **Django**: Views extend generic views
- **Rails**: Controllers extend ApplicationController

**We should use it too.**

---

## Decision Point

**Do you want me to:**
1. Finish Driver as-is (copy/paste approach)
2. **Create the base template first**, then rebuild Driver properly
3. Finish Driver now, create template later for Owner

**I strongly recommend Option 2** - create the template now, use it going forward.
