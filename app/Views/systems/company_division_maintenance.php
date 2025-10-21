<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="tls-page-header">
        <h1 class="tls-page-title">
            <i class="bi bi-building me-2"></i>Company & Division Maintenance
        </h1>
        <div class="tls-top-actions">
            <button type="button" class="btn tls-btn-primary" id="btn-new-company">
                <i class="bi bi-plus-circle me-1"></i>New Company
            </button>
        </div>
    </div>

    <!-- Companies Grid (Top Section) -->
    <div class="tls-form-card mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="bi bi-list me-2"></i>Companies
            </h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm table-hover mb-0" id="companies-grid">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;">Select</th>
                        <th>Company Name</th>
                        <th>Short Name</th>
                        <th>SCAC</th>
                        <th>Phone</th>
                        <th style="width: 80px;">Active</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($companies)): ?>
                        <?php foreach ($companies as $index => $company): ?>
                            <tr class="company-row" data-company-id="<?= $company['CompanyID'] ?>">
                                <td class="text-center">
                                    <input type="radio" name="selected_company" value="<?= $company['CompanyID'] ?>"
                                           <?= $index === 0 ? 'checked' : '' ?>>
                                </td>
                                <td><?= esc($company['CompanyName']) ?></td>
                                <td><?= esc($company['ShortName'] ?? '') ?></td>
                                <td><?= esc($company['SCAC'] ?? '') ?></td>
                                <td><?= esc($company['MainPhone'] ?? '') ?></td>
                                <td class="text-center">
                                    <?= $company['Active'] ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-x-circle-fill text-danger"></i>' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">No companies found. Click "New Company" to create one.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Main Content: Company Form (Left) + Divisions/Deps/Teams (Right) -->
    <div class="row">
        <!-- LEFT COLUMN: Company Form -->
        <div class="col-md-5">
            <form id="company-form">
                <input type="hidden" name="CompanyID" id="company-id">

                <!-- Basic Information Section -->
                <div class="tls-form-card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-info-circle me-2"></i>Basic Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="company_id_display" class="form-label">Company ID</label>
                                <input type="text" class="form-control" id="company_id_display" readonly>
                            </div>
                            <div class="col-md-9">
                                <label for="company_name" class="form-label">Company Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="CompanyName" id="company_name" required maxlength="50">
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <label for="short_name" class="form-label">Short Name</label>
                                <input type="text" class="form-control" name="ShortName" id="short_name" maxlength="20">
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mt-4">
                                    <input type="checkbox" class="form-check-input" name="Active" id="active" value="1">
                                    <label class="form-check-label" for="active">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mailing Address Section (Collapsible) -->
                <div class="accordion mb-3" id="company-accordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#mailing-collapse">
                                <i class="bi bi-envelope me-2"></i>Mailing Address
                            </button>
                        </h2>
                        <div id="mailing-collapse" class="accordion-collapse collapse" data-bs-parent="#company-accordion">
                            <div class="accordion-body">
                                <div class="mb-2">
                                    <label for="mailing_address" class="form-label">Address</label>
                                    <input type="text" class="form-control" name="MailingAddress" id="mailing_address" maxlength="50">
                                </div>
                                <div class="row">
                                    <div class="col-md-5 mb-2">
                                        <label for="mailing_city" class="form-label">City</label>
                                        <input type="text" class="form-control" name="MailingCity" id="mailing_city" maxlength="50">
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label for="mailing_state" class="form-label">State</label>
                                        <input type="text" class="form-control" name="MailingState" id="mailing_state" maxlength="2">
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label for="mailing_zip" class="form-label">ZIP</label>
                                        <input type="text" class="form-control" name="MailingZip" id="mailing_zip" maxlength="50">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Shipping Address Section -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#shipping-collapse">
                                <i class="bi bi-truck me-2"></i>Shipping Address
                            </button>
                        </h2>
                        <div id="shipping-collapse" class="accordion-collapse collapse" data-bs-parent="#company-accordion">
                            <div class="accordion-body">
                                <div class="mb-2">
                                    <label for="shipping_address" class="form-label">Address</label>
                                    <input type="text" class="form-control" name="ShippingAddress" id="shipping_address" maxlength="50">
                                </div>
                                <div class="row">
                                    <div class="col-md-5 mb-2">
                                        <label for="shipping_city" class="form-label">City</label>
                                        <input type="text" class="form-control" name="ShippingCity" id="shipping_city" maxlength="50">
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label for="shipping_state" class="form-label">State</label>
                                        <input type="text" class="form-control" name="ShippingState" id="shipping_state" maxlength="2">
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label for="shipping_zip" class="form-label">ZIP</label>
                                        <input type="text" class="form-control" name="ShippingZip" id="shipping_zip" maxlength="10">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information Section -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#contact-collapse">
                                <i class="bi bi-telephone me-2"></i>Contact Information
                            </button>
                        </h2>
                        <div id="contact-collapse" class="accordion-collapse collapse" data-bs-parent="#company-accordion">
                            <div class="accordion-body">
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <label for="main_phone" class="form-label">Main Phone</label>
                                        <input type="text" class="form-control" name="MainPhone" id="main_phone" maxlength="10">
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label for="main_fax" class="form-label">Main Fax</label>
                                        <input type="text" class="form-control" name="MainFax" id="main_fax" maxlength="10">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Identifiers Section -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#identifiers-collapse">
                                <i class="bi bi-card-list me-2"></i>Identifiers
                            </button>
                        </h2>
                        <div id="identifiers-collapse" class="accordion-collapse collapse" data-bs-parent="#company-accordion">
                            <div class="accordion-body">
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <label for="scac" class="form-label">SCAC</label>
                                        <input type="text" class="form-control" name="SCAC" id="scac" maxlength="4">
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label for="duns" class="form-label">DUNS</label>
                                        <input type="text" class="form-control" name="DUNS" id="duns" maxlength="50">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-2">
                                        <label for="icc" class="form-label">ICC</label>
                                        <input type="text" class="form-control" name="ICC" id="icc" maxlength="50">
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label for="dot" class="form-label">DOT</label>
                                        <input type="text" class="form-control" name="DOT" id="dot" maxlength="50">
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label for="fid" class="form-label">FID</label>
                                        <input type="text" class="form-control" name="FID" id="fid" maxlength="50">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-grid gap-2 mb-3">
                    <button type="submit" class="btn tls-btn-primary">
                        <i class="bi bi-save me-1"></i>Save Company
                    </button>
                    <button type="button" class="btn tls-btn-secondary" id="btn-reset-company">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                    </button>
                </div>
            </form>
        </div>

        <!-- RIGHT COLUMN: Divisions, Departments, Teams -->
        <div class="col-md-7">
            <!-- Divisions Section -->
            <div class="tls-form-card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-diagram-3 me-2"></i>Divisions
                    </h5>
                    <button type="button" class="btn btn-sm tls-btn-primary" id="btn-add-division">
                        <i class="bi bi-plus-circle me-1"></i>Add Division
                    </button>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0" id="divisions-grid">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60px;">Select</th>
                                <th>Division Name</th>
                                <th>City</th>
                                <th>State</th>
                                <th style="width: 80px;">Active</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5" class="text-center text-muted">Select a company to view divisions</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Division Details (when division selected) -->
            <div id="division-details" class="tls-form-card mb-3" style="display: none;">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-pencil me-2"></i>Division Details
                    </h5>
                </div>
                <div class="card-body">
                    <form id="division-form">
                        <input type="hidden" name="CompanyID" id="division-company-id">
                        <input type="hidden" name="DivisionID" id="division-id">

                        <div class="row">
                            <div class="col-md-8 mb-2">
                                <label for="division_name" class="form-label">Division Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="Name" id="division_name" required maxlength="50">
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="form-check mt-4">
                                    <input type="checkbox" class="form-check-input" name="Active" id="division_active" value="1">
                                    <label class="form-check-label" for="division_active">Active</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label for="division_address" class="form-label">Address</label>
                            <input type="text" class="form-control" name="Address" id="division_address" maxlength="50">
                        </div>

                        <div class="row">
                            <div class="col-md-5 mb-2">
                                <label for="division_city" class="form-label">City</label>
                                <input type="text" class="form-control" name="City" id="division_city" maxlength="50">
                            </div>
                            <div class="col-md-3 mb-2">
                                <label for="division_state" class="form-label">State</label>
                                <input type="text" class="form-control" name="State" id="division_state" maxlength="2">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label for="division_zip" class="form-label">ZIP</label>
                                <input type="text" class="form-control" name="Zip" id="division_zip" maxlength="10">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label for="division_phone" class="form-label">Phone</label>
                                <input type="text" class="form-control" name="Phone" id="division_phone" maxlength="10">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="division_fax" class="form-label">Fax</label>
                                <input type="text" class="form-control" name="Fax" id="division_fax" maxlength="10">
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-3">
                            <button type="submit" class="btn tls-btn-primary">
                                <i class="bi bi-save me-1"></i>Save Division
                            </button>
                            <button type="button" class="btn tls-btn-secondary" id="btn-cancel-division">
                                <i class="bi bi-x-circle me-1"></i>Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Departments & Teams Tabs -->
            <div id="division-children" class="tls-form-card" style="display: none;">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="departments-tab" data-bs-toggle="tab" data-bs-target="#departments-content" type="button">
                                <i class="bi bi-building me-1"></i>Departments
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="teams-tab" data-bs-toggle="tab" data-bs-target="#teams-content" type="button">
                                <i class="bi bi-people me-1"></i>Teams
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <!-- Departments Tab -->
                        <div class="tab-pane fade show active" id="departments-content">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Departments</h6>
                                <button type="button" class="btn btn-sm tls-btn-primary" id="btn-add-department">
                                    <i class="bi bi-plus-circle me-1"></i>Add Department
                                </button>
                            </div>

                            <table class="table table-sm table-hover" id="departments-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Description</th>
                                        <th>Active</th>
                                        <th style="width: 100px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="departments-tbody">
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No departments found</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Teams Tab -->
                        <div class="tab-pane fade" id="teams-content">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Teams</h6>
                                <button type="button" class="btn btn-sm tls-btn-primary" id="btn-add-team">
                                    <i class="bi bi-plus-circle me-1"></i>Add Team
                                </button>
                            </div>

                            <table class="table table-sm table-hover" id="teams-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Team Key</th>
                                        <th>Team Name</th>
                                        <th>Phone</th>
                                        <th>Fax</th>
                                        <th style="width: 100px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="teams-tbody">
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No teams found</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Department Modal -->
<div class="modal fade" id="departmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="departmentModalLabel">Add Department</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="department-modal-form">
                    <input type="hidden" id="dept-company-id" name="CompanyID">
                    <input type="hidden" id="dept-division-id" name="DivisionID">
                    <input type="hidden" id="dept-department-id" name="DepartmentID">

                    <div class="mb-3">
                        <label for="dept-description" class="form-label">Description <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="dept-description" name="Description" required maxlength="50">
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="dept-active" name="Active" value="1" checked>
                        <label class="form-check-label" for="dept-active">Active</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn tls-btn-primary" onclick="saveDepartment()">Save Department</button>
            </div>
        </div>
    </div>
