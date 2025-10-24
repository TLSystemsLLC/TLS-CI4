# Entity Maintenance Implementation Guide

## Overview

This guide shows you EXACTLY how to create a new entity maintenance screen in 30 minutes.

The base template system handles ALL the complexity - you just fill in entity-specific details.

## How to Use This Guide with Claude Code

When requesting Claude Code to create a new entity maintenance screen, use this exact prompt:

```
Create [EntityName] Maintenance following ENTITY_MAINTENANCE_GUIDE.md exactly. Do not reference any other entity screens.
```

**Why this prompt works:**
- **"following ENTITY_MAINTENANCE_GUIDE.md exactly"** - Makes it clear the guide is the single source of truth
- **"Do not reference any other entity screens"** - Prevents variations from other implementations

**Optional expanded prompt with entity-specific details:**

```
Create [EntityName] Maintenance following ENTITY_MAINTENANCE_GUIDE.md exactly. Do not reference any other entity screens.

Entity details:
- Entity key: [EntityKey]
- Section: [safety/accounting/dispatch/etc]
- Menu permission: [mnuEntityMaint]
- Has Tax ID: [yes/no]
- Table: [tEntityName]
- Stored procedures: [spEntity_Get, spEntity_Save, etc]
```

## The 5-Step Process

### STEP 1: Create the Controller (10 minutes)

Create `/app/Controllers/YourEntityMaintenance.php`:

```php
<?php
namespace App\Controllers;

use App\Controllers\BaseEntityMaintenance;
use App\Models\YourEntityModel;

class YourEntityMaintenance extends BaseEntityMaintenance
{
    // ===== 9 REQUIRED METHODS =====

    protected function getEntityName(): string
    {
        return 'YourEntity';  // Example: 'Driver', 'Owner', 'Customer'
    }

    protected function getEntityKey(): string
    {
        return 'YourEntityKey';  // Example: 'DriverKey', 'OwnerKey'
    }

    protected function getSection(): string
    {
        return 'section';  // Example: 'safety', 'accounting', 'dispatch'
    }

    protected function getMenuPermission(): string
    {
        return 'mnuYourEntityMaint';  // From tMenu table
    }

    protected function getEntityModel()
    {
        if ($this->entityModel === null) {
            $this->entityModel = new YourEntityModel();
        }

        $customerDb = $this->getCurrentDatabase();
        if ($customerDb && $this->entityModel->db) {
            $this->entityModel->db->setDatabase($customerDb);
        }

        return $this->entityModel;
    }

    protected function hasTaxIdField(): bool
    {
        return false;  // Set true if entity has SSN/EIN field
    }

    protected function getTaxIdConfig(): array
    {
        // If hasTaxIdField() is false, return empty array
        return [];

        // If hasTaxIdField() is true, return:
        // return [
        //     'types' => ['S' => 'SSN', 'E' => 'EIN', 'O' => 'Other'],
        //     'field_name' => 'TaxID',        // Column that stores the ID value
        //     'type_field_name' => 'IDType'   // Column that stores ID type, or null if no type
        // ];
    }

    protected function getFormFields(): array
    {
        // Define fields using PascalCase
        // form_field_renderer will convert to lowercase for HTML
        return [
            'YourEntityKey' => [
                'type' => 'text',
                'label' => 'Key',
                'readonly' => true,
                'section' => 'basic',
                'width' => '2'  // Bootstrap col-md-{width}
            ],
            'FirstName' => [
                'type' => 'text',
                'label' => 'First Name',
                'required' => true,
                'maxlength' => 50,
                'section' => 'basic',
                'width' => '5'
            ],
            'LastName' => [
                'type' => 'text',
                'label' => 'Last Name',
                'required' => true,
                'section' => 'basic',
                'width' => '5'
            ],
            'Email' => [
                'type' => 'email',
                'label' => 'Email Address',
                'section' => 'basic',
                'width' => '6'
            ],
            'StartDate' => [
                'type' => 'date',
                'label' => 'Start Date',
                'section' => 'basic',
                'nullDate' => '1899-12-30'  // Null date convention
            ],
            'EndDate' => [
                'type' => 'date',
                'label' => 'End Date',
                'section' => 'basic',
                'nullDate' => '1899-12-30',
                'help' => 'Leave empty for active records'
            ],
            'Active' => [
                'type' => 'checkbox',
                'label' => 'Active',
                'section' => 'basic'
            ],
            'Status' => [
                'type' => 'select',
                'label' => 'Status',
                'section' => 'details',
                'options' => [
                    'A' => 'Active',
                    'I' => 'Inactive',
                    'P' => 'Pending'
                ],
                'default' => 'A'
            ],
            'Notes' => [
                'type' => 'textarea',
                'label' => 'Notes',
                'section' => 'details',
                'rows' => 4
            ]
        ];

        // Field types: text, email, number, date, checkbox, select, textarea
        // Sections group fields into cards: basic, details, license, pay, etc.
    }

    protected function getNewEntityTemplate(): array
    {
        // Default values for new entity - use PascalCase keys
        return [
            'YourEntityKey' => 0,
            'FirstName' => 'New',
            'LastName' => 'Entity',
            'Email' => '',
            'StartDate' => date('Y-m-d'),
            'EndDate' => null,
            'Active' => 1,
            'Status' => 'A',
            'Notes' => ''
        ];
    }

    // ===== SAVE METHOD (REQUIRED) =====

    public function save()
    {
        $this->requireAuth();
        $this->requireMenuPermission($this->getMenuPermission());

        // Get entity key from form (lowercase!)
        $entityKey = intval($this->request->getPost('yourentitykey') ?? 0);
        $isNew = ($entityKey == 0);

        // Collect all form data
        $entityData = [];
        foreach ($this->getFormFields() as $fieldName => $fieldConfig) {
            // CRITICAL: Form fields are lowercase
            $postName = strtolower($fieldName);
            $value = $this->request->getPost($postName);

            // Handle checkboxes (they don't post if unchecked)
            if ($fieldConfig['type'] === 'checkbox') {
                $entityData[$fieldName] = $value ? 1 : 0;
            } else {
                $entityData[$fieldName] = $value;
            }
        }

        // Add entity key
        $entityData['YourEntityKey'] = $entityKey;

        // Add Tax ID if applicable
        if ($this->hasTaxIdField()) {
            $config = $this->getTaxIdConfig();
            $entityData[$config['field_name']] = $this->request->getPost('tax_id');
            if ($config['type_field_name']) {
                $entityData[$config['type_field_name']] = $this->request->getPost('id_type');
            }
        }

        // Set Active based on EndDate (standard pattern)
        $endDate = $entityData['EndDate'] ?? null;
        $entityData['Active'] = (empty($endDate) || $endDate == '1899-12-30') ? 1 : 0;

        try {
            $success = $this->getEntityModel()->saveYourEntity($entityData);

            if ($success) {
                if ($isNew) {
                    // For new entity, find it and get its key
                    $fullName = $entityData['LastName'] . ', ' . $entityData['FirstName'];
                    $newEntity = $this->getEntityModel()->searchYourEntityByName($fullName);

                    if ($newEntity && isset($newEntity['YourEntityKey'])) {
                        $newKey = $newEntity['YourEntityKey'];

                        // Create blank address
                        $nameKey = $this->getAddressModel()->createBlankAddress('YE');  // 2-char qualifier
                        if ($nameKey > 0) {
                            $this->getAddressModel()->linkYourEntityAddress($newKey, $nameKey);
                        }

                        return redirect()->to('section/yourentity-maintenance/load/' . $newKey)
                            ->with('success', 'Entity created successfully.');
                    }
                } else {
                    return redirect()->to('section/yourentity-maintenance/load/' . $entityKey)
                        ->with('success', 'Entity updated successfully.');
                }
            } else {
                return redirect()->back()->withInput()->with('error', 'Failed to save entity.');
            }
        } catch (\Exception $e) {
            log_message('error', 'Entity save error: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Database error occurred.');
        }
    }
}
```

### STEP 2: Create the Model (10 minutes)

Create `/app/Models/YourEntityModel.php`:

```php
<?php
namespace App\Models;

use App\Models\BaseModel;

class YourEntityModel extends BaseModel
{
    /**
     * Get entity by key
     */
    public function getYourEntity(int $key): ?array
    {
        if ($key <= 0) return null;

        $results = $this->callStoredProcedure('spYourEntity_Get', [$key]);
        return !empty($results) ? $results[0] : null;
    }

    /**
     * Search entity by name
     */
    public function searchYourEntityByName(string $name): ?array
    {
        if (empty($name)) return null;

        // Try exact match
        $sql = "SELECT TOP 1 YourEntityKey FROM dbo.tYourEntities
                WHERE CONCAT(LastName, ', ', FirstName) = ?";
        $results = $this->db->query($sql, [$name])->getResultArray();

        if (!empty($results)) {
            return $this->getYourEntity($results[0]['YourEntityKey']);
        }

        // Try partial match
        $sql = "SELECT TOP 1 YourEntityKey FROM dbo.tYourEntities
                WHERE LastName LIKE ? OR FirstName LIKE ?
                ORDER BY LastName, FirstName";
        $searchTerm = '%' . $name . '%';
        $results = $this->db->query($sql, [$searchTerm, $searchTerm])->getResultArray();

        if (!empty($results)) {
            return $this->getYourEntity($results[0]['YourEntityKey']);
        }

        return null;
    }

    /**
     * Search for autocomplete
     */
    public function searchYourEntitiesForAutocomplete(string $term, bool $includeInactive = false): array
    {
        if (strlen($term) < 1) return [];

        $sql = "SELECT TOP 20 YourEntityKey, LastName, FirstName, EndDate
                FROM dbo.tYourEntities
                WHERE (UPPER(LastName) LIKE UPPER(?)
                   OR UPPER(FirstName) LIKE UPPER(?)
                   OR CAST(YourEntityKey AS VARCHAR) LIKE ?)";

        if (!$includeInactive) {
            $sql .= " AND (EndDate IS NULL OR EndDate = '1899-12-30')";
        }

        $sql .= " ORDER BY LastName, FirstName";

        $searchTerm = '%' . $term . '%';
        $results = $this->db->query($sql, [$searchTerm, $searchTerm, $searchTerm])->getResultArray();

        $entities = [];
        foreach ($results as $row) {
            $isActive = (empty($row['EndDate']) || $row['EndDate'] == '1899-12-30 00:00:00.000');
            $fullName = trim($row['LastName']) . ', ' . trim($row['FirstName']);

            $entities[] = [
                'id' => $row['YourEntityKey'],
                'label' => $fullName . ' (' . $row['YourEntityKey'] . ')',
                'value' => $row['YourEntityKey'],
                'active' => $isActive
            ];
        }

        return $entities;
    }

    /**
     * Save entity using stored procedure
     */
    public function saveYourEntity(array $data): bool
    {
        try {
            $key = $data['YourEntityKey'] ?? 0;

            if ($key == 0) {
                $key = $this->getNextKey('YourEntity');
                if ($key <= 0) {
                    log_message('error', 'Failed to get next entity key');
                    return false;
                }
            }

            // Convert empty dates to 1899-12-30
            $nullDate = '1899-12-30';
            $startDate = !empty($data['StartDate']) ? $data['StartDate'] : $nullDate;
            $endDate = !empty($data['EndDate']) ? $data['EndDate'] : $nullDate;

            // Prepare parameters for spYourEntity_Save
            $params = [
                $key,
                $data['FirstName'] ?? '',
                $data['LastName'] ?? '',
                $data['Email'] ?? '',
                $startDate,
                $endDate,
                $data['Active'] ?? 1,
                $data['Status'] ?? 'A',
                $data['Notes'] ?? ''
                // Add all your fields here...
            ];

            $returnCode = $this->callStoredProcedureWithReturn('spYourEntity_Save', $params);

            return ($returnCode === self::SRV_NORMAL);
        } catch (\Exception $e) {
            log_message('error', 'Error saving entity: ' . $e->getMessage());
            return false;
        }
    }
}
```

### STEP 3: Create the View (1 minute)

Create `/app/Views/section/yourentity_maintenance.php`:

```php
<?php
/**
 * YourEntity Maintenance View
 * Uses the base entity maintenance template
 */
echo view('section/base_entity_maintenance', get_defined_vars());
```

That's it! The base template handles all the HTML.

### STEP 4: Add Routes (2 minutes)

Edit `/app/Config/Routes.php`:

```php
// YourEntity Maintenance
$routes->group('section', ['filter' => 'auth'], function($routes) {
    $routes->get('yourentity-maintenance', 'YourEntityMaintenance::index');
    $routes->post('yourentity-maintenance/search', 'YourEntityMaintenance::search');
    $routes->post('yourentity-maintenance/create-new', 'YourEntityMaintenance::createNew');
    $routes->post('yourentity-maintenance/save', 'YourEntityMaintenance::save');
    $routes->get('yourentity-maintenance/load/(:num)', 'YourEntityMaintenance::load/$1');
    $routes->get('yourentity-maintenance/autocomplete', 'YourEntityMaintenance::autocomplete');
    $routes->get('yourentity-maintenance/get-address', 'YourEntityMaintenance::getAddress');
    $routes->post('yourentity-maintenance/save-address', 'YourEntityMaintenance::saveAddress');
    $routes->get('yourentity-maintenance/get-contacts', 'YourEntityMaintenance::getContacts');
    $routes->get('yourentity-maintenance/get-contact-function-options', 'YourEntityMaintenance::getContactFunctionOptions');
    $routes->post('yourentity-maintenance/save-contact', 'YourEntityMaintenance::saveContact');
    $routes->post('yourentity-maintenance/delete-contact', 'YourEntityMaintenance::deleteContact');
    $routes->get('yourentity-maintenance/get-comments', 'YourEntityMaintenance::getComments');
    $routes->post('yourentity-maintenance/save-comment', 'YourEntityMaintenance::saveComment');
    $routes->post('yourentity-maintenance/delete-comment', 'YourEntityMaintenance::deleteComment');
});
```

### STEP 5: Add AddressModel Methods (5 minutes)

Edit `/app/Models/AddressModel.php` and add:

```php
/**
 * Link address to yourentity
 */
public function linkYourEntityAddress(int $entityKey, int $nameKey): bool
{
    try {
        if ($entityKey <= 0 || $nameKey <= 0) return false;

        $returnCode = $this->callStoredProcedureWithReturn(
            'spYourEntityNameAddresses_Save',
            [$entityKey, $nameKey]
        );

        return ($returnCode === 0);
    } catch (\Exception $e) {
        log_message('error', 'Error linking address to entity: ' . $e->getMessage());
        return false;
    }
}
```

## Critical Field Naming Convention

**THIS IS THE MOST COMMON ERROR:**

1. **Define fields in getFormFields() using PascalCase:**
   ```php
   'FirstName' => [...], 'EmailAddress' => [...], 'StartDate' => [...]
   ```

2. **form_field_renderer converts to lowercase in HTML:**
   ```html
   <input name="firstname">
   <input name="emailaddress">
   <input name="startdate">
   ```

3. **In save() method, use lowercase:**
   ```php
   $this->request->getPost('firstname')
   $this->request->getPost('emailaddress')
   $this->request->getPost('startdate')
   ```

4. **Pass to Model using PascalCase:**
   ```php
   $data['FirstName'] = $this->request->getPost('firstname');
   $data['EmailAddress'] = $this->request->getPost('emailaddress');
   ```

## What the Base Template Handles Automatically

- ✅ Search form with autocomplete
- ✅ Load/display entity data
- ✅ Form rendering from field definitions
- ✅ Address management (add, edit, display)
- ✅ Contact management (add, edit, delete, list)
- ✅ Comment management (add, edit, delete, list)
- ✅ Change tracking
- ✅ Authentication/permissions
- ✅ Database context switching
- ✅ Success/error messages
- ✅ Responsive two-column layout
- ✅ Tax ID/PII protection

## Testing Your New Screen

1. Navigate to: `http://localhost:8888/tls-ci4/section/yourentity-maintenance`
2. Search for an entity
3. Edit a field and save
4. Add an address
5. Add contacts
6. Add comments

Everything should work immediately.

## Common Issues

### Issue: 500 Error "abstract method save()"
**Fix:** You forgot to implement the save() method. It's required - see Step 1.

### Issue: Validation errors "field is required"
**Fix:** Field name mismatch. Check that you're using lowercase in getPost() calls.

### Issue: Changes don't save
**Fix:** Check that your Model's saveYourEntity() method parameters match your stored procedure.

### Issue: Search doesn't work
**Fix:** Make sure Model has searchYourEntityByName() and searchYourEntitiesForAutocomplete()

## Summary

Total time: **30 minutes** to create a fully functional maintenance screen with:
- Search
- Load/Save
- Address management
- Contact management
- Comment management
- Change tracking
- All UI handled automatically

The base template system does 95% of the work - you just provide entity-specific details.
