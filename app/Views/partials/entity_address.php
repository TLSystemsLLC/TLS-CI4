<!-- Entity Address Section - Reusable Partial -->
<!-- Used by all entity maintenance screens -->

<?php
/**
 * Entity Address Partial
 *
 * Required variables:
 * - $entityName (string): e.g., 'Agent', 'Driver', 'Owner'
 * - $entityKey (string): e.g., 'AgentKey', 'DriverKey'
 * - $entity (array): Current entity data
 */
$entityLower = strtolower($entityName);
$entityKeyLower = strtolower($entityKey);
?>

<div class="tls-form-card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="bi-house me-2"></i>Address Information
        </h5>
    </div>
    <div class="card-body">
        <!-- Address Display/Edit Toggle -->
        <div id="address-display">
            <p class="text-muted"><em>Loading address...</em></p>
        </div>

        <div id="address-edit" style="display: none;">
            <input type="hidden" id="address-name-key" value="0">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="address-name1" class="form-label">Name Line 1</label>
                    <input type="text" class="form-control" id="address-name1" maxlength="50">
                </div>
                <div class="col-md-6">
                    <label for="address-name2" class="form-label">Name Line 2</label>
                    <input type="text" class="form-control" id="address-name2" maxlength="50">
                </div>
                <div class="col-md-6">
                    <label for="address-address1" class="form-label">Address Line 1</label>
                    <input type="text" class="form-control" id="address-address1" maxlength="50">
                </div>
                <div class="col-md-6">
                    <label for="address-address2" class="form-label">Address Line 2</label>
                    <input type="text" class="form-control" id="address-address2" maxlength="50">
                </div>
                <div class="col-md-4">
                    <label for="address-city" class="form-label">City</label>
                    <input type="text" class="form-control" id="address-city" maxlength="30">
                </div>
                <div class="col-md-4">
                    <label for="address-state" class="form-label">State</label>
                    <input type="text" class="form-control text-uppercase" id="address-state" maxlength="2">
                </div>
                <div class="col-md-4">
                    <label for="address-zip" class="form-label">ZIP Code</label>
                    <input type="text" class="form-control" id="address-zip" maxlength="10">
                </div>
                <div class="col-md-6">
                    <label for="address-phone" class="form-label">Phone</label>
                    <input type="tel" class="form-control" id="address-phone" maxlength="20">
                </div>
            </div>
            <div class="mt-3">
                <button type="button" class="btn tls-btn-primary" onclick="saveAddress()">
                    <i class="bi-check-circle me-1"></i> Save Address
                </button>
                <button type="button" class="btn tls-btn-secondary" onclick="cancelEditAddress()">
                    <i class="bi-x-circle me-1"></i> Cancel
                </button>
            </div>
        </div>

        <div class="mt-3" id="address-actions">
            <button type="button" class="btn btn-sm tls-btn-primary" onclick="editAddress()">
                <i class="bi-pencil me-1"></i> Edit Address
            </button>
        </div>
    </div>
</div>