</div>

<!-- Team Modal -->
<div class="modal fade" id="teamModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="teamModalLabel">Add Team</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="team-modal-form">
                    <input type="hidden" id="team-key" name="TeamKey">
                    <input type="hidden" id="team-company-id" name="CompanyID">
                    <input type="hidden" id="team-division-id" name="DivisionID">

                    <div class="mb-3">
                        <label for="team-name" class="form-label">Team Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="team-name" name="TeamName" required maxlength="30">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="team-phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="team-phone" name="TeamPhone" maxlength="10">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="team-fax" class="form-label">Fax</label>
                            <input type="text" class="form-control" id="team-fax" name="TeamFax" maxlength="10">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="team-report-group" class="form-label">Report Group</label>
                        <input type="text" class="form-control" id="team-report-group" name="ReportGroup" maxlength="30">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="team-agent-key" class="form-label">Agent Key</label>
                            <input type="number" class="form-control" id="team-agent-key" name="AgentKey">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="team-agent-pay" class="form-label">Agent Pay %</label>
                            <input type="number" step="0.01" class="form-control" id="team-agent-pay" name="AgentPay">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn tls-btn-primary" onclick="saveTeam()">Save Team</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentCompanyID = null;
let currentDivisionID = null;

document.addEventListener('DOMContentLoaded', function() {
    // Load first company if exists
    const firstCompanyRadio = document.querySelector('input[name="selected_company"]:checked');
    if (firstCompanyRadio) {
        currentCompanyID = parseInt(firstCompanyRadio.value);
        loadCompany(currentCompanyID);
    }

    // Company selection from grid
    document.querySelectorAll('.company-row').forEach(row => {
        row.addEventListener('click', function(e) {
            if (e.target.type !== 'radio') {
                const radio = this.querySelector('input[type="radio"]');
                radio.checked = true;
            }
            const companyID = parseInt(this.dataset.companyId);
            currentCompanyID = companyID;
            loadCompany(companyID);
        });
    });

    // New Company button
    document.getElementById('btn-new-company').addEventListener('click', createNewCompany);

    // Company form submit
    document.getElementById('company-form').addEventListener('submit', function(e) {
        e.preventDefault();
        saveCompany();
    });

    // Reset company button
    document.getElementById('btn-reset-company').addEventListener('click', function() {
        if (currentCompanyID) {
            loadCompany(currentCompanyID);
        }
    });

    // Add Division button
    document.getElementById('btn-add-division').addEventListener('click', createNewDivision);

    // Division form submit
    document.getElementById('division-form').addEventListener('submit', function(e) {
        e.preventDefault();
        saveDivision();
    });

    // Cancel division edit
    document.getElementById('btn-cancel-division').addEventListener('click', function() {
        document.getElementById('division-details').style.display = 'none';
        currentDivisionID = null;
    });

    // Department and Team buttons
    document.getElementById('btn-add-department').addEventListener('click', addDepartment);
    document.getElementById('btn-add-team').addEventListener('click', addTeam);
});

