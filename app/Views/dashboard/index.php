<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<!-- Page Header -->
<div class="tls-page-header">
    <h1 class="tls-page-title">
        <i class="bi-speedometer2 me-2"></i>Dashboard
    </h1>
</div>

<div class="row">
    <!-- User Information Card -->
    <div class="col-lg-6 mb-4">
        <div class="tls-form-card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi-person-circle me-2"></i>User Information
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tbody>
                        <tr>
                            <th style="width: 150px;">User ID:</th>
                            <td><?= esc($user['user_id'] ?? 'N/A') ?></td>
                        </tr>
                        <tr>
                            <th>Name:</th>
                            <td><?= esc($user['user_name'] ?? 'N/A') ?></td>
                        </tr>
                        <tr>
                            <th>Customer Database:</th>
                            <td><?= esc($user['customer_db'] ?? 'N/A') ?></td>
                        </tr>
                        <tr>
                            <th>Company:</th>
                            <td><?= esc($user['company_name'] ?? 'N/A') ?></td>
                        </tr>
                        <tr>
                            <th>Session Start:</th>
                            <td><?= date('Y-m-d H:i:s', $user['login_time'] ?? time()) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Company Information Card -->
    <div class="col-lg-6 mb-4">
        <div class="tls-form-card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi-building me-2"></i>Company Information
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tbody>
                        <tr>
                            <th style="width: 150px;">Company:</th>
                            <td><?= esc($user['company_name'] ?? 'N/A') ?></td>
                        </tr>
                        <tr>
                            <th>Address:</th>
                            <td>
                                <?php if (!empty($user['company_address'])): ?>
                                    <?= esc($user['company_address']) ?><br>
                                    <?= esc($user['company_city'] ?? '') ?>,
                                    <?= esc($user['company_state'] ?? '') ?>
                                    <?= esc($user['company_zip'] ?? '') ?>
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Phone:</th>
                            <td><?= esc($user['company_phone'] ?? 'N/A') ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Permissions Card -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="tls-form-card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi-shield-check me-2"></i>Your Permissions
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($user['menus']) && is_array($user['menus'])): ?>
                    <?php
                    // Filter out security permissions (sec prefix)
                    $displayMenus = array_filter($user['menus'], function($menu) {
                        return !str_starts_with($menu, 'sec');
                    });
                    ?>
                    <p class="mb-3">You have access to <strong><?= count($displayMenus) ?></strong> menu items:</p>
                    <div class="row">
                        <?php
                        $chunks = array_chunk($displayMenus, ceil(count($displayMenus) / 3));
                        foreach ($chunks as $chunk):
                        ?>
                            <div class="col-lg-4">
                                <ul class="list-unstyled">
                                    <?php foreach ($chunk as $menu): ?>
                                        <li class="mb-1">
                                            <i class="bi-check-circle-fill text-success me-2"></i>
                                            <?= esc($menu) ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No menu permissions found</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions Card -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="tls-form-card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi-lightning-fill me-2"></i>Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-success">
                    <i class="bi-check-circle-fill me-2"></i>
                    <strong>Phase 3 Complete: MenuManager Migrated!</strong>
                    Navigation menu is now available with permission-based filtering.
                </div>
                <p>Available modules (coming soon):</p>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <button class="btn btn-outline-secondary w-100" disabled>
                            <i class="bi-person-badge me-2"></i>Driver Maintenance
                        </button>
                    </div>
                    <div class="col-md-4 mb-3">
                        <button class="btn btn-outline-secondary w-100" disabled>
                            <i class="bi-truck me-2"></i>Load Entry
                        </button>
                    </div>
                    <div class="col-md-4 mb-3">
                        <button class="btn btn-outline-secondary w-100" disabled>
                            <i class="bi-cash-stack me-2"></i>Payroll
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
