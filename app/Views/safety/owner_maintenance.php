<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="tls-page-header">
        <h1 class="tls-page-title">
            <i class="bi bi-briefcase me-2"></i>Owner Maintenance
        </h1>
        <div class="tls-top-actions d-flex gap-2">
            <button type="button" class="btn tls-btn-primary" id="new-owner-btn" onclick="newOwner()">
                <i class="bi bi-plus-circle me-1"></i>New Owner
            </button>
            <?php if ($owner && !$isNewOwner): ?>
            <button type="submit" form="owner-form" class="btn tls-btn-primary">
                <i class="bi bi-check-circle me-1"></i>Save Owner
            </button>
            <button type="button" class="btn tls-btn-secondary" onclick="resetForm()">
                <i class="bi bi-x-circle me-1"></i>Reset
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->has('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i><?= esc(session('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->has('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i><?= esc(session('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->has('warning')): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i><?= esc(session('warning')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Validation Errors -->
    <?php if (session()->has('errors')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Validation Errors:</strong>
            <ul class="mb-0 mt-2">
                <?php foreach (session('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Unsaved Changes Warning -->
    <div class="alert alert-warning" id="unsaved-changes-alert" style="display: none;">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong>Unsaved Changes:</strong>
        <span id="tls-change-counter">0</span> field(s) modified.
        <button type="submit" form="owner-form" class="btn btn-sm tls-btn-primary ms-3">Save Now</button>
    </div>

    <!-- Search Section -->
    <div class="tls-form-card mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="bi bi-search me-2"></i>Search Owner
            </h5>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= base_url('safety/owner-maintenance/search') ?>" class="row g-3">
                <?= csrf_field() ?>
                <div class="col-md-6">
                    <label for="owner_key" class="form-label">Owner Key or Name</label>
                    <input type="text"
                           class="form-control tls-autocomplete"
                           id="owner_key"
                           name="owner_key"
                           placeholder="Enter Owner Key or search by name..."
                           autocomplete="off"
                           data-api-type="owners"
                           data-include-inactive-id="include_inactive">
                </div>
                <div class="col-md-3">
                    <label class="form-label d-block">&nbsp;</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="include_inactive" name="include_inactive">
                        <label class="form-check-label" for="include_inactive">
                            Include Inactive Owners
                        </label>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label d-block">&nbsp;</label>
                    <button type="submit" class="btn tls-btn-primary w-100">
                        <i class="bi bi-search me-1"></i>Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($owner): ?>
    <!-- Owner Details Form -->
    <form id="owner-form" method="POST" action="<?= base_url('safety/owner-maintenance/save') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="owner_key" id="form_owner_key" value="<?= esc($owner['OwnerKey'] ?? 0) ?>">

        <div class="row">
            <!-- Left Column: Owner Information -->
            <div class="col-md-6">
                <!-- Basic Information Section -->
                <div class="tls-form-card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-person me-2"></i>Basic Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="owner_key_display" class="form-label">Owner Key</label>
                                <input type="text" class="form-control" id="owner_key_display"
                                       value="<?= esc($owner['OwnerKey'] ?? 0) ?>" disabled>
                            </div>
                            <div class="col-md-4">
                                <label for="owner_id" class="form-label">Owner ID</label>
                                <input type="text" class="form-control tls-track-changes" id="owner_id" name="owner_id"
                                       value="<?= esc($owner['OwnerID'] ?? '') ?>" maxlength="9">
                            </div>
                            <div class="col-md-4">
                                <label for="id_type" class="form-label">ID Type</label>
                                <select class="form-select tls-track-changes" id="id_type" name="id_type">
                                    <option value="O" <?= (($owner['IDType'] ?? 'O') == 'O') ? 'selected' : '' ?>>Owner (O)</option>
                                    <option value="S" <?= (($owner['IDType'] ?? 'O') == 'S') ? 'selected' : '' ?>>SSN (S)</option>
                                    <option value="E" <?= (($owner['IDType'] ?? 'O') == 'E') ? 'selected' : '' ?>>EIN (E)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control tls-track-changes" id="first_name" name="first_name"
                                       value="<?= esc($owner['FirstName'] ?? '') ?>" maxlength="15">
                            </div>
                            <div class="col-md-4">
                                <label for="middle_name" class="form-label">Middle Name</label>
                                <input type="text" class="form-control tls-track-changes" id="middle_name" name="middle_name"
                                       value="<?= esc($owner['MiddleName'] ?? '') ?>" maxlength="15">
                            </div>
                            <div class="col-md-4">
                                <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control tls-track-changes" id="last_name" name="last_name"
                                       value="<?= esc($owner['LastName'] ?? '') ?>" maxlength="35" required>
                            </div>
                            <div class="col-md-6">
                                <label for="other_name" class="form-label">Other Name / DBA</label>
                                <input type="text" class="form-control tls-track-changes" id="other_name" name="other_name"
                                       value="<?= esc($owner['OtherName'] ?? '') ?>" maxlength="35">
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control tls-track-changes" id="email" name="email"
                                       value="<?= esc($owner['Email'] ?? '') ?>" maxlength="50">
                            </div>
                            <div class="col-md-6">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control tls-track-changes" id="start_date" name="start_date"
                                       value="<?= !empty($owner['StartDate']) && $owner['StartDate'] != '1899-12-30 00:00:00.000' ? date('Y-m-d', strtotime($owner['StartDate'])) : '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control tls-track-changes" id="end_date" name="end_date"
                                       value="<?= !empty($owner['EndDate']) && $owner['EndDate'] != '1899-12-30 00:00:00.000' ? date('Y-m-d', strtotime($owner['EndDate'])) : '' ?>">
                                <small class="form-text text-muted">Leave empty for active owners</small>
                            </div>
                            <div class="col-md-12">
                                <label for="driver_key" class="form-label">Associated Driver Key</label>
                                <input type="number" class="form-control tls-track-changes" id="driver_key" name="driver_key"
                                       value="<?= esc($owner['DriverKey'] ?? '') ?>" min="0">
                                <small class="form-text text-muted">Link to a driver record if this owner is also a driver</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Financial Information Section -->
                <div class="tls-form-card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-cash-stack me-2"></i>Financial Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="min_check" class="form-label">Minimum Check</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control tls-track-changes" id="min_check" name="min_check"
                                           value="<?= esc($owner['MinCheck'] ?? '0.00') ?>" step="0.01" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="max_debt" class="form-label">Maximum Debt</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control tls-track-changes" id="max_debt" name="max_debt"
                                           value="<?= esc($owner['MaxDebt'] ?? '0.00') ?>" step="0.01" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="min_deduction" class="form-label">Minimum Deduction</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control tls-track-changes" id="min_deduction" name="min_deduction"
                                           value="<?= esc($owner['MinDeduction'] ?? '0.00') ?>" step="0.01" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="verified_check" class="form-label">Verified Check</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control tls-track-changes" id="verified_check" name="verified_check"
                                           value="<?= esc($owner['VerifiedCheck'] ?? '0.00') ?>" step="0.01" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mt-4">
                                    <input class="form-check-input tls-track-changes" type="checkbox" id="direct_deposit" name="direct_deposit"
                                           <?= !empty($owner['DirectDeposit']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="direct_deposit">
                                        Direct Deposit Enabled
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="dd_id" class="form-label">Direct Deposit ID</label>
                                <input type="number" class="form-control tls-track-changes" id="dd_id" name="dd_id"
                                       value="<?= esc($owner['DDId'] ?? '0') ?>" min="0">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Information Section -->
                <div class="tls-form-card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-gear me-2"></i>Additional Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="company_id" class="form-label">Company ID</label>
                                <input type="number" class="form-control tls-track-changes" id="company_id" name="company_id"
                                       value="<?= esc($owner['CompanyID'] ?? '3') ?>" min="1">
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mt-4">
                                    <input class="form-check-input tls-track-changes" type="checkbox" id="contract_signed" name="contract_signed"
                                           <?= !empty($owner['ContractSigned']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="contract_signed">
                                        Contract Signed
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Address, Contacts, Comments -->
            <div class="col-md-6">
                <!-- Address Section -->
                <div class="tls-form-card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-geo-alt me-2"></i>Address
                        </h5>
                        <button type="button" class="btn btn-sm tls-btn-primary" id="edit-address-btn" onclick="editAddress()" style="display: none;">
                            <i class="bi bi-pencil me-1"></i>Edit Address
                        </button>
                    </div>
                    <div class="card-body">
                        <!-- Address Display Mode -->
                        <div id="address-display" style="display: none;">
                            <p class="mb-1"><strong id="addr-name1"></strong></p>
                            <p class="mb-1" id="addr-name2"></p>
                            <p class="mb-1" id="addr-address1"></p>
                            <p class="mb-1" id="addr-address2"></p>
                            <p class="mb-0">
                                <span id="addr-city"></span><span id="addr-state-zip"></span>
                            </p>
                            <p class="mb-0" id="addr-phone"></p>
                        </div>

                        <!-- Address Edit Mode -->
                        <div id="address-edit" style="display: none;">
                            <input type="hidden" id="addr-name-key" value="0">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="addr-edit-name1" class="form-label">Name 1</label>
                                    <input type="text" class="form-control" id="addr-edit-name1" maxlength="35">
                                </div>
                                <div class="col-md-6">
                                    <label for="addr-edit-name2" class="form-label">Name 2</label>
                                    <input type="text" class="form-control" id="addr-edit-name2" maxlength="35">
                                </div>
                                <div class="col-md-12">
                                    <label for="addr-edit-address1" class="form-label">Address 1</label>
                                    <input type="text" class="form-control" id="addr-edit-address1" maxlength="35">
                                </div>
                                <div class="col-md-12">
                                    <label for="addr-edit-address2" class="form-label">Address 2</label>
                                    <input type="text" class="form-control" id="addr-edit-address2" maxlength="35">
                                </div>
                                <div class="col-md-6">
                                    <label for="addr-edit-city" class="form-label">City</label>
                                    <input type="text" class="form-control" id="addr-edit-city" maxlength="18">
                                </div>
                                <div class="col-md-3">
                                    <label for="addr-edit-state" class="form-label">State</label>
                                    <input type="text" class="form-control" id="addr-edit-state" maxlength="2" style="text-transform: uppercase;">
                                </div>
                                <div class="col-md-3">
                                    <label for="addr-edit-zip" class="form-label">ZIP</label>
                                    <input type="text" class="form-control" id="addr-edit-zip" maxlength="9">
                                </div>
                                <div class="col-md-6">
                                    <label for="addr-edit-phone" class="form-label">Phone</label>
                                    <input type="text" class="form-control" id="addr-edit-phone" maxlength="15">
                                </div>
                                <div class="col-md-12">
                                    <button type="button" class="btn tls-btn-primary" onclick="saveAddress()">
                                        <i class="bi bi-check-circle me-1"></i>Save Address
                                    </button>
                                    <button type="button" class="btn tls-btn-secondary" onclick="cancelEditAddress()">
                                        <i class="bi bi-x-circle me-1"></i>Cancel
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- No Address Message -->
                        <div id="no-address-message">
                            <p class="text-muted mb-0">No address on file. Click "Edit Address" to add one.</p>
                        </div>
                    </div>
                </div>

                <!-- Contacts Section -->
                <div class="tls-form-card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-people me-2"></i>Contacts
                            <span class="badge bg-secondary ms-2" id="contact-count">0</span>
                        </h5>
                        <button type="button" class="btn btn-sm tls-btn-primary" onclick="showContactModal()">
                            <i class="bi bi-plus-circle me-1"></i>Add Contact
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="contacts-table-container">
                            <p class="text-muted">No contacts found. Click "Add Contact" to create one.</p>
                        </div>
                    </div>
                </div>

                <!-- Comments Section -->
                <div class="tls-form-card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-chat-left-text me-2"></i>Comments
                        </h5>
                        <button type="button" class="btn btn-sm tls-btn-primary" onclick="showCommentModal()">
                            <i class="bi bi-plus-circle me-1"></i>Add Comment
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="comments-container">
                            <p class="text-muted">No comments found.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <?php endif; ?>
</div>

<!-- Contact Modal -->
<div class="modal fade" id="contactModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="contactModalLabel">Add Contact</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="contact-modal-form">
                    <input type="hidden" id="contact-key" value="0">
                    <div class="mb-3">
                        <label for="contact-first-name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="contact-first-name" required>
                    </div>
                    <div class="mb-3">
                        <label for="contact-last-name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="contact-last-name" required>
                    </div>
                    <div class="mb-3">
                        <label for="contact-phone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="contact-phone">
                    </div>
                    <div class="mb-3">
                        <label for="contact-mobile" class="form-label">Mobile</label>
                        <input type="text" class="form-control" id="contact-mobile">
                    </div>
                    <div class="mb-3">
                        <label for="contact-email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="contact-email">
                    </div>
                    <div class="mb-3">
                        <label for="contact-relationship" class="form-label">Relationship</label>
                        <input type="text" class="form-control" id="contact-relationship">
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="contact-primary">
                            <label class="form-check-label" for="contact-primary">
                                Primary Contact
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn tls-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn tls-btn-primary" onclick="saveContact()">Save Contact</button>
            </div>
        </div>
    </div>
</div>

<!-- Comment Modal -->
<div class="modal fade" id="commentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="commentModalLabel">Add Comment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="comment-modal-form">
                    <input type="hidden" id="comment-key" value="0">
                    <div class="mb-3">
                        <label for="comment-text" class="form-label">Comment</label>
                        <textarea class="form-control" id="comment-text" rows="4" maxlength="255" required></textarea>
                        <small class="form-text text-muted">Maximum 255 characters</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn tls-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn tls-btn-primary" onclick="saveComment()">Save Comment</button>
            </div>
        </div>
    </div>
</div>

<script>
// Global variables
let currentOwnerKey = <?= $owner['OwnerKey'] ?? 0 ?>;
let contactModal;
let commentModal;

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap modals
    contactModal = new bootstrap.Modal(document.getElementById('contactModal'));
    commentModal = new bootstrap.Modal(document.getElementById('commentModal'));

    // Load address, contacts, and comments if owner is loaded
    if (currentOwnerKey > 0) {
        loadOwnerAddress();
        loadOwnerContacts();
        loadOwnerComments();
    }
});

// =====================
// NEW OWNER FUNCTIONS
// =====================

/**
 * Create new owner with confirmation
 */
function newOwner() {
    // Check for unsaved changes
    if (typeof TLSFormTracker !== 'undefined' && TLSFormTracker.hasChanges) {
        if (!confirm('You have unsaved changes. Are you sure? All unsaved changes will be lost.')) {
            return;
        }
    }

    if (!confirm('Are you sure you want to create a new owner? This will reserve an Owner Key in the database.')) {
        return;
    }

    // Call API to create new owner
    fetch('<?= base_url('safety/owner-maintenance/create-new') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Redirect to load the newly created owner
            window.location.href = '<?= base_url('safety/owner-maintenance/load/') ?>' + data.owner_key;
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error creating new owner:', error);
        alert('An error occurred while creating the new owner.');
    });
}

// =====================
// ADDRESS FUNCTIONS
// =====================

/**
 * Load owner's address via AJAX
 */
function loadOwnerAddress() {
    fetch(`<?= base_url('safety/owner-maintenance/get-address') ?>?owner_key=${currentOwnerKey}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.address) {
                displayAddress(data.address);
            } else {
                // No address found - show edit button and message
                document.getElementById('edit-address-btn').style.display = 'block';
                document.getElementById('no-address-message').style.display = 'block';
                document.getElementById('address-display').style.display = 'none';
                document.getElementById('address-edit').style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error loading address:', error);
        });
}

/**
 * Display address in view mode
 */
function displayAddress(address) {
    // Set hidden NameKey
    document.getElementById('addr-name-key').value = address.NameKey || 0;

    // Populate display elements
    document.getElementById('addr-name1').textContent = address.Name1 || '';
    document.getElementById('addr-name2').textContent = address.Name2 || '';
    document.getElementById('addr-address1').textContent = address.Address1 || '';
    document.getElementById('addr-address2').textContent = address.Address2 || '';
    document.getElementById('addr-city').textContent = address.City || '';

    let stateZip = '';
    if (address.State || address.Zip) {
        stateZip = ', ' + (address.State || '') + ' ' + (address.Zip || '');
    }
    document.getElementById('addr-state-zip').textContent = stateZip;

    if (address.Phone) {
        document.getElementById('addr-phone').textContent = 'Phone: ' + address.Phone;
    } else {
        document.getElementById('addr-phone').textContent = '';
    }

    // Show display mode, hide edit mode
    document.getElementById('edit-address-btn').style.display = 'block';
    document.getElementById('address-display').style.display = 'block';
    document.getElementById('address-edit').style.display = 'none';
    document.getElementById('no-address-message').style.display = 'none';
}

/**
 * Switch to address edit mode
 */
function editAddress() {
    const nameKey = document.getElementById('addr-name-key').value;

    if (nameKey > 0) {
        // Load existing address into edit form
        fetch(`<?= base_url('safety/owner-maintenance/get-address') ?>?owner_key=${currentOwnerKey}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.address) {
                    document.getElementById('addr-edit-name1').value = data.address.Name1 || '';
                    document.getElementById('addr-edit-name2').value = data.address.Name2 || '';
                    document.getElementById('addr-edit-address1').value = data.address.Address1 || '';
                    document.getElementById('addr-edit-address2').value = data.address.Address2 || '';
                    document.getElementById('addr-edit-city').value = data.address.City || '';
                    document.getElementById('addr-edit-state').value = data.address.State || '';
                    document.getElementById('addr-edit-zip').value = data.address.Zip || '';
                    document.getElementById('addr-edit-phone').value = data.address.Phone || '';
                }
            });
    } else {
        // Clear edit form for new address
        document.getElementById('addr-edit-name1').value = '';
        document.getElementById('addr-edit-name2').value = '';
        document.getElementById('addr-edit-address1').value = '';
        document.getElementById('addr-edit-address2').value = '';
        document.getElementById('addr-edit-city').value = '';
        document.getElementById('addr-edit-state').value = '';
        document.getElementById('addr-edit-zip').value = '';
        document.getElementById('addr-edit-phone').value = '';
    }

    // Show edit mode, hide display mode
    document.getElementById('address-edit').style.display = 'block';
    document.getElementById('address-display').style.display = 'none';
    document.getElementById('no-address-message').style.display = 'none';
    document.getElementById('edit-address-btn').style.display = 'none';
}

/**
 * Cancel address editing and return to display mode
 */
function cancelEditAddress() {
    const nameKey = document.getElementById('addr-name-key').value;

    if (nameKey > 0) {
        // Return to display mode
        document.getElementById('address-edit').style.display = 'none';
        document.getElementById('address-display').style.display = 'block';
        document.getElementById('edit-address-btn').style.display = 'block';
    } else {
        // Return to no address message
        document.getElementById('address-edit').style.display = 'none';
        document.getElementById('no-address-message').style.display = 'block';
        document.getElementById('edit-address-btn').style.display = 'block';
    }
}

/**
 * Save address via AJAX
 */
function saveAddress() {
    const addressData = {
        owner_key: currentOwnerKey,
        name_key: document.getElementById('addr-name-key').value,
        name1: document.getElementById('addr-edit-name1').value,
        name2: document.getElementById('addr-edit-name2').value,
        address1: document.getElementById('addr-edit-address1').value,
        address2: document.getElementById('addr-edit-address2').value,
        city: document.getElementById('addr-edit-city').value,
        state: document.getElementById('addr-edit-state').value,
        zip: document.getElementById('addr-edit-zip').value,
        phone: document.getElementById('addr-edit-phone').value
    };

    fetch('<?= base_url('safety/owner-maintenance/save-address') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(addressData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update NameKey if it was a new address
            if (data.address && data.address.NameKey) {
                document.getElementById('addr-name-key').value = data.address.NameKey;
            }

            // Display the updated address
            displayAddress(data.address);

            // Show success message
            alert('Address saved successfully!');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error saving address:', error);
        alert('An error occurred while saving the address.');
    });
}

