<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="tls-page-header">
        <h1 class="tls-page-title">
            <i class="bi bi-person-badge me-2"></i>Driver Maintenance
        </h1>
        <div class="tls-top-actions d-flex gap-2">
            <button type="button" class="btn tls-btn-primary" id="new-driver-btn" onclick="newDriver()">
                <i class="bi bi-plus-circle me-1"></i>New Driver
            </button>
            <?php if ($driver && !$isNewDriver): ?>
            <button type="submit" form="driver-form" class="btn tls-btn-primary">
                <i class="bi bi-check-circle me-1"></i>Save Driver
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
        <button type="submit" form="driver-form" class="btn btn-sm tls-btn-primary ms-3">Save Now</button>
    </div>

    <!-- Search Section -->
    <div class="tls-form-card mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="bi bi-search me-2"></i>Search Driver
            </h5>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= base_url('safety/driver-maintenance/search') ?>" class="row g-3">
                <?= csrf_field() ?>
                <div class="col-md-6">
                    <label for="driver_key" class="form-label">Driver Key or Name</label>
                    <input type="text"
                           class="form-control tls-autocomplete"
                           id="driver_key"
                           name="driver_key"
                           placeholder="Enter Driver Key or search by name..."
                           autocomplete="off"
                           data-api-type="drivers"
                           data-include-inactive-id="include_inactive">
                </div>
                <div class="col-md-3">
                    <label class="form-label d-block">&nbsp;</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="includeInactive" name="include_inactive">
                        <label class="form-check-label" for="includeInactive">
                            Include Inactive Drivers
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

    <?php if ($driver): ?>
    <!-- Driver Details Form -->
    <form id="driver-form" method="POST" action="<?= base_url('safety/driver-maintenance/save') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="driver_key" id="form_driver_key" value="<?= esc($driver['DriverKey'] ?? 0) ?>">

        <div class="row">
            <!-- Left Column: Driver Information -->
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
                                <label for="driver_id" class="form-label">Driver ID</label>
                                <input type="text" class="form-control tls-track-changes" id="driver_id" name="driver_id"
                                       value="<?= esc($driver['DriverID'] ?? '') ?>" maxlength="9">
                            </div>
                            <div class="col-md-8">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control tls-track-changes" id="email" name="email"
                                       value="<?= esc($driver['Email'] ?? '') ?>" maxlength="50">
                            </div>
                            <div class="col-md-4">
                                <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control tls-track-changes" id="first_name" name="first_name"
                                       value="<?= esc($driver['FirstName'] ?? '') ?>" maxlength="15" required>
                            </div>
                            <div class="col-md-4">
                                <label for="middle_name" class="form-label">Middle Name</label>
                                <input type="text" class="form-control tls-track-changes" id="middle_name" name="middle_name"
                                       value="<?= esc($driver['MiddleName'] ?? '') ?>" maxlength="15">
                            </div>
                            <div class="col-md-4">
                                <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control tls-track-changes" id="last_name" name="last_name"
                                       value="<?= esc($driver['LastName'] ?? '') ?>" maxlength="15" required>
                            </div>
                            <div class="col-md-4">
                                <label for="birth_date" class="form-label">Birth Date</label>
                                <input type="date" class="form-control tls-track-changes" id="birth_date" name="birth_date"
                                       value="<?= !empty($driver['BirthDate']) && $driver['BirthDate'] != '1899-12-30 00:00:00.000' ? date('Y-m-d', strtotime($driver['BirthDate'])) : '' ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control tls-track-changes" id="start_date" name="start_date"
                                       value="<?= !empty($driver['StartDate']) && $driver['StartDate'] != '1899-12-30 00:00:00.000' ? date('Y-m-d', strtotime($driver['StartDate'])) : '' ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control tls-track-changes" id="end_date" name="end_date"
                                       value="<?= !empty($driver['EndDate']) && $driver['EndDate'] != '1899-12-30 00:00:00.000' ? date('Y-m-d', strtotime($driver['EndDate'])) : '' ?>">
                                <small class="form-text text-muted">Leave empty for active drivers</small>
                            </div>
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input tls-track-changes" type="checkbox" id="active" name="active"
                                           <?= !empty($driver['Active']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="active">
                                        Active (auto-set by End Date)
                                    </label>
                                    <small class="form-text text-muted d-block">Active = checked requires empty End Date. Inactive = unchecked requires End Date.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- License & Medical Section -->
                <div class="tls-form-card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-card-checklist me-2"></i>License & Medical
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="license_number" class="form-label">License Number</label>
                                <input type="text" class="form-control tls-track-changes" id="license_number" name="license_number"
                                       value="<?= esc($driver['LicenseNumber'] ?? '') ?>" maxlength="15">
                            </div>
                            <div class="col-md-3">
                                <label for="license_state" class="form-label">State</label>
                                <input type="text" class="form-control tls-track-changes" id="license_state" name="license_state"
                                       value="<?= esc($driver['LicenseState'] ?? '') ?>" maxlength="2" style="text-transform: uppercase;">
                            </div>
                            <div class="col-md-3">
                                <label for="license_expires" class="form-label">Expires</label>
                                <input type="date" class="form-control tls-track-changes" id="license_expires" name="license_expires"
                                       value="<?= !empty($driver['LicenseExpires']) && $driver['LicenseExpires'] != '1899-12-30 00:00:00.000' ? date('Y-m-d', strtotime($driver['LicenseExpires'])) : '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="physical_date" class="form-label">Physical Date</label>
                                <input type="date" class="form-control tls-track-changes" id="physical_date" name="physical_date"
                                       value="<?= !empty($driver['PhysicalDate']) && $driver['PhysicalDate'] != '1899-12-30 00:00:00.000' ? date('Y-m-d', strtotime($driver['PhysicalDate'])) : '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="physical_expires" class="form-label">Physical Expires</label>
                                <input type="date" class="form-control tls-track-changes" id="physical_expires" name="physical_expires"
                                       value="<?= !empty($driver['PhysicalExpires']) && $driver['PhysicalExpires'] != '1899-12-30 00:00:00.000' ? date('Y-m-d', strtotime($driver['PhysicalExpires'])) : '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="mvr_due" class="form-label">MVR Due Date</label>
                                <input type="date" class="form-control tls-track-changes" id="mvr_due" name="mvr_due"
                                       value="<?= !empty($driver['MVRDue']) && $driver['MVRDue'] != '1899-12-30 00:00:00.000' ? date('Y-m-d', strtotime($driver['MVRDue'])) : '' ?>">
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mt-4">
                                    <input class="form-check-input tls-track-changes" type="checkbox" id="medical_verification" name="medical_verification"
                                           <?= !empty($driver['MedicalVerification']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="medical_verification">
                                        Medical Verification
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Driver Characteristics Section -->
                <div class="tls-form-card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-tags me-2"></i>Driver Characteristics
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="driver_type" class="form-label">Driver Type</label>
                                <select class="form-select tls-track-changes" id="driver_type" name="driver_type">
                                    <option value="F" <?= ($driver['DriverType'] ?? 'F') == 'F' ? 'selected' : '' ?>>Full-time</option>
                                    <option value="P" <?= ($driver['DriverType'] ?? '') == 'P' ? 'selected' : '' ?>>Part-time</option>
                                    <option value="C" <?= ($driver['DriverType'] ?? '') == 'C' ? 'selected' : '' ?>>Contractor</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="driver_spec" class="form-label">Driver Spec</label>
                                <select class="form-select tls-track-changes" id="driver_spec" name="driver_spec">
                                    <option value="OTH" <?= ($driver['DriverSpec'] ?? 'OTH') == 'OTH' ? 'selected' : '' ?>>Other</option>
                                    <option value="HAZ" <?= ($driver['DriverSpec'] ?? '') == 'HAZ' ? 'selected' : '' ?>>Hazmat</option>
                                    <option value="TNK" <?= ($driver['DriverSpec'] ?? '') == 'TNK' ? 'selected' : '' ?>>Tanker</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="company_id" class="form-label">Company ID</label>
                                <input type="number" class="form-control tls-track-changes" id="company_id" name="company_id"
                                       value="<?= esc($driver['CompanyID'] ?? 3) ?>">
                            </div>
                            <div class="col-md-12">
                                <label for="favorite_route" class="form-label">Favorite Route</label>
                                <input type="text" class="form-control tls-track-changes" id="favorite_route" name="favorite_route"
                                       value="<?= esc($driver['FavoriteRoute'] ?? '') ?>" maxlength="50">
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input tls-track-changes" type="checkbox" id="twic" name="twic"
                                           <?= !empty($driver['TWIC']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="twic">
                                        TWIC Certified
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input tls-track-changes" type="checkbox" id="coil_cert" name="coil_cert"
                                           <?= !empty($driver['CoilCert']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="coil_cert">
                                        Coil Certified
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pay Information Section -->
                <div class="tls-form-card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-currency-dollar me-2"></i>Pay Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="pay_type" class="form-label">Pay Type</label>
                                <select class="form-select tls-track-changes" id="pay_type" name="pay_type">
                                    <option value="P" <?= ($driver['PayType'] ?? 'P') == 'P' ? 'selected' : '' ?>>Percentage</option>
                                    <option value="M" <?= ($driver['PayType'] ?? '') == 'M' ? 'selected' : '' ?>>Mileage</option>
                                    <option value="H" <?= ($driver['PayType'] ?? '') == 'H' ? 'selected' : '' ?>>Hourly</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="company_loaded_pay" class="form-label">Loaded Pay</label>
                                <input type="number" step="0.001" class="form-control tls-track-changes" id="company_loaded_pay" name="company_loaded_pay"
                                       value="<?= esc($driver['CompanyLoadedPay'] ?? '0.000') ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="company_empty_pay" class="form-label">Empty Pay</label>
                                <input type="number" step="0.001" class="form-control tls-track-changes" id="company_empty_pay" name="company_empty_pay"
                                       value="<?= esc($driver['CompanyEmptyPay'] ?? '0.000') ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="company_tarp_pay" class="form-label">Tarp Pay</label>
                                <input type="number" step="0.01" class="form-control tls-track-changes" id="company_tarp_pay" name="company_tarp_pay"
                                       value="<?= esc($driver['CompanyTarpPay'] ?? '0.00') ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="company_stop_pay" class="form-label">Stop Pay</label>
                                <input type="number" step="0.01" class="form-control tls-track-changes" id="company_stop_pay" name="company_stop_pay"
                                       value="<?= esc($driver['CompanyStopPay'] ?? '0.00') ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="weekly_cash" class="form-label">Weekly Cash</label>
                                <input type="number" step="0.01" class="form-control tls-track-changes" id="weekly_cash" name="weekly_cash"
                                       value="<?= esc($driver['WeeklyCash'] ?? '0.00') ?>">
                            </div>
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input tls-track-changes" type="checkbox" id="card_exception" name="card_exception"
                                           <?= !empty($driver['CardException']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="card_exception">
                                        Card Exception
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Company Driver Information Section -->
                <div class="tls-form-card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-building me-2"></i>Company Driver Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input tls-track-changes" type="checkbox" id="company_driver" name="company_driver"
                                           <?= !empty($driver['CompanyDriver']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="company_driver">
                                        Company Driver
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input tls-track-changes" type="checkbox" id="eobr" name="eobr"
                                           <?= !empty($driver['EOBR']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="eobr">
                                        EOBR (Electronic Logging)
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="eobr_start" class="form-label">EOBR Start Date</label>
                                <input type="date" class="form-control tls-track-changes" id="eobr_start" name="eobr_start"
                                       value="<?= !empty($driver['EOBRStart']) && $driver['EOBRStart'] != '1899-12-30 00:00:00.000' ? date('Y-m-d', strtotime($driver['EOBRStart'])) : '' ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="arcnc" class="form-label">AR CNC Date</label>
                                <input type="date" class="form-control tls-track-changes" id="arcnc" name="arcnc"
                                       value="<?= !empty($driver['ARCNC']) && $driver['ARCNC'] != '1899-12-30 00:00:00.000' ? date('Y-m-d', strtotime($driver['ARCNC'])) : '' ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="txcnc" class="form-label">TX CNC Date</label>
                                <input type="date" class="form-control tls-track-changes" id="txcnc" name="txcnc"
                                       value="<?= !empty($driver['TXCNC']) && $driver['TXCNC'] != '1899-12-30 00:00:00.000' ? date('Y-m-d', strtotime($driver['TXCNC'])) : '' ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Address, Contacts, Comments -->
            <div class="col-md-6">
                <!-- Address Section -->
                <div class="tls-form-card mb-3" id="address-section">
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

<!-- REQUIRED: TLS JavaScript Libraries -->
<script src="<?= base_url('js/tls-autocomplete.js') ?>"></script>
<script src="<?= base_url('js/tls-form-tracker.js') ?>"></script>

<script>
// Global variables
let currentDriverKey = <?= $driver['DriverKey'] ?? 0 ?>;
let contactModal;
let commentModal;

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded fired');

    // Initialize modals
    contactModal = new bootstrap.Modal(document.getElementById('contactModal'));
    commentModal = new bootstrap.Modal(document.getElementById('commentModal'));

    // Load driver data if driver is loaded
    <?php if ($driver && !$isNewDriver): ?>
        loadDriverData();
    <?php endif; ?>

    // Initialize TLS Form Tracker (only if driver form exists)
    <?php if ($driver): ?>
    if (typeof TLSFormTracker !== 'undefined') {
        console.log('Creating TLSFormTracker instance...');
        const tracker = new TLSFormTracker({
            formSelector: '#driver-form',
            saveButtonId: 'tls-save-btn',
            resetButtonId: 'tls-reset-btn',
            saveIndicatorId: 'unsaved-changes-alert',
            changeCounterId: 'tls-change-counter',
            excludeFields: [],
            onSave: function(changes) {
                console.log('Saving driver changes:', changes);
                document.getElementById('driver-form').submit();
            }
        });
        console.log('TLSFormTracker instance created');
    } else {
        console.error('TLSFormTracker not available');
    }
    <?php endif; ?>

    // Initialize TLS Autocomplete for driver search
    const driverSearchField = document.getElementById('driver_key');
    console.log('Driver search field:', driverSearchField);
    console.log('TLSAutocomplete available:', typeof TLSAutocomplete);

    if (driverSearchField && typeof TLSAutocomplete !== 'undefined') {
        console.log('Creating TLSAutocomplete instance...');
        const driverAutocomplete = new TLSAutocomplete(
            driverSearchField,
            'drivers',
            function(driver) {
                console.log('Driver selected:', driver);
                // Set the search field value to the DriverKey
                driverSearchField.value = driver.value;
                // Auto-submit the form when driver is selected from dropdown
                document.querySelector('form[action*="driver-maintenance/search"]').submit();
            }
        );
        console.log('TLSAutocomplete instance created:', driverAutocomplete);
    } else {
        console.error('Failed to initialize autocomplete. Field:', driverSearchField, 'TLSAutocomplete:', typeof TLSAutocomplete);
    }
});

// New Driver
function newDriver() {
    // Check for unsaved changes
    if (typeof TLSFormTracker !== 'undefined' && TLSFormTracker.hasChanges()) {
        if (!confirm('You have unsaved changes. Are you sure you want to create a new driver? All unsaved changes will be lost.')) {
            return;
        }
    }

    // Confirm new driver creation
    if (!confirm('Are you sure you want to create a new driver? This will reserve a Driver Key in the database.')) {
        return;
    }

    // Call create new driver endpoint
    fetch('<?= base_url('safety/driver-maintenance/create-new') ?>', {
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
            // Redirect to load the newly created driver
            window.location.href = '<?= base_url('safety/driver-maintenance/load') ?>/' + data.driver_key;
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while creating the new driver.');
    });
}

// Load driver data (address, contacts, comments)
function loadDriverData() {
    if (currentDriverKey > 0) {
        loadAddress();
        loadContacts();
        loadComments();
    }
}

// Reset form
function resetForm() {
    if (confirm('Are you sure you want to reset the form? All unsaved changes will be lost.')) {
        window.location.reload();
    }
}

// ==================== ADDRESS MANAGEMENT ====================

function loadAddress() {
    fetch(`<?= base_url('safety/driver-maintenance/get-address') ?>?driver_key=${currentDriverKey}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.address) {
                displayAddress(data.address);
            } else {
                document.getElementById('no-address-message').style.display = 'block';
                document.getElementById('edit-address-btn').style.display = 'inline-block';
            }
        })
        .catch(error => {
            console.error('Error loading address:', error);
        });
}

function displayAddress(address) {
    document.getElementById('addr-name-key').value = address.NameKey || 0;
    document.getElementById('addr-name1').textContent = address.Name1 || '';
    document.getElementById('addr-name2').textContent = address.Name2 || '';
    document.getElementById('addr-address1').textContent = address.Address1 || '';
    document.getElementById('addr-address2').textContent = address.Address2 || '';
    document.getElementById('addr-city').textContent = address.City || '';

    let stateZip = '';
    if (address.State) stateZip += ', ' + address.State;
    if (address.Zip) stateZip += ' ' + address.Zip;
    document.getElementById('addr-state-zip').textContent = stateZip;

    document.getElementById('addr-phone').textContent = address.Phone ? 'Phone: ' + address.Phone : '';

    document.getElementById('address-display').style.display = 'block';
    document.getElementById('address-edit').style.display = 'none';
    document.getElementById('no-address-message').style.display = 'none';
    document.getElementById('edit-address-btn').style.display = 'inline-block';
}

function editAddress() {
    const nameKey = document.getElementById('addr-name-key').value;

    if (nameKey > 0) {
        // Load existing address into edit form
        document.getElementById('addr-edit-name1').value = document.getElementById('addr-name1').textContent;
        document.getElementById('addr-edit-name2').value = document.getElementById('addr-name2').textContent;
        document.getElementById('addr-edit-address1').value = document.getElementById('addr-address1').textContent;
        document.getElementById('addr-edit-address2').value = document.getElementById('addr-address2').textContent;
        document.getElementById('addr-edit-city').value = document.getElementById('addr-city').textContent;
        document.getElementById('addr-edit-state').value = ''; // Will be extracted from stateZip
        document.getElementById('addr-edit-zip').value = ''; // Will be extracted from stateZip
        document.getElementById('addr-edit-phone').value = document.getElementById('addr-phone').textContent.replace('Phone: ', '');
    }

    document.getElementById('address-display').style.display = 'none';
    document.getElementById('address-edit').style.display = 'block';
    document.getElementById('no-address-message').style.display = 'none';
    document.getElementById('edit-address-btn').style.display = 'none';
}

function cancelEditAddress() {
    const nameKey = document.getElementById('addr-name-key').value;

    if (nameKey > 0) {
        document.getElementById('address-display').style.display = 'block';
    } else {
        document.getElementById('no-address-message').style.display = 'block';
    }

    document.getElementById('address-edit').style.display = 'none';
    document.getElementById('edit-address-btn').style.display = 'inline-block';
}

function saveAddress() {
    const formData = {
        driver_key: currentDriverKey,
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

    fetch('<?= base_url('safety/driver-maintenance/save-address') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayAddress(data.address);
            alert('Address saved successfully.');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error saving address:', error);
        alert('An error occurred while saving the address.');
    });
}

// ==================== CONTACT MANAGEMENT ====================

function loadContacts() {
    fetch(`<?= base_url('safety/driver-maintenance/get-contacts') ?>?driver_key=${currentDriverKey}`)
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

function displayContacts(contacts) {
    const container = document.getElementById('contacts-table-container');
    const countBadge = document.getElementById('contact-count');

    countBadge.textContent = contacts.length;

    if (contacts.length === 0) {
        container.innerHTML = '<p class="text-muted">No contacts found. Click "Add Contact" to create one.</p>';
        return;
    }

    let html = '<table class="table table-sm table-hover"><thead><tr>';
    html += '<th>Name</th><th>Phone</th><th>Mobile</th><th>Relationship</th><th class="text-end">Actions</th>';
    html += '</tr></thead><tbody>';

    contacts.forEach(contact => {
        html += '<tr>';
        html += `<td>${contact.FirstName || ''} ${contact.LastName || ''}</td>`;
        html += `<td>${contact.Phone || ''}</td>`;
        html += `<td>${contact.Mobile || ''}</td>`;
        html += `<td>${contact.Relationship || ''}</td>`;
        html += `<td class="text-end">`;
        html += `<button class="btn btn-sm tls-btn-primary me-1" onclick="editContact(${contact.ContactKey})"><i class="bi bi-pencil"></i></button>`;
        html += `<button class="btn btn-sm tls-btn-warning" onclick="deleteContact(${contact.ContactKey})"><i class="bi bi-trash"></i></button>`;
        html += `</td></tr>`;
    });

    html += '</tbody></table>';
    container.innerHTML = html;
}

function showContactModal() {
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

function editContact(contactKey) {
    // Load contact data
    fetch(`<?= base_url('safety/driver-maintenance/get-contacts') ?>?driver_key=${currentDriverKey}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const contact = data.contacts.find(c => c.ContactKey == contactKey);
                if (contact) {
                    document.getElementById('contact-key').value = contact.ContactKey;
                    document.getElementById('contact-first-name').value = contact.FirstName || '';
                    document.getElementById('contact-last-name').value = contact.LastName || '';
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

function saveContact() {
    const formData = {
        driver_key: currentDriverKey,
        contact_key: document.getElementById('contact-key').value,
        contact_name: document.getElementById('contact-first-name').value + ' ' + document.getElementById('contact-last-name').value,
        telephone_no: document.getElementById('contact-phone').value,
        cell_no: document.getElementById('contact-mobile').value,
        email: document.getElementById('contact-email').value,
        contact_function: document.getElementById('contact-relationship').value,
        primary_contact: document.getElementById('contact-primary').checked ? 1 : 0
    };

    fetch('<?= base_url('safety/driver-maintenance/save-contact') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            contactModal.hide();
            loadContacts();
            alert('Contact saved successfully.');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error saving contact:', error);
        alert('An error occurred while saving the contact.');
    });
}

function deleteContact(contactKey) {
    if (!confirm('Are you sure you want to delete this contact?')) {
        return;
    }

    fetch('<?= base_url('safety/driver-maintenance/delete-contact') ?>', {
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
            loadContacts();
            alert('Contact deleted successfully.');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error deleting contact:', error);
        alert('An error occurred while deleting the contact.');
    });
}

// ==================== COMMENT MANAGEMENT ====================

function loadComments() {
    fetch(`<?= base_url('safety/driver-maintenance/get-comments') ?>?driver_key=${currentDriverKey}`)
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
        html += '<small class="text-muted">';
        if (comment.CommentBy) html += `Added by ${comment.CommentBy}`;
        if (comment.CommentDate) html += ` on ${new Date(comment.CommentDate).toLocaleDateString()}`;
        if (comment.EditedBy) html += ` | Edited by ${comment.EditedBy}`;
        if (comment.EditedDate) html += ` on ${new Date(comment.EditedDate).toLocaleDateString()}`;
        html += '</small>';
        html += '<div class="mt-2">';
        html += `<button class="btn btn-sm tls-btn-primary me-1" onclick="editComment(${comment.CommentKey})"><i class="bi bi-pencil"></i> Edit</button>`;
        html += `<button class="btn btn-sm tls-btn-warning" onclick="deleteComment(${comment.CommentKey})"><i class="bi bi-trash"></i> Delete</button>`;
        html += '</div>';
        html += '</div></div>';
    });

    container.innerHTML = html;
}

function showCommentModal() {
    document.getElementById('comment-key').value = '0';
    document.getElementById('comment-text').value = '';
    document.getElementById('commentModalLabel').textContent = 'Add Comment';
    commentModal.show();
}

function editComment(commentKey) {
    fetch(`<?= base_url('safety/driver-maintenance/get-comments') ?>?driver_key=${currentDriverKey}`)
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

function saveComment() {
    const formData = {
        driver_key: currentDriverKey,
        comment_key: document.getElementById('comment-key').value,
        comment: document.getElementById('comment-text').value
    };

    fetch('<?= base_url('safety/driver-maintenance/save-comment') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            commentModal.hide();
            loadComments();
            alert('Comment saved successfully.');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error saving comment:', error);
        alert('An error occurred while saving the comment.');
    });
}

function deleteComment(commentKey) {
    if (!confirm('Are you sure you want to delete this comment?')) {
        return;
    }

    fetch('<?= base_url('safety/driver-maintenance/delete-comment') ?>', {
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
            loadComments();
            alert('Comment deleted successfully.');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error deleting comment:', error);
        alert('An error occurred while deleting the comment.');
    });
}
</script>

<?= $this->endSection() ?>
