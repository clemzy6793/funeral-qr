<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle ?? APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/admin">
                <i class="bi bi-qr-code"></i> <?= APP_NAME ?>
            </a>
            <?php if ($isLoggedIn ?? false): ?>
            <div class="d-flex align-items-center gap-2">
                <span class="text-light small opacity-75">
                    <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['admin_user'] ?? '') ?>
                </span>
                <a href="/logout" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
            <?php endif; ?>
        </div>
    </nav>
    <main class="container py-4">
        <?php if (!empty($f)): ?>
        <div class="alert alert-<?= htmlspecialchars($f['type']) ?> alert-dismissible fade show">
            <?= htmlspecialchars($f['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        <?= $content ?>
    </main>
    <footer class="text-center text-muted small py-3 border-top mt-auto">
        <?= APP_NAME ?> &copy; <?= date('Y') ?>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