// =====================
// CONTACT FUNCTIONS
// =====================

/**
 * Load owner's contacts via AJAX
 */
function loadOwnerContacts() {
    fetch(`<?= base_url('safety/owner-maintenance/get-contacts') ?>?owner_key=${currentOwnerKey}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayContacts(data.contacts);
            }
        })
        .catch(error => {
            console.error('Error loading contacts:', error);
        });
}

/**
 * Display contacts in table
 */
function displayContacts(contacts) {
    const container = document.getElementById('contacts-table-container');
    document.getElementById('contact-count').textContent = contacts.length;

    if (contacts.length === 0) {
        container.innerHTML = '<p class="text-muted">No contacts found. Click "Add Contact" to create one.</p>';
        return;
    }

    let html = '<table class="table table-sm table-hover">';
    html += '<thead><tr><th>Name</th><th>Phone</th><th>Mobile</th><th>Relationship</th><th>Actions</th></tr></thead>';
    html += '<tbody>';

    contacts.forEach(contact => {
        html += '<tr>';
        html += `<td>${contact.ContactName || ''}</td>`;
        html += `<td>${contact.Phone || ''}</td>`;
        html += `<td>${contact.Mobile || ''}</td>`;
        html += `<td>${contact.Relationship || ''}</td>`;
        html += `<td>
            <button type="button" class="btn btn-sm tls-btn-primary" onclick="editContact(${contact.ContactKey})">
                <i class="bi bi-pencil"></i>
            </button>
            <button type="button" class="btn btn-sm tls-btn-secondary" onclick="deleteContact(${contact.ContactKey})">
                <i class="bi bi-trash"></i>
            </button>
        </td>`;
        html += '</tr>';
    });

    html += '</tbody></table>';
    container.innerHTML = html;
}

/**
 * Show contact modal for new contact
 */
function showContactModal() {
    // Clear form
    document.getElementById('contact-key').value = '0';
    document.getElementById('contact-first-name').value = '';
    document.getElementById('contact-last-name').value = '';
    document.getElementById('contact-phone').value = '';
    document.getElementById('contact-mobile').value = '';
    document.getElementById('contact-email').value = '';
    document.getElementById('contact-relationship').value = '';
    document.getElementById('contact-primary').checked = false;

    document.getElementById('contactModalLabel').textContent = 'Add Contact';
    contactModal.show();
}

/**
 * Edit contact - load into modal
 */
function editContact(contactKey) {
    fetch(`<?= base_url('safety/owner-maintenance/get-contacts') ?>?owner_key=${currentOwnerKey}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const contact = data.contacts.find(c => c.ContactKey == contactKey);
                if (contact) {
                    // Split ContactName into first and last
                    const nameParts = (contact.ContactName || '').split(' ');
                    const firstName = nameParts[0] || '';
                    const lastName = nameParts.slice(1).join(' ') || '';

                    document.getElementById('contact-key').value = contact.ContactKey;
                    document.getElementById('contact-first-name').value = firstName;
                    document.getElementById('contact-last-name').value = lastName;
                    document.getElementById('contact-phone').value = contact.Phone || '';
                    document.getElementById('contact-mobile').value = contact.Mobile || '';
                    document.getElementById('contact-email').value = contact.Email || '';
                    document.getElementById('contact-relationship').value = contact.Relationship || '';
                    document.getElementById('contact-primary').checked = contact.PrimaryContact == 1;

                    document.getElementById('contactModalLabel').textContent = 'Edit Contact';
                    contactModal.show();
                }
            }
        });
}

