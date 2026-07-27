<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle ?? 'Admin') ?> — <?= htmlspecialchars($appName) ?> Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar{width:250px;min-height:100vh;background:#1a1a2e;position:fixed;top:0;left:0;overflow-y:auto;z-index:1000}
        .sidebar .nav-link{color:rgba(255,255,255,.7);padding:.6rem 1.2rem;font-size:.9rem;border-radius:0}
        .sidebar .nav-link:hover,.sidebar .nav-link.active{color:#fff;background:rgba(255,255,255,.1)}
        .sidebar .nav-link i{width:1.4rem;display:inline-block}
        .sidebar-brand{padding:1rem 1.2rem;color:#fff;font-weight:700;font-size:1.1rem;border-bottom:1px solid rgba(255,255,255,.1)}
        .sidebar-section{color:rgba(255,255,255,.4);padding:.8rem 1.2rem .3rem;font-size:.7rem;text-transform:uppercase;letter-spacing:.08em}
        .main-content{margin-left:250px;min-height:100vh;background:#f4f5f7}
        .topbar{background:#fff;border-bottom:1px solid #e5e7eb;padding:.75rem 1.5rem}
        @media(max-width:768px){.sidebar{transform:translateX(-100%);transition:.3s}.sidebar.show{transform:translateX(0)}.main-content{margin-left:0}}
    </style>
</head>
<body>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-brand"><i class="bi bi-qr-code"></i> <?= htmlspecialchars($appName) ?></div>
        <div class="sidebar-section">Platform</div>
        <nav class="nav flex-column">
            <a class="nav-link <?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>" href="/superadmin">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a class="nav-link <?= ($activePage ?? '') === 'tenants' ? 'active' : '' ?>" href="/superadmin/tenants">
                <i class="bi bi-building"></i> Tenants
            </a>
            <a class="nav-link <?= ($activePage ?? '') === 'plans' ? 'active' : '' ?>" href="/superadmin/plans">
                <i class="bi bi-credit-card"></i> Plans
            </a>
            <a class="nav-link <?= ($activePage ?? '') === 'coupons' ? 'active' : '' ?>" href="/superadmin/coupons">
                <i class="bi bi-tag"></i> Coupons
            </a>
        </nav>
        <div class="sidebar-section">Payments</div>
        <nav class="nav flex-column">
            <a class="nav-link <?= ($activePage ?? '') === 'providers' ? 'active' : '' ?>" href="/superadmin/providers">
                <i class="bi bi-wallet2"></i> Providers
            </a>
            <a class="nav-link <?= ($activePage ?? '') === 'revenue' ? 'active' : '' ?>" href="/superadmin/revenue">
                <i class="bi bi-graph-up-arrow"></i> Revenue
            </a>
        </nav>
        <div class="sidebar-section">System</div>
        <nav class="nav flex-column">
            <a class="nav-link <?= ($activePage ?? '') === 'settings' ? 'active' : '' ?>" href="/superadmin/settings">
                <i class="bi bi-gear"></i> Settings
            </a>
            <a class="nav-link <?= ($activePage ?? '') === 'support' ? 'active' : '' ?>" href="/superadmin/support">
                <i class="bi bi-headset"></i> Support
            </a>
            <a class="nav-link <?= ($activePage ?? '') === 'activity' ? 'active' : '' ?>" href="/superadmin/activity">
                <i class="bi bi-clock-history"></i> Activity Log
            </a>
        </nav>
        <div class="mt-auto p-3 border-top border-secondary">
            <div class="text-light small mb-2"><i class="bi bi-person-circle"></i> <?= htmlspecialchars($currentUser['name'] ?? '') ?></div>
            <a href="/logout" class="btn btn-outline-light btn-sm w-100"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </div>
    </div>

    <div class="main-content">
        <div class="topbar d-flex justify-content-between align-items-center">
            <button class="btn btn-sm btn-outline-secondary d-md-none" onclick="document.getElementById('sidebar').classList.toggle('show')">
                <i class="bi bi-list"></i>
            </button>
            <h5 class="mb-0 fw-bold"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h5>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-dark">Super Admin</span>
            </div>
        </div>
        <div class="p-4">
            <?php if (!empty($f)): ?>
            <div class="alert alert-<?= htmlspecialchars($f['type']) ?> alert-dismissible fade show">
                <?= htmlspecialchars($f['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            <?= $content ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
