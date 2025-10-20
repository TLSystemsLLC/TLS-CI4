<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle ?? 'TLS Operations') ?></title>

    <!-- REQUIRED: Bootstrap CSS (exact version from standards) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- REQUIRED: Bootstrap Icons (exact version from standards) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

    <!-- REQUIRED: TLS Application CSS (standardized theme) -->
    <link href="<?= base_url('css/app.css') ?>" rel="stylesheet">

    <!-- Additional page-specific CSS -->
    <?= $this->renderSection('css') ?>
</head>
<body>
    <!-- Navigation Bar (only shown when logged in) -->
    <?php if (isset($menuStructure) && isset($currentUser)): ?>
        <?= view('partials/navbar') ?>
    <?php endif; ?>

    <!-- Main Content Area -->
    <div class="container-fluid">
        <!-- Breadcrumb Navigation (optional, set in controller) -->
        <?php if (isset($breadcrumbPath)): ?>
            <?= view('partials/breadcrumb') ?>
        <?php endif; ?>

        <!-- Flash Messages (success, error, warning, info) -->
        <?php if (session()->has('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?= esc(session('success')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->has('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= esc(session('error')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->has('warning')): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle-fill me-2"></i>
                <?= esc(session('warning')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->has('info')): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i>
                <?= esc(session('info')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Validation Errors -->
        <?php if (session()->has('errors')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Please correct the following errors:</strong>
                <ul class="mb-0 mt-2">
                    <?php foreach (session('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Page Content (defined by child views) -->
        <?= $this->renderSection('content') ?>
    </div>

    <!-- REQUIRED: Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Additional page-specific JavaScript -->
    <?= $this->renderSection('scripts') ?>
</body>
</html>
