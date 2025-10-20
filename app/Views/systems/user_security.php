<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<style>
    /* Permission card styling */
    .permission-card {
        margin-bottom: 1rem;
    }

    /* Masonry layout - cards pack naturally without equal heights */
    #permissionCards.show {
        column-count: 2;
        column-gap: 1.5rem;
        column-fill: balance;
    }

    .permission-card-wrapper {
        break-inside: avoid;
        display: inline-block;
        width: 100%;
        margin-bottom: 1rem;
    }

    /* Stack on smaller screens */
    @media (max-width: 991.98px) {
        #permissionCards.show {
            column-count: 1;
        }
    }

    .permission-item {
        padding: 0.5rem 0;
        border-bottom: 1px solid #f8f9fa;
    }

    .permission-item:last-child {
        border-bottom: none;
    }

    .form-check-input:checked {
        background-color: #198754;
        border-color: #198754;
    }

    .bulk-controls {
        background: #f8f9fa;
        border-radius: 6px;
        padding: 0.75rem;
        margin-bottom: 1rem;
    }

    .stats-badge {
        font-size: 0.75rem;
        margin-left: 0.5rem;
    }

    .search-highlight {
        background-color: #fff3cd;
    }

    .loading {
        opacity: 0.6;
        pointer-events: none;
    }
</style>

<!-- Page Header -->
<div class="tls-page-header">
    <h1 class="tls-page-title">
        <i class="bi bi-shield-lock me-2"></i>User Security Management
    </h1>
    <div class="tls-top-actions">
        <button type="button" class="btn tls-btn-primary" id="tls-save-btn" disabled>
            <i class="bi bi-check-circle me-2"></i>Save Changes
        </button>
        <button type="button" class="btn tls-btn-secondary" id="tls-reset-btn" disabled>
            <i class="bi bi-arrow-counterclockwise me-2"></i>Reset
        </button>
    </div>
</div>

<!-- Save Indicator Banner -->
<div id="tls-save-indicator" class="tls-save-indicator" style="display: none;">
    <div class="alert alert-warning" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        You have <span id="tls-change-counter">0</span> unsaved changes. Remember to save before navigating away.
    </div>
</div>