function loadCompany(companyID) {
    fetch(`<?= base_url('systems/company-division-maintenance/load-company') ?>/${companyID}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateCompanyForm(data.company);
                loadDivisions(companyID);
            } else {
                alert('Error loading company: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
}

function populateCompanyForm(company) {
    document.getElementById('company-id').value = company.CompanyID || '';
    document.getElementById('company_id_display').value = company.CompanyID || '';
    document.getElementById('company_name').value = company.CompanyName || '';
    document.getElementById('short_name').value = company.ShortName || '';
    document.getElementById('active').checked = company.Active == 1;

    // Mailing Address
    document.getElementById('mailing_address').value = company.MailingAddress || '';
    document.getElementById('mailing_city').value = company.MailingCity || '';
    document.getElementById('mailing_state').value = company.MailingState || '';
    document.getElementById('mailing_zip').value = company.MailingZip || '';

    // Shipping Address
    document.getElementById('shipping_address').value = company.ShippingAddress || '';
    document.getElementById('shipping_city').value = company.ShippingCity || '';
    document.getElementById('shipping_state').value = company.ShippingState || '';
    document.getElementById('shipping_zip').value = company.ShippingZip || '';

    // Contact Info
    document.getElementById('main_phone').value = company.MainPhone || '';
    document.getElementById('main_fax').value = company.MainFax || '';

    // Identifiers
    document.getElementById('scac').value = company.SCAC || '';
    document.getElementById('duns').value = company.DUNS || '';
    document.getElementById('icc').value = company.ICC || '';
    document.getElementById('dot').value = company.DOT || '';
    document.getElementById('fid').value = company.FID || '';
}

function saveCompany() {
    const formData = new FormData(document.getElementById('company-form'));

    fetch('<?= base_url('systems/company-division-maintenance/save-company') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Company saved successfully!');
            // Reload page to refresh companies grid
            location.reload();
        } else {
            alert('Error saving company: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}

function createNewCompany() {
    if (!confirm('Are you sure you want to create a new company? This will reserve a Company ID in the database.')) {
        return;
    }

    fetch('<?= base_url('systems/company-division-maintenance/create-company') ?>', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('New company created successfully!');
            location.reload();
        } else {
            alert('Error creating company: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}

function loadDivisions(companyID) {
    fetch(`<?= base_url('systems/company-division-maintenance/divisions') ?>/${companyID}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayDivisions(data.divisions);
            } else {
                console.error('Error loading divisions:', data.message);
            }
        })
        .catch(error => console.error('Error:', error));
}