/**
 * Save contact via AJAX
 */
function saveContact() {
    const firstName = document.getElementById('contact-first-name').value.trim();
    const lastName = document.getElementById('contact-last-name').value.trim();
    const contactName = firstName + (lastName ? ' ' + lastName : '');

    const contactData = {
        owner_key: currentOwnerKey,
        contact_key: document.getElementById('contact-key').value,
        contact_name: contactName,
        telephone_no: document.getElementById('contact-phone').value,
        cell_no: document.getElementById('contact-mobile').value,
        email: document.getElementById('contact-email').value,
        contact_function: document.getElementById('contact-relationship').value,
        primary_contact: document.getElementById('contact-primary').checked ? 1 : 0
    };

    fetch('<?= base_url('safety/owner-maintenance/save-contact') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(contactData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            contactModal.hide();
            loadOwnerContacts();
            alert('Contact saved successfully!');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error saving contact:', error);
        alert('An error occurred while saving the contact.');
    });
}

/**
 * Delete contact with confirmation
 */
function deleteContact(contactKey) {
    if (!confirm('Are you sure you want to delete this contact?')) {
        return;
    }

    fetch('<?= base_url('safety/owner-maintenance/delete-contact') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ contact_key: contactKey })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadOwnerContacts();
            alert('Contact deleted successfully!');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error deleting contact:', error);
        alert('An error occurred while deleting the contact.');
    });
}

