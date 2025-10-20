<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<!-- Page Header -->
<div class="tls-page-header">
    <h1 class="tls-page-title">
        <i class="bi-person-fill me-2"></i>User Maintenance
    </h1>
    <div class="tls-top-actions">
        <?php if ($user): ?>
        <!-- User is loaded - show Save/Reset buttons -->
        <button type="button" class="btn tls-btn-primary" id="tls-save-btn" onclick="saveUser()">
            <i class="bi bi-save me-2"></i>Save
        </button>
        <button type="button" class="btn tls-btn-secondary" id="tls-reset-btn" onclick="resetForm()">
            <i class="bi bi-arrow-counterclockwise me-2"></i>Reset
        </button>
        <?php else: ?>
        <!-- No user loaded - show New User button -->
        <button type="button" class="btn tls-btn-primary" onclick="newUser()">
            <i class="bi bi-plus-circle me-2"></i>New User
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- User Search Card -->
<div class="tls-form-card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="bi bi-search me-2"></i>User Search
        </h5>
    </div>
    <div class="card-body">
        <form id="searchForm" method="post" action="<?= base_url('systems/user-maintenance/search') ?>">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-md-8">
                    <div class="position-relative">
                        <div class="input-group">
                            <input type="text" class="form-control" id="userSearch" name="user_search"
                                   placeholder="Search by user name or enter User ID"
                                   autocomplete="off">
                            <button type="submit" class="btn tls-btn-primary">
                                <i class="bi bi-arrow-down-circle me-2"></i>Load User
                            </button>
                        </div>
                        <div id="userSearchResults" class="autocomplete-dropdown"></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="includeInactive" name="include_inactive">
                        <label class="form-check-label" for="includeInactive">
                            Include Inactive Users
                        </label>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if ($user): ?>