function displayDivisions(divisions) {
    const tbody = document.querySelector('#divisions-grid tbody');
    tbody.innerHTML = '';

    if (divisions.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No divisions found. Click "Add Division" to create one.</td></tr>';
        return;
    }

    divisions.forEach((division, index) => {
        const tr = document.createElement('tr');
        tr.className = 'division-row';
        tr.dataset.divisionId = division.DivisionID;
        tr.innerHTML = `
            <td class="text-center">
                <input type="radio" name="selected_division" value="${division.DivisionID}" ${index === 0 ? 'checked' : ''}>
            </td>
            <td>${division.Name || ''}</td>
            <td>${division.City || ''}</td>
            <td>${division.State || ''}</td>
            <td class="text-center">
                ${division.Active ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-x-circle-fill text-danger"></i>'}
            </td>
        `;
        tr.addEventListener('click', function(e) {
            if (e.target.type !== 'radio') {
                const radio = this.querySelector('input[type="radio"]');
                radio.checked = true;
            }
            loadDivision(currentCompanyID, parseInt(this.dataset.divisionId));
        });
        tbody.appendChild(tr);
    });
}

function loadDivision(companyID, divisionID) {
    currentDivisionID = divisionID;

    fetch(`<?= base_url('systems/company-division-maintenance/division') ?>/${companyID}/${divisionID}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateDivisionForm(data.division);
                document.getElementById('division-details').style.display = 'block';
                document.getElementById('division-children').style.display = 'block';

                // Load departments and teams for this division
                loadDepartments(companyID, divisionID);
                loadTeams(companyID, divisionID);
            } else {
                alert('Error loading division: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
}

function populateDivisionForm(division) {
    document.getElementById('division-company-id').value = division.CompanyID || '';
    document.getElementById('division-id').value = division.DivisionID || '';
    document.getElementById('division_name').value = division.Name || '';
    document.getElementById('division_address').value = division.Address || '';
    document.getElementById('division_city').value = division.City || '';
    document.getElementById('division_state').value = division.State || '';
    document.getElementById('division_zip').value = division.Zip || '';
    document.getElementById('division_phone').value = division.Phone || '';
    document.getElementById('division_fax').value = division.Fax || '';
    document.getElementById('division_active').checked = division.Active == 1;
}

function saveDivision() {
    const formData = new FormData(document.getElementById('division-form'));

    fetch('<?= base_url('systems/company-division-maintenance/save-division') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Division saved successfully!');
            loadDivisions(currentCompanyID);
        } else {
            alert('Error saving division: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}

function createNewDivision() {
    if (!currentCompanyID) {
        alert('Please select a company first.');
        return;
    }

    if (!confirm('Are you sure you want to create a new division?')) {
        return;
    }

    const formData = new FormData();
    formData.append('CompanyID', currentCompanyID);

    fetch('<?= base_url('systems/company-division-maintenance/create-division') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('New division created successfully!');
            loadDivisions(currentCompanyID);
        } else {
            alert('Error creating division: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}

// ====================
// Department Functions
// ====================

function loadDepartments(companyID, divisionID) {
    fetch(`<?= base_url('systems/company-division-maintenance/departments') ?>/${companyID}/${divisionID}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayDepartments(data.departments);
            } else {
                console.error('Error loading departments:', data.message);
            }
        })
        .catch(error => console.error('Error:', error));
}