// =====================
// COMMENT FUNCTIONS
// =====================

/**
 * Load owner's comments via AJAX
 */
function loadOwnerComments() {
    fetch(`<?= base_url('safety/owner-maintenance/get-comments') ?>?owner_key=${currentOwnerKey}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayComments(data.comments);
            }
        })
        .catch(error => {
            console.error('Error loading comments:', error);
        });
}

/**
 * Display comments
 */
function displayComments(comments) {
    const container = document.getElementById('comments-container');

    if (comments.length === 0) {
        container.innerHTML = '<p class="text-muted">No comments found.</p>';
        return;
    }

    let html = '';
    comments.forEach(comment => {
        html += '<div class="card mb-2">';
        html += '<div class="card-body">';
        html += `<p class="mb-1">${comment.Comment || ''}</p>`;
        html += '<div class="d-flex justify-content-between align-items-center">';
        html += `<small class="text-muted">By ${comment.CommentBy || 'Unknown'} on ${comment.CommentDate || 'Unknown date'}</small>`;
        html += '<div>';
        html += `<button type="button" class="btn btn-sm tls-btn-primary" onclick="editComment(${comment.CommentKey})">
                    <i class="bi bi-pencil"></i>
                </button>`;
        html += `<button type="button" class="btn btn-sm tls-btn-secondary" onclick="deleteComment(${comment.CommentKey})">
                    <i class="bi bi-trash"></i>
                </button>`;
        html += '</div></div></div></div>';
    });

    container.innerHTML = html;
}

/**
 * Show comment modal for new comment
 */
function showCommentModal() {
    document.getElementById('comment-key').value = '0';
    document.getElementById('comment-text').value = '';
    document.getElementById('commentModalLabel').textContent = 'Add Comment';
    commentModal.show();
}

/**
 * Edit comment - load into modal
 */
function editComment(commentKey) {
    fetch(`<?= base_url('safety/owner-maintenance/get-comments') ?>?owner_key=${currentOwnerKey}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const comment = data.comments.find(c => c.CommentKey == commentKey);
                if (comment) {
                    document.getElementById('comment-key').value = comment.CommentKey;
                    document.getElementById('comment-text').value = comment.Comment || '';
                    document.getElementById('commentModalLabel').textContent = 'Edit Comment';
                    commentModal.show();
                }
            }
        });
}

