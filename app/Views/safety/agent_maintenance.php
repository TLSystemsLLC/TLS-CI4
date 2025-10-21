<?= $this->extend('layouts/main') ?>

<?= $this->section('css') ?>
<style>
    .readonly-field {
        background-color: #f8f9fa;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Standardized Page Header -->
<div class="tls-page-header">
    <h2 class="tls-page-title"><i class="bi-person-badge me-2"></i>Agent Maintenance</h2>
    <div class="tls-top-actions">
        <?php if ($agent): ?>
        <!-- Form Action Buttons (shown when agent is loaded) -->
        <button type="button" class="btn tls-btn-primary" id="tls-save-btn" disabled>
            <i class="bi-check-circle me-1"></i> Save Agent
        </button>
        <button type="button" class="btn tls-btn-secondary" id="tls-reset-btn" disabled>
            <i class="bi-arrow-clockwise me-1"></i> Reset
        </button>
        <?php else: ?>
        <!-- New Agent Button (shown when no agent is loaded) -->
        <button type="button" class="btn tls-btn-primary" onclick="newAgent()">
            <i class="bi-plus me-1"></i> New Agent
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- Save Indicator Banner -->
<div class="tls-save-indicator" id="tls-save-indicator" style="display: none;">
    <i class="bi bi-exclamation-triangle me-2"></i>
    You have <span id="tls-change-counter">0</span> unsaved changes. Remember to save before navigating away.
</div>

<!-- Agent Search Section -->
<div class="tls-form-card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="bi-search me-2"></i>Agent Search
        </h5>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= base_url('safety/agent-maintenance/search') ?>" id="searchForm">
            <?= csrf_field() ?>

            <!-- Row 1: Label -->
            <div class="row">
                <div class="col-md-8">
                    <label for="agent_search" class="form-label">Search Agent:</label>
                </div>
            </div>

            <!-- Row 2: Search input and Load Agent button -->
            <div class="row">
                <div class="col-md-8">
                    <div class="position-relative">
                        <input type="text" class="form-control" id="agent_search" name="agent_key"
                               placeholder="Type agent name or AgentKey...">
                        <div id="search-spinner" class="position-absolute top-50 end-0 translate-middle-y me-3" style="display: none;">
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="visually-hidden">Searching...</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 d-flex align-items-start">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi-search"></i> Load Agent
                    </button>
                </div>
            </div>

            <!-- Row 3: Status text with checkbox -->
            <div class="row mt-1">
                <div class="col-md-8">
                    <div class="d-flex justify-content-between">
                        <div id="search-status" class="form-text text-muted">Type agent name or enter AgentKey directly</div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="includeInactive" name="include_inactive">
                            <label class="form-check-label form-text text-muted" for="includeInactive">
                                Include Inactive Agents
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if ($agent): ?>
<!-- Agent Form -->
<form method="POST" action="<?= base_url('safety/agent-maintenance/save') ?>" id="agentForm">
    <?= csrf_field() ?>
    <input type="hidden" name="agent_key" value="<?= esc($agent['AgentKey'] ?? 0) ?>">

    <div class="row mt-4">
        <!-- Left Column -->
        <div class="col-lg-6">
            <!-- Basic Information -->
            <div class="tls-form-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi-info-circle me-2"></i>Agent Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="agent_key_display" class="form-label">Agent Key:</label>
                            <input type="text" class="form-control readonly-field" id="agent_key_display"
                                   value="<?= esc($agent['AgentKey'] ?? '') ?>" readonly>
                        </div>
                        <div class="col-md-9">
                            <label for="agent_name" class="form-label">Agent Name <span class="text-danger">*</span>:</label>
                            <input type="text" class="form-control" id="agent_name" name="name"
                                   value="<?= esc($agent['NAME'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <label for="start_date" class="form-label">Start Date:</label>
                            <input type="date" class="form-control" id="start_date" name="start_date"
                                   value="<?= (!empty($agent['StartDate']) && $agent['StartDate'] !== '1899-12-30 00:00:00.000') ? date('Y-m-d', strtotime($agent['StartDate'])) : '' ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="end_date" class="form-label">End Date:</label>
                            <input type="date" class="form-control" id="end_date" name="end_date"
                                   value="<?= (!empty($agent['EndDate']) && $agent['EndDate'] !== '1899-12-30 00:00:00.000') ? date('Y-m-d', strtotime($agent['EndDate'])) : '' ?>">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="active" name="active"
                                       <?= ($agent['ACTIVE'] ?? 0) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="active">Active</label>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-8">
                            <label for="email" class="form-label">Email:</label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?= esc($agent['Email'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="division" class="form-label">Division:</label>
                            <select class="form-select" id="division" name="division">
                                <?php for ($i = 1; $i <= 7; $i++): ?>
                                <option value="<?= $i ?>" <?= ($agent['Division'] ?? 1) == $i ? 'selected' : '' ?>>
                                    Division <?= $i ?>
                                </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pay Information -->
            <div class="tls-form-card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi-cash-coin me-2"></i>Pay Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="broker_pct" class="form-label">Broker %:</label>
                            <input type="number" class="form-control" id="broker_pct" name="broker_pct"
                                   step="0.001" min="0" max="100"
                                   value="<?= esc($agent['BrokerPct'] ?? '0') ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="fleet_pct" class="form-label">Fleet %:</label>
                            <input type="number" class="form-control" id="fleet_pct" name="fleet_pct"
                                   step="0.001" min="0" max="100"
                                   value="<?= esc($agent['FleetPct'] ?? '0') ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="company_pct" class="form-label">Company %:</label>
                            <input type="number" class="form-control" id="company_pct" name="company_pct"
                                   step="0.001" min="0" max="100"
                                   value="<?= esc($agent['CompanyPct'] ?? '0') ?>">
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="full_freight_pay" name="full_freight_pay"
                                       <?= ($agent['FullFreightPay'] ?? 0) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="full_freight_pay">Full Freight Pay</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tax/ID Information (PII Protected) -->
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
                            <button type="button" class="btn btn-outline-warning" onclick="showPII()">
                                <i class="bi bi-eye me-2"></i>Show Tax ID Information
                            </button>
                            <p class="small text-muted mt-2">Tax ID information is protected. Click to reveal.</p>
                        </div>
                    </div>

                    <!-- Tax ID Section -->
                    <div id="tax_id_section" style="display: none;">
                        <div class="mb-3">
                            <label for="id_type" class="form-label">ID Type</label>
                            <?php
                            // Default to Other when IDType is blank/null
                            $defaultType = 'O';
                            if ($agent && isset($agent['IDType']) && trim($agent['IDType']) !== '') {
                                $defaultType = trim($agent['IDType']);
                            }
                            ?>
                            <select class="form-select" id="id_type" name="id_type" onchange="applyInputMask()">
                                <option value="S" <?= $defaultType === 'S' ? 'selected' : '' ?>>SSN (Social Security Number)</option>
                                <option value="E" <?= $defaultType === 'E' ? 'selected' : '' ?>>EIN (Employer Identification Number)</option>
                                <option value="O" <?= $defaultType === 'O' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="tax_id" class="form-label">Tax ID (SSN/EIN)</label>
                            <input type="text" class="form-control" id="tax_id" name="tax_id"
                                   value="<?= esc(trim($agent['TaxID'] ?? '')) ?>"
                                   placeholder="Enter Tax ID" oninput="handleTaxIdInput(event)">
                        </div>
                        <div class="text-center">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="hidePII()">
                                <i class="bi bi-eye-slash me-2"></i>Hide Tax ID Information
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Address -->
        <div class="col-lg-6">
            <!-- Address Section -->
            <div class="tls-form-card">
                <div class="card-header">
                    <h5 class="card-title mb-0 d-inline">
                        <i class="bi-geo-alt me-2"></i>Address
                    </h5>
                    <button type="button" class="btn btn-sm btn-outline-primary float-end" id="edit-address-btn" onclick="editAddress()">
                        <i class="bi-pencil"></i> Edit Address
                    </button>
                </div>
                <div class="card-body">
                    <!-- Address Display Mode -->
                    <div id="address-display">
                        <p class="text-muted"><em>Loading address...</em></p>
                    </div>

                    <!-- Address Edit Mode (initially hidden) -->
                    <div id="address-edit" style="display: none;">
                        <input type="hidden" id="address_name_key" value="0">

                        <div class="row">
                            <div class="col-md-6">
                                <label for="address_name1" class="form-label">Name 1:</label>
                                <input type="text" class="form-control" id="address_name1" maxlength="35">
                            </div>
                            <div class="col-md-6">
                                <label for="address_name2" class="form-label">Name 2:</label>
                                <input type="text" class="form-control" id="address_name2" maxlength="35">
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <label for="address_address1" class="form-label">Address 1:</label>
                                <input type="text" class="form-control" id="address_address1" maxlength="35">
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <label for="address_address2" class="form-label">Address 2:</label>
                                <input type="text" class="form-control" id="address_address2" maxlength="35">
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label for="address_city" class="form-label">City:</label>
                                <input type="text" class="form-control" id="address_city" maxlength="18">
                            </div>
                            <div class="col-md-3">
                                <label for="address_state" class="form-label">State:</label>
                                <input type="text" class="form-control text-uppercase" id="address_state" maxlength="2">
                            </div>
                            <div class="col-md-3">
                                <label for="address_zip" class="form-label">ZIP:</label>
                                <input type="text" class="form-control" id="address_zip" maxlength="9">
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label for="address_phone" class="form-label">Phone:</label>
                                <input type="text" class="form-control" id="address_phone" maxlength="15">
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <button type="button" class="btn tls-btn-primary" onclick="saveAddress()">
                                    <i class="bi-save"></i> Save Address
                                </button>
                                <button type="button" class="btn tls-btn-secondary" onclick="cancelAddressEdit()">
                                    <i class="bi-x-circle"></i> Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Future: Contacts and Comments Cards -->
            <!-- Contacts Section -->
            <div class="tls-form-card mt-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>
                        <i class="bi-telephone me-2"></i>Contacts
                        <span class="badge bg-secondary" id="contact-count">0</span>
                    </span>
                    <button type="button" class="btn btn-sm tls-btn-primary" onclick="showContactModal()">
                        <i class="bi-plus-circle"></i> Add Contact
                    </button>
                </div>
                <div class="card-body">
                    <div id="contacts-loading" class="text-center" style="display: none;">
                        <i class="bi-hourglass-split"></i> Loading contacts...
                    </div>
                    <div id="contacts-grid">
                        <p class="text-muted">No contacts on file</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<?php endif; ?>

<!-- Contact Modal -->
<div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="contactModalLabel">Add/Edit Contact</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="contactForm">
                    <input type="hidden" id="contact_key" name="contact_key" value="0">

                    <div class="row">
                        <div class="col-md-6">
                            <label for="contact_first_name" class="form-label">First Name:</label>
                            <input type="text" class="form-control" id="contact_first_name" name="first_name" maxlength="30">
                        </div>
                        <div class="col-md-6">
                            <label for="contact_last_name" class="form-label">Last Name:</label>
                            <input type="text" class="form-control" id="contact_last_name" name="last_name" maxlength="30">
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label for="contact_phone" class="form-label">Phone:</label>
                            <input type="text" class="form-control" id="contact_phone" name="phone" maxlength="20">
                        </div>
                        <div class="col-md-6">
                            <label for="contact_mobile" class="form-label">Mobile:</label>
                            <input type="text" class="form-control" id="contact_mobile" name="mobile" maxlength="20">
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <label for="contact_email" class="form-label">Email:</label>
                            <input type="email" class="form-control" id="contact_email" name="email" maxlength="50">
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-8">
                            <label for="contact_relationship" class="form-label">Relationship:</label>
                            <input type="text" class="form-control" id="contact_relationship" name="relationship" maxlength="30">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="contact_is_primary" name="is_primary">
                                <label class="form-check-label" for="contact_is_primary">
                                    Primary Contact
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn tls-btn-secondary" data-bs-dismiss="modal">
                    <i class="bi-x-circle"></i> Cancel
                </button>
                <button type="button" class="btn tls-btn-primary" onclick="saveContact()">
                    <i class="bi-check-circle"></i> Save Contact
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- TLS JavaScript -->
<script src="<?= base_url('js/tls-autocomplete.js') ?>"></script>
<script src="<?= base_url('js/tls-form-tracker.js') ?>"></script>

<script>
    // Agent search with TLSAutocomplete
    const agentSearchField = document.getElementById('agent_search');

    // Initialize autocomplete for name searches
    const agentAutocomplete = new TLSAutocomplete(
        agentSearchField,
        'agents',
        function(agent) {
            console.log('Agent selected:', agent);
            // Set the search field value to the AgentKey
            agentSearchField.value = agent.value;
            // Auto-submit the form when agent is selected from dropdown
            document.getElementById('searchForm').submit();
        }
    );

    // Handle form submission - allow direct AgentKey entry or search
    document.getElementById('searchForm').addEventListener('submit', function(e) {
        const searchValue = agentSearchField.value.trim();

        // Make sure we have a value
        if (!searchValue) {
            e.preventDefault();
            alert('Please enter an AgentKey or select an agent from the search results.');
            return false;
        }

        return true;
    });

    function newAgent() {
        window.location.href = '<?= base_url('safety/agent-maintenance') ?>?new=1';
    }

    <?php if ($agent): ?>
    // Initialize TLS Form Tracker
    let tracker = null;
    document.addEventListener('DOMContentLoaded', function() {
        tracker = new TLSFormTracker({
            formSelector: '#agentForm',
            saveButtonId: 'tls-save-btn',
            resetButtonId: 'tls-reset-btn',
            saveIndicatorId: 'tls-save-indicator',
            excludeFields: [], // Track all fields
            onSave: function(changes) {
                console.log('Saving agent changes:', changes);
                // Submit the form normally
                document.getElementById('agentForm').submit();
            }
        });
    });
    <?php endif; ?>

    // PII Show/Hide Functions
    function showPII() {
        document.getElementById('tax_id_section').style.display = 'block';
        document.getElementById('show_pii_section').style.display = 'none';
    }

    function hidePII() {
        document.getElementById('tax_id_section').style.display = 'none';
        document.getElementById('show_pii_section').style.display = 'block';
    }

    // Tax ID Formatting Functions
    function applyInputMask() {
        const idType = document.getElementById('id_type').value;
        const taxIdField = document.getElementById('tax_id');

        // Remove existing formatting for fresh start
        const digitsOnly = taxIdField.value.replace(/\D/g, '');

        switch(idType) {
            case 'S': // SSN: XXX-XX-XXXX
                taxIdField.maxLength = 11;
                taxIdField.placeholder = "___-__-____";
                if (digitsOnly.length > 0) {
                    taxIdField.value = formatSSN(digitsOnly);
                } else {
                    taxIdField.value = '';
                }
                break;
            case 'E': // EIN: XX-XXXXXXX
                taxIdField.maxLength = 10;
                taxIdField.placeholder = "__-_______";
                if (digitsOnly.length > 0) {
                    taxIdField.value = formatEIN(digitsOnly);
                } else {
                    taxIdField.value = '';
                }
                break;
            case 'O': // Other: No formatting
                taxIdField.maxLength = 20;
                taxIdField.placeholder = "Enter Tax ID";
                taxIdField.value = digitsOnly;
                break;
        }
    }

    function formatSSN(digits) {
        // Format as XXX-XX-XXXX
        if (digits.length >= 9) {
            return digits.substring(0,3) + '-' + digits.substring(3,5) + '-' + digits.substring(5,9);
        } else if (digits.length >= 5) {
            return digits.substring(0,3) + '-' + digits.substring(3,5) + '-' + digits.substring(5);
        } else if (digits.length >= 3) {
            return digits.substring(0,3) + '-' + digits.substring(3);
        }
        return digits;
    }

    function formatEIN(digits) {
        // Format as XX-XXXXXXX
        if (digits.length >= 9) {
            return digits.substring(0,2) + '-' + digits.substring(2,9);
        } else if (digits.length >= 2) {
            return digits.substring(0,2) + '-' + digits.substring(2);
        }
        return digits;
    }

    function handleTaxIdInput(event) {
        const idType = document.getElementById('id_type').value;

        if (idType === 'S') {
            handleSSNInput(event);
        } else if (idType === 'E') {
            handleEINInput(event);
        }
    }

    function handleSSNInput(event) {
        const taxIdField = document.getElementById('tax_id');
        let value = taxIdField.value;

        // Remove all non-digits
        const digitsOnly = value.replace(/\D/g, '');

        // Limit to 9 digits max
        const limitedDigits = digitsOnly.substring(0, 9);

        // Format as XXX-XX-XXXX
        let formattedValue = '';
        if (limitedDigits.length > 0) {
            formattedValue = limitedDigits.substring(0, 3);
            if (limitedDigits.length > 3) {
                formattedValue += '-' + limitedDigits.substring(3, 5);
                if (limitedDigits.length > 5) {
                    formattedValue += '-' + limitedDigits.substring(5, 9);
                }
            }
        }

        taxIdField.value = formattedValue;
    }

    function handleEINInput(event) {
        const taxIdField = document.getElementById('tax_id');
        let value = taxIdField.value;

        // Remove all non-digits
        const digitsOnly = value.replace(/\D/g, '');

        // Limit to 9 digits max
        const limitedDigits = digitsOnly.substring(0, 9);

        // Format as XX-XXXXXXX
        let formattedValue = '';
        if (limitedDigits.length > 0) {
            formattedValue = limitedDigits.substring(0, 2);
            if (limitedDigits.length > 2) {
                formattedValue += '-' + limitedDigits.substring(2, 9);
            }
        }

        taxIdField.value = formattedValue;
    }

    <?php if ($agent): ?>
    // ========================================
    // Address Management Functions
    // ========================================

    let currentAddress = null;

    /**
     * Load agent address via AJAX
     */
    function loadAgentAddress() {
        // Get the hidden agent_key field from the agent form (not the search form)
        const agentKeyField = document.querySelector('#agentForm input[name="agent_key"]');

        if (!agentKeyField) {
            console.error('Agent key field not found');
            document.getElementById('address-display').innerHTML = '<p class="text-muted"><em>No agent loaded</em></p>';
            return;
        }

        const agentKey = agentKeyField.value;
        console.log('Loading address for agent:', agentKey);

        if (!agentKey || agentKey == '0') {
            document.getElementById('address-display').innerHTML = '<p class="text-muted"><em>No agent loaded</em></p>';
            return;
        }

        const url = `<?= base_url('safety/agent-maintenance/get-address') ?>?agent_key=${agentKey}`;
        console.log('Fetching address from:', url);

        fetch(url)
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Address data:', data);
                if (data.success && data.address) {
                    currentAddress = data.address;
                    displayAddress(data.address);
                } else {
                    document.getElementById('address-display').innerHTML = '<p class="text-muted"><em>No address found</em></p>';
                }
            })
            .catch(error => {
                console.error('Error loading address:', error);
                document.getElementById('address-display').innerHTML = '<p class="text-danger"><em>Error loading address</em></p>';
            });
    }

    /**
     * Display address in read-only format
     */
    function displayAddress(address) {
        let html = '';

        if (address.Name1) {
            html += `<p class="mb-1"><strong>${escapeHtml(address.Name1)}</strong></p>`;
        }
        if (address.Name2) {
            html += `<p class="mb-1">${escapeHtml(address.Name2)}</p>`;
        }
        if (address.Address1) {
            html += `<p class="mb-1">${escapeHtml(address.Address1)}</p>`;
        }
        if (address.Address2) {
            html += `<p class="mb-1">${escapeHtml(address.Address2)}</p>`;
        }

        if (address.City || address.State || address.Zip) {
            html += '<p class="mb-1">';
            if (address.City) html += escapeHtml(address.City);
            if (address.State) html += `, ${escapeHtml(address.State)}`;
            if (address.Zip) html += ` ${escapeHtml(address.Zip)}`;
            html += '</p>';
        }

        if (address.Phone) {
            html += `<p class="mb-1"><i class="bi-telephone me-2"></i>${escapeHtml(address.Phone)}</p>`;
        }

        if (!html) {
            html = '<p class="text-muted"><em>No address information</em></p>';
        }

        document.getElementById('address-display').innerHTML = html;
    }

    /**
     * Toggle address edit mode
     */
    function editAddress() {
        if (!currentAddress) {
            alert('Please load an agent first');
            return;
        }

        // Populate edit form
        document.getElementById('address_name_key').value = currentAddress.NameKey || '0';
        document.getElementById('address_name1').value = currentAddress.Name1 || '';
        document.getElementById('address_name2').value = currentAddress.Name2 || '';
        document.getElementById('address_address1').value = currentAddress.Address1 || '';
        document.getElementById('address_address2').value = currentAddress.Address2 || '';
        document.getElementById('address_city').value = currentAddress.City || '';
        document.getElementById('address_state').value = currentAddress.State || '';
        document.getElementById('address_zip').value = currentAddress.Zip || '';
        document.getElementById('address_phone').value = currentAddress.Phone || '';

        // Toggle display/edit mode
        document.getElementById('address-display').style.display = 'none';
        document.getElementById('address-edit').style.display = 'block';
        document.getElementById('edit-address-btn').style.display = 'none';
    }

    /**
     * Cancel address edit and return to display mode
     */
    function cancelAddressEdit() {
        document.getElementById('address-display').style.display = 'block';
        document.getElementById('address-edit').style.display = 'none';
        document.getElementById('edit-address-btn').style.display = 'inline-block';
    }

    /**
     * Save address via AJAX
     */
    function saveAddress() {
        // Get the hidden agent_key field from the agent form (not the search form)
        const agentKey = document.querySelector('#agentForm input[name="agent_key"]').value;

        if (!agentKey || agentKey == '0') {
            alert('Invalid agent key');
            return;
        }

        const formData = new FormData();

        // Add CSRF token for CI4
        const csrfToken = document.querySelector('input[name="<?= csrf_token() ?>"]');
        if (csrfToken) {
            formData.append('<?= csrf_token() ?>', csrfToken.value);
        }

        formData.append('agent_key', agentKey);
        formData.append('name_key', document.getElementById('address_name_key').value);
        formData.append('name1', document.getElementById('address_name1').value);
        formData.append('name2', document.getElementById('address_name2').value);
        formData.append('address1', document.getElementById('address_address1').value);
        formData.append('address2', document.getElementById('address_address2').value);
        formData.append('city', document.getElementById('address_city').value);
        formData.append('state', document.getElementById('address_state').value);
        formData.append('zip', document.getElementById('address_zip').value);
        formData.append('phone', document.getElementById('address_phone').value);

        fetch('<?= base_url('safety/agent-maintenance/save-address') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Save response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Save response data:', data);
            console.log('Success flag:', data.success);
            console.log('Message:', data.message);
            console.log('Address:', data.address);

            if (data.success) {
                currentAddress = data.address;
                displayAddress(data.address);
                cancelAddressEdit();
                alert(data.message || 'Address saved successfully');
            } else {
                console.error('Save failed:', data);
                alert(data.message || 'Failed to save address');
            }
        })
        .catch(error => {
            console.error('Error saving address:', error);
            alert('Error saving address: ' + error.message);
        });
    }

    /**
     * Escape HTML to prevent XSS
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Apply initial formatting when page loads
    document.addEventListener('DOMContentLoaded', function() {
        applyInputMask();
        loadAgentAddress(); // Load address when page loads
        loadAgentContacts(); // Load contacts when page loads
    });

    /**
     * Load agent contacts using 3-level chain retrieval
     */
    function loadAgentContacts() {
        const agentKeyField = document.querySelector('#agentForm input[name="agent_key"]');
        if (!agentKeyField) {
            console.error('Agent key field not found');
            document.getElementById('contacts-grid').innerHTML = '<p class="text-muted">No agent loaded</p>';
            return;
        }

        const agentKey = agentKeyField.value;
        console.log('Loading contacts for agent:', agentKey);

        if (!agentKey || agentKey == '0') {
            document.getElementById('contacts-grid').innerHTML = '<p class="text-muted">No agent loaded</p>';
            return;
        }

        document.getElementById('contacts-loading').style.display = 'block';
        document.getElementById('contacts-grid').innerHTML = '';

        const url = `<?= base_url('safety/agent-maintenance/get-contacts') ?>?agent_key=${agentKey}`;
        console.log('Fetching contacts from:', url);

        fetch(url)
            .then(response => response.json())
            .then(data => {
                console.log('Contacts data:', data);
                document.getElementById('contacts-loading').style.display = 'none';

                if (data.success && data.contacts && data.contacts.length > 0) {
                    displayContacts(data.contacts);
                    document.getElementById('contact-count').textContent = data.contacts.length;
                } else {
                    document.getElementById('contacts-grid').innerHTML = '<p class="text-muted">No contacts on file</p>';
                    document.getElementById('contact-count').textContent = '0';
                }
            })
            .catch(error => {
                console.error('Error loading contacts:', error);
                document.getElementById('contacts-loading').style.display = 'none';
                document.getElementById('contacts-grid').innerHTML = '<p class="text-danger">Error loading contacts</p>';
            });
    }

    /**
     * Display contacts in table format
     */
    function displayContacts(contacts) {
        let html = '<div class="table-responsive">';
        html += '<table class="table table-hover table-sm">';
        html += '<thead><tr><th>Name</th><th>Phone</th><th>Mobile</th><th>Relationship</th><th>Actions</th></tr></thead>';
        html += '<tbody>';

        contacts.forEach(contact => {
            html += '<tr>';
            html += '<td>' + escapeHtml(contact.ContactName || '') + '</td>';
            html += '<td>' + escapeHtml(contact.Phone || '') + '</td>';
            html += '<td>' + escapeHtml(contact.Mobile || '') + '</td>';
            html += '<td>' + escapeHtml(contact.Relationship || '') + '</td>';
            html += '<td>';
            html += '<button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="editContact(' + contact.ContactKey + ')" title="Edit">';
            html += '<i class="bi-pencil"></i>';
            html += '</button>';
            html += '<button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteContact(' + contact.ContactKey + ')" title="Delete">';
            html += '<i class="bi-trash"></i>';
            html += '</button>';
            html += '</td>';
            html += '</tr>';
        });

        html += '</tbody></table></div>';
        document.getElementById('contacts-grid').innerHTML = html;
    }

    /**
     * Show contact modal for adding new contact
     */
    function showContactModal() {
        // Reset form
        document.getElementById('contactForm').reset();
        document.getElementById('contact_key').value = '0';
        document.getElementById('contactModalLabel').textContent = 'Add Contact';

        // Show modal
        new bootstrap.Modal(document.getElementById('contactModal')).show();
    }

    /**
     * Edit existing contact
     */
    function editContact(contactKey) {
        // Find the contact in the current data
        const agentKey = document.querySelector('#agentForm input[name="agent_key"]').value;

        fetch(`<?= base_url('safety/agent-maintenance/get-contacts') ?>?agent_key=${agentKey}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.contacts) {
                    const contact = data.contacts.find(c => c.ContactKey == contactKey);
                    if (contact) {
                        // Populate form
                        document.getElementById('contact_key').value = contact.ContactKey;
                        document.getElementById('contact_first_name').value = contact.FirstName || '';
                        document.getElementById('contact_last_name').value = contact.LastName || '';
                        document.getElementById('contact_phone').value = contact.Phone || '';
                        document.getElementById('contact_mobile').value = contact.Mobile || '';
                        document.getElementById('contact_email').value = contact.Email || '';
                        document.getElementById('contact_relationship').value = contact.Relationship || '';
                        document.getElementById('contact_is_primary').checked = contact.IsPrimary == 1;

                        document.getElementById('contactModalLabel').textContent = 'Edit Contact';

                        // Show modal
                        new bootstrap.Modal(document.getElementById('contactModal')).show();
                    }
                }
            })
            .catch(error => {
                console.error('Error loading contact for edit:', error);
                alert('Error loading contact details');
            });
    }

    /**
     * Save contact via AJAX
     */
    function saveContact() {
        const agentKey = document.querySelector('#agentForm input[name="agent_key"]').value;

        if (!agentKey || agentKey == '0') {
            alert('Invalid agent key');
            return;
        }

        const formData = new FormData();

        // Add CSRF token
        const csrfToken = document.querySelector('input[name="<?= csrf_token() ?>"]');
        if (csrfToken) {
            formData.append('<?= csrf_token() ?>', csrfToken.value);
        }

        formData.append('agent_key', agentKey);
        formData.append('contact_key', document.getElementById('contact_key').value);
        formData.append('first_name', document.getElementById('contact_first_name').value);
        formData.append('last_name', document.getElementById('contact_last_name').value);
        formData.append('phone', document.getElementById('contact_phone').value);
        formData.append('mobile', document.getElementById('contact_mobile').value);
        formData.append('email', document.getElementById('contact_email').value);
        formData.append('relationship', document.getElementById('contact_relationship').value);

        if (document.getElementById('contact_is_primary').checked) {
            formData.append('is_primary', '1');
        }

        fetch('<?= base_url('safety/agent-maintenance/save-contact') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close modal
                bootstrap.Modal.getInstance(document.getElementById('contactModal')).hide();

                // Reload contacts
                loadAgentContacts();

                alert(data.message || 'Contact saved successfully');
            } else {
                alert(data.message || 'Failed to save contact');
            }
        })
        .catch(error => {
            console.error('Error saving contact:', error);
            alert('Error saving contact: ' + error.message);
        });
    }

    /**
     * Delete contact with confirmation
     */
    function deleteContact(contactKey) {
        if (!confirm('Are you sure you want to delete this contact?')) {
            return;
        }

        const formData = new FormData();

        // Add CSRF token
        const csrfToken = document.querySelector('input[name="<?= csrf_token() ?>"]');
        if (csrfToken) {
            formData.append('<?= csrf_token() ?>', csrfToken.value);
        }

        formData.append('contact_key', contactKey);

        fetch('<?= base_url('safety/agent-maintenance/delete-contact') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload contacts
                loadAgentContacts();

                alert(data.message || 'Contact deleted successfully');
            } else {
                alert(data.message || 'Failed to delete contact');
            }
        })
        .catch(error => {
            console.error('Error deleting contact:', error);
            alert('Error deleting contact: ' + error.message);
        });
    }
    <?php endif; ?>
</script>
<?= $this->endSection() ?>