function displayDepartments(departments) {
    const tbody = document.getElementById('departments-tbody');
    tbody.innerHTML = '';

    if (!departments || departments.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No departments found</td></tr>';
        return;
    }

    departments.forEach(dept => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${dept.DepartmentID}</td>
            <td>${dept.Description || ''}</td>
            <td class="text-center">
                ${dept.Active ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-x-circle-fill text-danger"></i>'}
            </td>
            <td>
                <button class="btn btn-sm btn-outline-primary" onclick="editDepartment(${dept.CompanyID}, ${dept.DivisionID}, ${dept.DepartmentID})">
                    <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteDepartment(${dept.CompanyID}, ${dept.DivisionID}, ${dept.DepartmentID})">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function showDepartmentModal(isEdit = false) {
    document.getElementById('departmentModalLabel').textContent = isEdit ? 'Edit Department' : 'Add Department';
    const modal = new bootstrap.Modal(document.getElementById('departmentModal'));
    modal.show();
}

function addDepartment() {
    if (!currentCompanyID || !currentDivisionID) {
        alert('Please select a division first.');
        return;
    }

    // Reset form
    document.getElementById('department-modal-form').reset();
    document.getElementById('dept-company-id').value = currentCompanyID;
    document.getElementById('dept-division-id').value = currentDivisionID;
    document.getElementById('dept-department-id').value = '0';
    document.getElementById('dept-active').checked = true;

    showDepartmentModal(false);
}

function editDepartment(companyID, divisionID, departmentID) {
    fetch(`<?= base_url('systems/company-division-maintenance/department') ?>/${companyID}/${divisionID}/${departmentID}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const dept = data.department;
                document.getElementById('dept-company-id').value = dept.CompanyID;
                document.getElementById('dept-division-id').value = dept.DivisionID;
                document.getElementById('dept-department-id').value = dept.DepartmentID;
                document.getElementById('dept-description').value = dept.Description || '';
                document.getElementById('dept-active').checked = dept.Active == 1;

                showDepartmentModal(true);
            } else {
                alert('Error loading department: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
}

function saveDepartment() {
    const formData = new FormData(document.getElementById('department-modal-form'));

    fetch('<?= base_url('systems/company-division-maintenance/save-department') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Department saved successfully!');
            bootstrap.Modal.getInstance(document.getElementById('departmentModal')).hide();
            loadDepartments(currentCompanyID, currentDivisionID);
        } else {
            alert('Error saving department: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}

function deleteDepartment(companyID, divisionID, departmentID) {
    if (!confirm('Are you sure you want to delete this department?')) {
        return;
    }

    const formData = new FormData();
    formData.append('CompanyID', companyID);
    formData.append('DivisionID', divisionID);
    formData.append('DepartmentID', departmentID);

    fetch('<?= base_url('systems/company-division-maintenance/delete-department') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Department deleted successfully!');
            loadDepartments(currentCompanyID, currentDivisionID);
        } else {
            alert('Error deleting department: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}

// ====================
// Team Functions
// ====================

function loadTeams(companyID, divisionID) {
    fetch(`<?= base_url('systems/company-division-maintenance/teams') ?>/${companyID}/${divisionID}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayTeams(data.teams);
            } else {
                console.error('Error loading teams:', data.message);
            }
        })
        .catch(error => console.error('Error:', error));
}

