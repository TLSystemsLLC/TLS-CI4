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
        <!-- TLS Standard Page Header -->
        <div class="tls-page-header">
            <h2 class="tls-page-title">
                <i class="bi-check-circle-fill me-2"></i><?= esc($pageTitle) ?>
            </h2>
            <div class="tls-top-actions">
                <button type="button" class="btn tls-btn-primary">Primary Button</button>
                <button type="button" class="btn tls-btn-secondary">Secondary Button</button>
            </div>
        </div>

        <!-- TLS Standard Form Card -->
        <div class="tls-form-card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi-info-circle me-2"></i>Setup Verification
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-success">
                    <i class="bi-check-circle-fill me-2"></i>
                    <strong>Success!</strong> <?= esc($testMessage) ?>
                </div>

                <h6 class="mt-4">System Information:</h6>
                <ul class="list-group">
                    <li class="list-group-item">
                        <strong>CodeIgniter Version:</strong> <?= \CodeIgniter\CodeIgniter::CI_VERSION ?>
                    </li>
                    <li class="list-group-item">
                        <strong>PHP Version:</strong> <?= phpversion() ?>
                    </li>
                    <li class="list-group-item">
                        <strong>Base URL:</strong> <?= base_url() ?>
                    </li>
                    <li class="list-group-item">
                        <strong>Environment:</strong> <?= ENVIRONMENT ?>
                    </li>
                </ul>

                <h6 class="mt-4">TLS UI Components Test:</h6>

                <!-- Test Button Styles -->
                <div class="mb-3">
                    <h6>Buttons:</h6>
                    <button type="button" class="btn tls-btn-primary me-2">Primary Button</button>
                    <button type="button" class="btn tls-btn-secondary me-2">Secondary Button</button>
                    <button type="button" class="btn tls-btn-warning me-2">Warning Button</button>
                    <button type="button" class="btn tls-btn-primary me-2" disabled>Disabled Primary</button>
                </div>

                <!-- Test Form Card -->
                <div class="tls-form-card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi-box me-2"></i>Nested Form Card Test
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">This is a nested form card to verify styling works correctly.</p>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="test_input" class="form-label">Test Input</label>
                                    <input type="text" class="form-control" id="test_input" placeholder="Type something...">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="test_select" class="form-label">Test Select</label>
                                    <select class="form-select" id="test_select">
                                        <option>Option 1</option>
                                        <option>Option 2</option>
                                        <option>Option 3</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Test Save Indicator -->
                <div id="tls-save-indicator" class="tls-save-indicator mt-3" style="display: block;">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <span>You have <span id="tls-change-count" class="tls-change-counter">3</span> unsaved change(s)</span>
                </div>
            </div>
        </div>

        <!-- Next Steps Card -->
        <div class="tls-form-card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi-list-check me-2"></i>Next Steps
                </h5>
            </div>
            <div class="card-body">
                <ol>
                    <li><strong>✅ CodeIgniter 4 installed</strong> - Framework is ready</li>
                    <li><strong>✅ UI Assets copied</strong> - app.css and JavaScript files in place</li>
                    <li><strong>✅ MAMP configured</strong> - Running at localhost:8888/tls-ci4/</li>
                    <li><strong>✅ TLS Theme verified</strong> - This page demonstrates all UI components work</li>
                    <li><strong>⏳ Migrate core classes</strong> - Auth, Database, MenuManager</li>
                    <li><strong>⏳ Build Driver Maintenance</strong> - Proof-of-concept entity screen</li>
                </ol>

                <div class="alert alert-info mt-3">
                    <i class="bi-lightbulb me-2"></i>
                    <strong>Current Status:</strong> Week 1 setup is complete! Ready to begin migrating core classes and building the first entity screen.
                </div>
            </div>
        </div>
    </div>

    <!-- REQUIRED JAVASCRIPT - TLS Standards -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url('js/tls-form-tracker.js') ?>"></script>
    <script src="<?= base_url('js/tls-autocomplete.js') ?>"></script>

    <script>
        console.log('TLS CodeIgniter 4 - Theme loaded successfully!');
        console.log('TLSFormTracker available:', typeof TLSFormTracker !== 'undefined');
        console.log('TLSAutocomplete available:', typeof TLSAutocomplete !== 'undefined');
    </script>
</body>
</html>