<!-- User Selection and Controls -->
<div class="tls-form-card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="bi bi-person-gear me-2"></i>User Selection
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <label for="userSelect" class="form-label fw-bold">Select User:</label>
                <select class="form-select" id="userSelect">
                    <option value="">Choose a user...</option>
                    <?php foreach ($users as $userData): ?>
                        <option value="<?= esc($userData['user_id']) ?>">
                            <?= esc($userData['user_name']) ?> (<?= esc($userData['user_id']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="searchBox" class="form-label fw-bold">Search Permissions:</label>
                <input type="text" class="form-control" id="searchBox" placeholder="Search menu items..." disabled>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">Permission Summary:</label>
                <div class="d-flex align-items-center" id="permissionSummary">
                    <span class="badge bg-success me-2" id="grantedCount">0 Granted</span>
                    <span class="badge bg-danger me-2" id="deniedCount">0 Denied</span>
                    <span class="badge bg-secondary" id="totalCount">0 Total</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Controls -->
<div class="bulk-controls" id="bulkControls" style="display: none;">
    <div class="row">
        <div class="col-md-6">
            <strong>Bulk Actions:</strong>
            <button class="btn btn-sm btn-outline-success ms-2" id="grantAllBtn">
                <i class="bi bi-check-all"></i> Grant All Visible
            </button>
            <button class="btn btn-sm btn-outline-danger ms-1" id="denyAllBtn">
                <i class="bi bi-x-lg"></i> Deny All Visible
            </button>
        </div>
        <div class="col-md-6 text-end">
            <strong>Quick Roles:</strong>
            <button class="btn btn-sm btn-outline-primary ms-1 role-template" data-role="dispatch">Dispatch</button>
            <button class="btn btn-sm btn-outline-primary ms-1 role-template" data-role="broker">Broker</button>
            <button class="btn btn-sm btn-outline-primary ms-1 role-template" data-role="accounting">Accounting</button>
        </div>
    </div>
</div>

<!-- Permission Cards Grid -->
<div id="permissionCards" style="display: none;">
    <?php foreach ($organizedPermissions as $category): ?>
        <div class="permission-card-wrapper">
            <div class="permission-card tls-form-card" data-category="<?= esc($category['key']) ?>">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="form-check form-switch me-3">
                            <input class="form-check-input category-toggle-all"
                                   type="checkbox"
                                   id="toggle-all-<?= esc($category['key']) ?>"
                                   data-category="<?= esc($category['key']) ?>"
                                   title="Toggle all permissions in this category">
                        </div>
                        <div>
                            <i class="<?= esc($category['icon']) ?> me-2"></i>
                            <?= esc($category['label']) ?>
                        </div>
                    </div>
                    <div>
                        <span class="stats-badge badge bg-secondary category-stats">0/0</span>
                        <button class="btn btn-sm btn-outline-secondary ms-2 toggle-category"
                                data-bs-toggle="collapse"
                                data-bs-target="#category-<?= esc($category['key']) ?>"
                                type="button">
                            <i class="bi bi-chevron-down"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body collapse show" id="category-<?= esc($category['key']) ?>">
                    <?php foreach ($category['items'] as $item): ?>
                        <div class="permission-item d-flex justify-content-between align-items-center"
                             data-menu-key="<?= esc($item['key']) ?>">
                            <div>
                                <label class="form-check-label fw-medium" for="perm-<?= esc($item['key']) ?>">
                                    <?= esc($item['label']) ?>
                                </label>
                                <div class="small text-muted"><?= esc($item['description']) ?></div>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input permission-toggle"
                                       type="checkbox"
                                       id="perm-<?= esc($item['key']) ?>"
                                       data-permission="<?= esc($item['key']) ?>">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Loading indicator -->
<div class="text-center" id="loadingIndicator" style="display: none;">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <p class="mt-2">Loading user permissions...</p>
</div>

<!-- No user selected message -->
<div class="text-center text-muted py-5" id="noUserMessage">
    <i class="bi bi-person-x display-1"></i>
    <p class="mt-3">Select a user to view and manage their permissions.</p>
</div>

<script>
let currentUserId = '';
let userPermissions = {};
let hasUnsavedChanges = false;
let originalPermissions = {};
let permissionChanges = [];
let saveBtn, resetBtn, saveIndicator, userSelect, searchBox;

document.addEventListener('DOMContentLoaded', function() {
    userSelect = document.getElementById('userSelect');
    searchBox = document.getElementById('searchBox');
    saveBtn = document.getElementById('tls-save-btn');
    resetBtn = document.getElementById('tls-reset-btn');
    saveIndicator = document.getElementById('tls-save-indicator');

    // Initialize page state
    hidePermissions();
    updateSummaryStats();

    // User selection change
    userSelect.addEventListener('change', function() {
        const userId = this.value;
        if (userId) {
            // Check for unsaved changes
            if (hasUnsavedChanges) {
                if (!confirm('You have unsaved changes. Are you sure you want to switch users? Your changes will be lost.')) {
                    // Revert to previous user
                    this.value = currentUserId;
                    return;
                }
            }
            loadUserPermissions(userId);
        } else {
            hidePermissions();
        }
    });

    // Permission toggle change
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('permission-toggle')) {
            const permissionKey = e.target.dataset.permission;
            const newValue = e.target.checked;
            const oldValue = originalPermissions[permissionKey] || false;

            // Track the change
            userPermissions[permissionKey] = newValue;

            // Update change tracking
            if (newValue !== oldValue) {
                // Add or update change
                const existingIndex = permissionChanges.findIndex(c => c.menu === permissionKey);
                if (existingIndex >= 0) {
                    permissionChanges[existingIndex].granted = newValue;
                } else {
                    permissionChanges.push({
                        menu: permissionKey,
                        granted: newValue
                    });
                }
            } else {
                // Remove change (reverted to original)
                permissionChanges = permissionChanges.filter(c => c.menu !== permissionKey);
            }

            updateChangeState();
            updateSummaryStats();
            updateCategoryStats();
        }
    });

    // Save button
    saveBtn.addEventListener('click', function() {
        if (!hasUnsavedChanges || !currentUserId) return;
        savePermissions();
    });

    // Reset button
    resetBtn.addEventListener('click', function() {
        if (!hasUnsavedChanges) return;
        if (confirm('Are you sure you want to reset all changes?')) {
            resetPermissions();
        }
    });

    // Search box
    searchBox.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        filterPermissions(searchTerm);
    });

    // Bulk actions
    document.getElementById('grantAllBtn').addEventListener('click', function() {
        bulkSetPermissions(true);
    });

    document.getElementById('denyAllBtn').addEventListener('click', function() {
        bulkSetPermissions(false);
    });

    // Role templates
    document.querySelectorAll('.role-template').forEach(btn => {
        btn.addEventListener('click', function() {
            const role = this.dataset.role;
            applyRoleTemplate(role);
        });
    });

    // Prevent accidental navigation
    window.addEventListener('beforeunload', function(e) {
        if (hasUnsavedChanges) {
            e.preventDefault();
            e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
            return e.returnValue;
        }
    });

    // Category toggle icons
    document.querySelectorAll('.toggle-category').forEach(btn => {
        btn.addEventListener('click', function() {
            const icon = this.querySelector('i');
            setTimeout(() => {
                const target = document.querySelector(this.dataset.bsTarget);
                if (target.classList.contains('show')) {
                    icon.className = 'bi bi-chevron-down';
                } else {
                    icon.className = 'bi bi-chevron-up';
                }
            }, 350);
        });
    });

    // Category "toggle all" checkboxes
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('category-toggle-all')) {
            const category = e.target.dataset.category;
            const checked = e.target.checked;
            const card = document.querySelector(`.permission-card[data-category="${category}"]`);

            // Toggle all permission switches in this category
            if (card) {
                const toggles = card.querySelectorAll('.permission-toggle');
                toggles.forEach(toggle => {
                    if (toggle.checked !== checked) {
                        toggle.checked = checked;
                        // Create a bubbling change event
                        const changeEvent = new Event('change', { bubbles: true });
                        toggle.dispatchEvent(changeEvent);
                    }
                });
            }
        }
    });
});

