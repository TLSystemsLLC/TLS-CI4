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

    <style>
        .login-container {
            max-width: 500px;
            margin: 100px auto;
        }
        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-logo h1 {
            color: #2c3e50;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-logo">
                <i class="bi-truck" style="font-size: 3rem; color: #27ae60;"></i>
                <h1>TLS Operations</h1>
                <p class="text-muted">Transportation Management System</p>
            </div>

            <!-- TLS Standard Form Card -->
            <div class="tls-form-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi-box-arrow-in-right me-2"></i>Login
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (session()->has('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi-exclamation-triangle-fill me-2"></i>
                            <?= esc(session('error')) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->has('message')): ?>
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <i class="bi-info-circle-fill me-2"></i>
                            <?= esc(session('message')) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->has('errors')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi-exclamation-triangle-fill me-2"></i>
                            <strong>Validation Errors:</strong>
                            <ul class="mb-0 mt-2">
                                <?php foreach (session('errors') as $error): ?>
                                    <li><?= esc($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('login/attempt') ?>" method="POST">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label for="customer" class="form-label">
                                <i class="bi-building me-1"></i>Customer
                            </label>
                            <input type="text"
                                   class="form-control <?= session()->has('errors') && isset(session('errors')['customer']) ? 'is-invalid' : '' ?>"
                                   id="customer"
                                   name="customer"
                                   value="<?= old('customer') ?>"
                                   placeholder="Enter customer database name"
                                   required
                                   autofocus>
                            <div class="form-text">The database name for your company</div>
                        </div>

                        <div class="mb-3">
                            <label for="user_id" class="form-label">
                                <i class="bi-person me-1"></i>User ID
                            </label>
                            <input type="text"
                                   class="form-control <?= session()->has('errors') && isset(session('errors')['user_id']) ? 'is-invalid' : '' ?>"
                                   id="user_id"
                                   name="user_id"
                                   value="<?= old('user_id') ?>"
                                   placeholder="Enter your user ID"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="bi-lock me-1"></i>Password
                            </label>
                            <input type="password"
                                   class="form-control <?= session()->has('errors') && isset(session('errors')['password']) ? 'is-invalid' : '' ?>"
                                   id="password"
                                   name="password"
                                   placeholder="Enter your password"
                                   required>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn tls-btn-primary">
                                <i class="bi-box-arrow-in-right me-2"></i>Sign In
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="text-center mt-3">
                <small class="text-muted">
                    <i class="bi-shield-check me-1"></i>Secure Login - CodeIgniter 4
                </small>
            </div>
        </div>
    </div>

    <!-- REQUIRED JAVASCRIPT -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