/**
 * Save comment via AJAX
 */
function saveComment() {
    const commentData = {
        owner_key: currentOwnerKey,
        comment_key: document.getElementById('comment-key').value,
        comment: document.getElementById('comment-text').value
    };

    fetch('<?= base_url('safety/owner-maintenance/save-comment') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(commentData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            commentModal.hide();
            loadOwnerComments();
            alert('Comment saved successfully!');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error saving comment:', error);
        alert('An error occurred while saving the comment.');
    });
}

/**
 * Delete comment with confirmation
 */
function deleteComment(commentKey) {
    if (!confirm('Are you sure you want to delete this comment?')) {
        return;
    }

    fetch('<?= base_url('safety/owner-maintenance/delete-comment') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ comment_key: commentKey })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadOwnerComments();
            alert('Comment deleted successfully!');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error deleting comment:', error);
        alert('An error occurred while deleting the comment.');
    });
}

// =====================
// FORM FUNCTIONS
// =====================

/**
 * Reset form to original loaded values
 */
function resetForm() {
    if (confirm('Are you sure you want to reset all changes?')) {
        location.reload();
    }
}
</script>

<!-- TLS JavaScript libraries -->
<script src="<?= base_url('js/tls-autocomplete.js') ?>"></script>
<script src="<?= base_url('js/tls-form-tracker.js') ?>"></script>

<?= $this->endSection() ?>