function loadUserPermissions(userId) {
    console.log('loadUserPermissions called with userId:', userId);
    currentUserId = userId;
    showLoading(true);

    const url = '<?= base_url('systems/user-security/get-permissions') ?>';
    const body = 'user_id=' + encodeURIComponent(userId) + '&<?= csrf_token() ?>=' + encodeURIComponent('<?= csrf_hash() ?>');

    console.log('Fetching URL:', url);
    console.log('Request body:', body);

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: body
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            console.log('Permissions loaded:', Object.keys(data.permissions).length, 'items');
            userPermissions = data.permissions;
            originalPermissions = JSON.parse(JSON.stringify(data.permissions));
            permissionChanges = [];
            displayPermissions();
            updateSummaryStats();
            updateCategoryStats();
            updateChangeState();
            showPermissions();
        } else {
            console.error('Error in response:', data.error);
            alert('Error loading permissions: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        alert('Failed to load user permissions: ' + error.message);
    })
    .finally(() => {
        showLoading(false);
    });
}

function displayPermissions() {
    document.querySelectorAll('.permission-toggle').forEach(toggle => {
        const permissionKey = toggle.dataset.permission;
        toggle.checked = userPermissions[permissionKey] || false;
    });
}

function savePermissions() {
    if (!currentUserId || permissionChanges.length === 0) return;

    showLoading(true);
    saveBtn.disabled = true;

    fetch('<?= base_url('systems/user-security/save-permissions') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'user_id=' + encodeURIComponent(currentUserId) +
              '&changes=' + encodeURIComponent(JSON.stringify(permissionChanges)) +
              '&<?= csrf_token() ?>=' + encodeURIComponent('<?= csrf_hash() ?>')
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            // Update original permissions
            originalPermissions = JSON.parse(JSON.stringify(userPermissions));
            permissionChanges = [];
            updateChangeState();
        } else {
            alert('Error saving permissions: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to save permissions');
    })
    .finally(() => {
        showLoading(false);
        saveBtn.disabled = false;
    });
}

function resetPermissions() {
    userPermissions = JSON.parse(JSON.stringify(originalPermissions));
    permissionChanges = [];
    displayPermissions();
    updateSummaryStats();
    updateCategoryStats();
    updateChangeState();
}

function bulkSetPermissions(granted) {
    document.querySelectorAll('.permission-toggle:not([style*="display: none"])').forEach(toggle => {
        if (toggle.closest('.permission-item').style.display !== 'none') {
            toggle.checked = granted;
            toggle.dispatchEvent(new Event('change'));
        }
    });
}