<!-- User Information Form (Two-Column Layout) -->
<form id="userForm" method="post" action="<?= base_url('systems/user-maintenance/save') ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="original_user_id" value="<?= esc($user['UserID'] ?? '') ?>">

    <!-- Save Indicator -->
    <div id="tls-save-indicator" class="tls-save-indicator" style="display: none;">
        <div class="alert alert-warning" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <span id="tls-change-counter">0</span> unsaved changes
        </div>
    </div>

    <!-- Two-Column Layout -->
    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-6">
            <!-- Basic Information Card -->
            <div class="tls-form-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-person-fill me-2"></i>Basic Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="user_id" class="form-label">User ID <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="user_id" name="user_id"
                               value="<?= esc($user['UserID'] ?? '') ?>"
                               maxlength="15" required <?= !empty($user['UserID']) && !$isNewUser ? 'readonly' : '' ?>>
                    </div>
                    <div class="mb-3">
                        <label for="user_name" class="form-label">User Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="user_name" name="user_name"
                               value="<?= esc($user['UserName'] ?? '') ?>"
                               maxlength="35" required>
                    </div>
                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="first_name" name="first_name"
                               value="<?= esc($user['FirstName'] ?? '') ?>"
                               maxlength="50" required>
                    </div>
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="last_name" name="last_name"
                               value="<?= esc($user['LastName'] ?? '') ?>"
                               maxlength="50" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email"
                               value="<?= esc($user['Email'] ?? '') ?>"
                               maxlength="50">
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="active" name="active"
                               <?= ($user['Active'] ?? 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="active">
                            Active
                        </label>
                    </div>
                </div>
            </div>

            <!-- Password Section (PII Protected) -->
            <div class="tls-form-card tls-pii-section mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-shield-lock me-2"></i>Password Management
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Password changes are logged and monitored for security purposes.
                    </div>

                    <!-- Show/Hide Password Section -->
                    <div id="show_password_section">
                        <div class="text-center py-3">
                            <button type="button" class="btn btn-outline-warning" onclick="showPassword()">
                                <i class="bi bi-eye me-2"></i>Change Password
                            </button>
                            <p class="small text-muted mt-2">Click to change user password.</p>
                        </div>
                    </div>

                    <!-- Password Section -->
                    <div id="password_section" style="display: none;">
                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="password" name="password"
                                   placeholder="Enter new password">
                            <small class="text-muted">Leave blank to keep current password</small>
                        </div>
                        <div class="text-center">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="hidePassword()">
                                <i class="bi bi-eye-slash me-2"></i>Cancel Password Change
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Organization Card -->
            <div class="tls-form-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-building me-2"></i>Organization
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="company_id" class="form-label">Company</label>
                        <select class="form-select" id="company_id" name="company_id">
                            <option value="">Select Company</option>
                            <?php foreach ($companies as $company): ?>
                                <option value="<?= esc($company['CompanyID']) ?>"
                                        <?= ($user['CompanyID'] ?? '') == $company['CompanyID'] ? 'selected' : '' ?>>
                                    <?= esc($company['CompanyName']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="division_id" class="form-label">Division</label>
                        <select class="form-select" id="division_id" name="division_id">
                            <option value="">Select Division</option>
                            <?php foreach ($divisions as $division): ?>
                                <option value="<?= esc($division['DivisionID']) ?>"
                                        <?= ($user['DivisionID'] ?? '') == $division['DivisionID'] ? 'selected' : '' ?>>
                                    <?= esc($division['Name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="department_id" class="form-label">Department</label>
                        <select class="form-select" id="department_id" name="department_id">
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= esc($dept['DepartmentID']) ?>"
                                        <?= ($user['DepartmentID'] ?? '') == $dept['DepartmentID'] ? 'selected' : '' ?>>
                                    <?= esc($dept['Description']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="user_type" class="form-label">User Type</label>
                        <select class="form-select" id="user_type" name="user_type">
                            <option value="">Select User Type</option>
                            <?php foreach ($userTypes as $type): ?>
                                <option value="<?= esc($type['Type']) ?>"
                                        <?= ($user['UserType'] ?? '') == $type['Type'] ? 'selected' : '' ?>>
                                    <?= esc($type['Description']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Financial Information Card -->
            <div class="tls-form-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-cash-coin me-2"></i>Financial Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="commission_pct" class="form-label">Commission Percentage</label>
                        <input type="number" class="form-control" id="commission_pct" name="commission_pct"
                               value="<?= isset($user['CommissionPct']) && $user['CommissionPct'] !== null ? number_format(floatval($user['CommissionPct']) * 100, 2, '.', '') : '' ?>"
                               step="0.01" min="0" max="100">
                        <small class="text-muted">Enter as percentage (e.g., 5.5 for 5.5%)</small>
                    </div>
                    <div class="mb-3">
                        <label for="commission_min_profit" class="form-label">Commission Minimum Profit</label>
                        <input type="number" class="form-control" id="commission_min_profit" name="commission_min_profit"
                               value="<?= isset($user['CommissionMinProfit']) && $user['CommissionMinProfit'] !== null ? number_format(floatval($user['CommissionMinProfit']) * 100, 2, '.', '') : '' ?>"
                               step="0.01" min="0" max="100">
                        <small class="text-muted">Enter as percentage (e.g., 15.0 for 15.0%)</small>
                    </div>
                </div>
            </div>

        </div> <!-- End Left Column -->

        <!-- Right Column -->
        <div class="col-lg-6">
            <!-- Profile Information Card -->
            <div class="tls-form-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-person-badge me-2"></i>Profile Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title"
                               value="<?= esc($user['Title'] ?? '') ?>"
                               maxlength="50">
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone"
                               value="<?= esc($user['Phone'] ?? '') ?>"
                               maxlength="20">
                    </div>
                    <div class="mb-3">
                        <label for="fax" class="form-label">Fax</label>
                        <input type="text" class="form-control" id="fax" name="fax"
                               value="<?= esc($user['Fax'] ?? '') ?>"
                               maxlength="20">
                    </div>
                    <div class="mb-3">
                        <label for="extension" class="form-label">Extension</label>
                        <input type="number" class="form-control" id="extension" name="extension"
                               value="<?= esc($user['Extension'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="team_key" class="form-label">Team</label>
                        <select class="form-select" id="team_key" name="team_key">
                            <option value="">Select Team</option>
                            <?php foreach ($teams as $team): ?>
                                <option value="<?= esc($team['TeamKey']) ?>"
                                        <?= ($user['TeamKey'] ?? '') == $team['TeamKey'] ? 'selected' : '' ?>>
                                    <?= esc($team['TeamName']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Important Dates Card -->
            <div class="tls-form-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-calendar-event me-2"></i>Important Dates
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="hire_date" class="form-label">Hire Date</label>
                        <input type="date" class="form-control" id="hire_date" name="hire_date"
                               value="<?= !empty($user['HireDate']) && $user['HireDate'] !== '1900-01-01' ? date('Y-m-d', strtotime($user['HireDate'])) : '' ?>">
                    </div>
                    <div class="mb-3">
                        <label for="term_date" class="form-label">Term Date</label>
                        <input type="date" class="form-control" id="term_date" name="term_date"
                               value="<?= !empty($user['TermDate']) && $user['TermDate'] !== '1899-12-30 00:00:00.000' ? date('Y-m-d', strtotime($user['TermDate'])) : '' ?>">
                    </div>
                    <div class="mb-3">
                        <label for="password_changed" class="form-label">Password Last Changed</label>
                        <input type="text" class="form-control" id="password_changed" name="password_changed"
                               value="<?= !empty($user['PasswordChanged']) ? date('Y-m-d H:i:s', strtotime($user['PasswordChanged'])) : 'Never' ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="last_login" class="form-label">Last Login</label>
                        <input type="text" class="form-control" id="last_login" name="last_login"
                               value="<?= !empty($user['LastLogin']) ? date('Y-m-d H:i:s', strtotime($user['LastLogin'])) : 'Never' ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="last_update" class="form-label">Last Update</label>
                        <input type="text" class="form-control" id="last_update" name="last_update"
                               value="<?= !empty($user['LastUpdate']) ? date('Y-m-d H:i:s', strtotime($user['LastUpdate'])) : 'Never' ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="last_web_access" class="form-label">Last Web Access</label>
                        <input type="text" class="form-control" id="last_web_access" name="last_web_access"
                               value="<?= !empty($user['LastWebAccess']) ? date('Y-m-d H:i:s', strtotime($user['LastWebAccess'])) : 'Never' ?>" readonly>
                    </div>
                </div>
            </div>

            <!-- System Flags Card -->
            <div class="tls-form-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-gear me-2"></i>System Flags
                    </h5>
                </div>
                <div class="card-body">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="rapid_log_user" name="rapid_log_user"
                               <?= ($user['RapidLogUser'] ?? 0) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="rapid_log_user">
                            Rapid Log User
                        </label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="satellite_installs" name="satellite_installs"
                               <?= ($user['SatelliteInstalls'] ?? 0) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="satellite_installs">
                            Satellite Installs
                        </label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="primary_account" name="primary_account"
                               <?= ($user['PrimaryAccount'] ?? 0) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="primary_account">
                            Primary Account
                        </label>
                    </div>
                </div>
            </div>

        </div> <!-- End Right Column -->
    </div> <!-- End Two-Column Row -->
</form>
<?php else: ?>
<!-- No User Selected Message -->
<div class="tls-form-card">
    <div class="card-body text-center py-5">
        <i class="bi bi-search text-muted mb-3" style="font-size: 3rem;"></i>
        <h5 class="text-muted mb-2">No User Selected</h5>
        <p class="text-muted">Please search for and select a user to view and edit their information.</p>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('js/tls-form-tracker.js') ?>"></script>
<script src="<?= base_url('js/tls-autocomplete.js') ?>"></script>

<script>
// Initialize form tracker only if form exists
<?php if ($user): ?>
let tracker = null;
document.addEventListener('DOMContentLoaded', function() {
    tracker = new TLSFormTracker({
        formSelector: '#userForm',
        saveButtonId: 'tls-save-btn',
        resetButtonId: 'tls-reset-btn',
        saveIndicatorId: 'tls-save-indicator',
        excludeFields: ['original_user_id'], // Don't track these for changes
        onSave: function(changes) {
            console.log('Saving user changes:', changes);
            // Submit the form normally
            document.getElementById('userForm').submit();
        }
    });
});
<?php endif; ?>

// User search autocomplete
const userSearchField = document.getElementById('userSearch');

// Initialize autocomplete for name searches
const userAutocomplete = new TLSAutocomplete(
    userSearchField,
    'users',
    function(user) {
        console.log('User selected:', user);
        // Set the search field value to the UserID
        userSearchField.value = user.value;
        // Auto-submit the form when user is selected from dropdown
        document.getElementById('searchForm').submit();
    }
);

// Re-trigger search when include_inactive checkbox changes
document.getElementById('includeInactive')?.addEventListener('change', function() {
    const searchInput = document.getElementById('userSearch');
    if (searchInput.value.trim().length >= 2) {
        // Clear current results and re-search
        userAutocomplete.search(searchInput.value.trim());
    }
});

function saveUser() {
    if (tracker && tracker.validateRequired()) {
        document.getElementById('userForm').submit();
    } else {
        document.getElementById('userForm').submit();
    }
}

function resetForm() {
    <?php if ($user): ?>
    if (tracker && tracker.hasChanges()) {
        if (confirm('You have unsaved changes. Are you sure you want to reset the form?')) {
            // Clear the unsaved changes flag before reloading to prevent duplicate alerts
            tracker.hasUnsavedChanges = false;
            location.reload();
        }
    } else {
        location.reload();
    }
    <?php else: ?>
    location.reload();
    <?php endif; ?>
}

function newUser() {
    // Navigate to show new user form
    window.location.href = '<?= base_url('systems/user-maintenance') ?>?new=1';
}

function showPassword() {
    // Show the password field and hide the show button
    document.getElementById('password_section').style.display = 'block';
    document.getElementById('show_password_section').style.display = 'none';
}

function hidePassword() {
    // Hide the password field and show the show button
    document.getElementById('password_section').style.display = 'none';
    document.getElementById('show_password_section').style.display = 'block';
    // Clear the password field
    document.getElementById('password').value = '';
}
</script>
<?= $this->endSection() ?>
