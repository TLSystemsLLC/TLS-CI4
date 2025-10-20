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

        <!-- Right Column (Empty for now - future: Address, Contacts) -->
        <div class="col-lg-6">
            <div class="tls-form-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi-info-circle me-2"></i>Additional Information
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Address, Contacts, and Comments will be added in future updates.</p>
                </div>
            </div>
        </div>
    </div>
</form>
<?php endif; ?>

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
    // Apply initial formatting when page loads
    document.addEventListener('DOMContentLoaded', function() {
        applyInputMask();
    });
    <?php endif; ?>
</script>
<?= $this->endSection() ?>
