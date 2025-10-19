<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle) ?></title>

    <!-- REQUIRED CSS - TLS Standards -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= base_url('css/app.css') ?>" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="tls-page-header">
            <h1 class="tls-page-title">
                <i class="bi-speedometer2 me-2"></i>Dashboard
            </h1>
            <div class="tls-top-actions">
                <a href="<?= base_url('logout') ?>" class="btn tls-btn-secondary">
                    <i class="bi-box-arrow-right me-2"></i>Logout
                </a>
            </div>
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
                            <p class="mb-3">You have access to <strong><?= count($user['menus']) ?></strong> menu items:</p>
                            <div class="row">
                                <?php
                                $displayMenus = array_filter($user['menus'], function($menu) {
                                    return !str_starts_with($menu, 'sec');
                                });
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
                        <div class="alert alert-info">
                            <i class="bi-info-circle-fill me-2"></i>
                            <strong>CodeIgniter 4 Authentication Working!</strong>
                            This dashboard demonstrates the complete authentication flow using CI4 patterns.
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
    </div>

    <!-- REQUIRED JAVASCRIPT -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