function displayTeams(teams) {
    const tbody = document.getElementById('teams-tbody');
    tbody.innerHTML = '';

    if (!teams || teams.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No teams found</td></tr>';
        return;
    }

    teams.forEach(team => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${team.TeamKey}</td>
            <td>${team.TeamName || ''}</td>
            <td>${team.TeamPhone || ''}</td>
            <td>${team.TeamFax || ''}</td>
            <td>
                <button class="btn btn-sm btn-outline-primary" onclick="editTeam(${team.TeamKey})">
                    <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteTeam(${team.TeamKey})">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function showTeamModal(isEdit = false) {
    document.getElementById('teamModalLabel').textContent = isEdit ? 'Edit Team' : 'Add Team';
    const modal = new bootstrap.Modal(document.getElementById('teamModal'));
    modal.show();
}

function addTeam() {
    if (!currentCompanyID || !currentDivisionID) {
        alert('Please select a division first.');
        return;
    }

    // Reset form
    document.getElementById('team-modal-form').reset();
    document.getElementById('team-key').value = '0';
    document.getElementById('team-company-id').value = currentCompanyID;
    document.getElementById('team-division-id').value = currentDivisionID;

    showTeamModal(false);
}

function editTeam(teamKey) {
    fetch(`<?= base_url('systems/company-division-maintenance/team') ?>/${teamKey}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const team = data.team;
                document.getElementById('team-key').value = team.TeamKey;
                document.getElementById('team-company-id').value = team.CompanyID || currentCompanyID;
                document.getElementById('team-division-id').value = team.DivisionID || currentDivisionID;
                document.getElementById('team-name').value = team.TeamName || '';
                document.getElementById('team-phone').value = team.TeamPhone || '';
                document.getElementById('team-fax').value = team.TeamFax || '';
                document.getElementById('team-report-group').value = team.ReportGroup || '';
                document.getElementById('team-agent-key').value = team.AgentKey || '';
                document.getElementById('team-agent-pay').value = team.AgentPay || '';

                showTeamModal(true);
            } else {
                alert('Error loading team: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
}

function saveTeam() {
    const formData = new FormData(document.getElementById('team-modal-form'));

    fetch('<?= base_url('systems/company-division-maintenance/save-team') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Team saved successfully!');
            bootstrap.Modal.getInstance(document.getElementById('teamModal')).hide();
            loadTeams(currentCompanyID, currentDivisionID);
        } else {
            alert('Error saving team: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}

function deleteTeam(teamKey) {
    if (!confirm('Are you sure you want to delete this team? This will set TeamKey to NULL in related users and units.')) {
        return;
    }

    const formData = new FormData();
    formData.append('TeamKey', teamKey);

    fetch('<?= base_url('systems/company-division-maintenance/delete-team') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Team deleted successfully!');
            loadTeams(currentCompanyID, currentDivisionID);
        } else {
            alert('Error deleting team: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>

<?= $this->endSection() ?>