function applyRoleTemplate(role) {
    if (!currentUserId) return;

    if (!confirm(`Are you sure you want to apply the '${role}' role template? This will overwrite all current permissions.`)) {
        return;
    }

    showLoading(true);

    fetch('<?= base_url('systems/user-security/apply-role') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'user_id=' + encodeURIComponent(currentUserId) +
              '&role=' + encodeURIComponent(role) +
              '&<?= csrf_token() ?>=' + encodeURIComponent('<?= csrf_hash() ?>')
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            // Reload permissions
            loadUserPermissions(currentUserId);
        } else {
            alert('Error applying role template: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to apply role template');
    })
    .finally(() => {
        showLoading(false);
    });
}

function filterPermissions(searchTerm) {
    document.querySelectorAll('.permission-item').forEach(item => {
        const label = item.querySelector('.form-check-label').textContent.toLowerCase();
        const description = item.querySelector('.text-muted').textContent.toLowerCase();

        if (searchTerm === '' || label.includes(searchTerm) || description.includes(searchTerm)) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });

    // Hide empty categories
    document.querySelectorAll('.permission-card').forEach(card => {
        const visibleItems = card.querySelectorAll('.permission-item:not([style*="display: none"])').length;
        card.style.display = visibleItems > 0 ? '' : 'none';
    });
}

function updateSummaryStats() {
    let granted = 0;
    let denied = 0;
    let total = 0;

    document.querySelectorAll('.permission-toggle').forEach(toggle => {
        total++;
        if (toggle.checked) {
            granted++;
        } else {
            denied++;
        }
    });

    document.getElementById('grantedCount').textContent = granted + ' Granted';
    document.getElementById('deniedCount').textContent = denied + ' Denied';
    document.getElementById('totalCount').textContent = total + ' Total';
}

function updateCategoryStats() {
    document.querySelectorAll('.permission-card').forEach(card => {
        const toggles = card.querySelectorAll('.permission-toggle');
        const category = card.dataset.category;
        const categoryToggle = document.querySelector(`.category-toggle-all[data-category="${category}"]`);

        let granted = 0;
        toggles.forEach(toggle => {
            if (toggle.checked) granted++;
        });

        const statsEl = card.querySelector('.category-stats');
        if (statsEl) {
            statsEl.textContent = `${granted}/${toggles.length}`;
        }

        // Update category toggle checkbox state
        if (categoryToggle && toggles.length > 0) {
            if (granted === 0) {
                // All unchecked
                categoryToggle.checked = false;
                categoryToggle.indeterminate = false;
            } else if (granted === toggles.length) {
                // All checked
                categoryToggle.checked = true;
                categoryToggle.indeterminate = false;
            } else {
                // Some checked (indeterminate state)
                categoryToggle.checked = false;
                categoryToggle.indeterminate = true;
            }
        }
    });
}

function updateChangeState() {
    hasUnsavedChanges = permissionChanges.length > 0;

    if (saveBtn) {
        saveBtn.disabled = !hasUnsavedChanges;
    }
    if (resetBtn) {
        resetBtn.disabled = !hasUnsavedChanges;
    }

    if (saveIndicator) {
        if (hasUnsavedChanges) {
            saveIndicator.style.display = 'block';
            const counter = document.getElementById('tls-change-counter');
            if (counter) {
                counter.textContent = permissionChanges.length;
            }
        } else {
            saveIndicator.style.display = 'none';
        }
    }
}

function showPermissions() {
    document.getElementById('permissionCards').style.display = '';
    document.getElementById('permissionCards').classList.add('show');
    document.getElementById('bulkControls').style.display = '';
    document.getElementById('searchBox').disabled = false;
    document.getElementById('noUserMessage').style.display = 'none';
}

function hidePermissions() {
    document.getElementById('permissionCards').style.display = 'none';
    document.getElementById('permissionCards').classList.remove('show');
    document.getElementById('bulkControls').style.display = 'none';
    document.getElementById('searchBox').disabled = true;
    document.getElementById('searchBox').value = '';
    document.getElementById('noUserMessage').style.display = 'block';

    currentUserId = '';
    userPermissions = {};
    originalPermissions = {};
    permissionChanges = [];
    hasUnsavedChanges = false;

    updateSummaryStats();
    updateChangeState();
}

function showLoading(show) {
    const indicator = document.getElementById('loadingIndicator');
    const cards = document.getElementById('permissionCards');

    if (show) {
        indicator.style.display = 'block';
        cards.classList.add('loading');
    } else {
        indicator.style.display = 'none';
        cards.classList.remove('loading');
    }
}
</script>
<?= $this->endSection() ?>
