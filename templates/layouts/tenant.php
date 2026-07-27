<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?> — <?= htmlspecialchars($currentTenant['name'] ?? $appName) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root{--brand-primary:<?= htmlspecialchars($currentTenant['brand_color_primary'] ?? '#212529') ?>;--brand-secondary:<?= htmlspecialchars($currentTenant['brand_color_secondary'] ?? '#6c757d') ?>}
        .sidebar{width:250px;min-height:100vh;background:var(--brand-primary);position:fixed;top:0;left:0;overflow-y:auto;z-index:1000}
        .sidebar .nav-link{color:rgba(255,255,255,.7);padding:.6rem 1.2rem;font-size:.9rem;border-radius:0}
        .sidebar .nav-link:hover,.sidebar .nav-link.active{color:#fff;background:rgba(255,255,255,.1)}
        .sidebar .nav-link i{width:1.4rem;display:inline-block}
        .sidebar-brand{padding:1rem 1.2rem;color:#fff;font-weight:700;font-size:1.05rem;border-bottom:1px solid rgba(255,255,255,.1);display:flex;align-items:center;gap:.5rem}
        .sidebar-brand img{width:28px;height:28px;object-fit:contain;border-radius:4px}
        .sidebar-section{color:rgba(255,255,255,.4);padding:.8rem 1.2rem .3rem;font-size:.7rem;text-transform:uppercase;letter-spacing:.08em}
        .main-content{margin-left:250px;min-height:100vh;background:#f4f5f7}
        .topbar{background:#fff;border-bottom:1px solid #e5e7eb;padding:.75rem 1.5rem}
        .btn-brand{background:var(--brand-primary);color:#fff;border:0}.btn-brand:hover{opacity:.9;color:#fff}
        @media(max-width:768px){.sidebar{transform:translateX(-100%);transition:.3s}.sidebar.show{transform:translateX(0)}.main-content{margin-left:0}}
    </style>
</head>
<body>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <?php if (!empty($currentTenant['logo'])): ?>
            <img src="/storage/uploads/<?= htmlspecialchars($currentTenant['logo']) ?>" alt="">
            <?php else: ?>
            <i class="bi bi-qr-code"></i>
            <?php endif; ?>
            <?= htmlspecialchars($currentTenant['name'] ?? $appName) ?>
        </div>
        <div class="sidebar-section">Events</div>
        <nav class="nav flex-column">
            <a class="nav-link <?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>" href="/dashboard">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a class="nav-link <?= ($activePage ?? '') === 'events' ? 'active' : '' ?>" href="/dashboard/events">
                <i class="bi bi-calendar-event"></i> Events
            </a>
            <a class="nav-link <?= ($activePage ?? '') === 'analytics' ? 'active' : '' ?>" href="/dashboard/analytics">
                <i class="bi bi-bar-chart-line"></i> Analytics
            </a>
        </nav>
        <div class="sidebar-section">Account</div>
        <nav class="nav flex-column">
            <a class="nav-link <?= ($activePage ?? '') === 'branding' ? 'active' : '' ?>" href="/dashboard/branding">
                <i class="bi bi-palette"></i> Branding
            </a>
            <a class="nav-link <?= ($activePage ?? '') === 'billing' ? 'active' : '' ?>" href="/dashboard/billing">
                <i class="bi bi-credit-card"></i> Billing
            </a>
            <a class="nav-link <?= ($activePage ?? '') === 'users' ? 'active' : '' ?>" href="/dashboard/users">
                <i class="bi bi-people"></i> Users
            </a>
            <a class="nav-link <?= ($activePage ?? '') === 'settings' ? 'active' : '' ?>" href="/dashboard/settings">
                <i class="bi bi-gear"></i> Settings
            </a>
        </nav>
        <div class="sidebar-section">Help</div>
        <nav class="nav flex-column">
            <a class="nav-link" href="/help"><i class="bi bi-question-circle"></i> Help Center</a>
            <a class="nav-link" href="/dashboard/support"><i class="bi bi-headset"></i> Support</a>
        </nav>
        <div class="mt-auto p-3 border-top" style="border-color:rgba(255,255,255,.1)!important">
            <div class="text-light small mb-2 text-truncate"><i class="bi bi-person-circle"></i> <?= htmlspecialchars($currentUser['name'] ?? '') ?></div>
            <a href="/logout" class="btn btn-outline-light btn-sm w-100"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </div>
    </div>

    <div class="main-content">
        <div class="topbar d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-outline-secondary d-md-none" onclick="document.getElementById('sidebar').classList.toggle('show')">
                    <i class="bi bi-list"></i>
                </button>
                <h5 class="mb-0 fw-bold"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h5>
            </div>
            <div class="d-flex align-items-center gap-2">
                <?php if (!empty($currentUser['impersonated_by'])): ?>
                <a href="/superadmin/stop-impersonate" class="btn btn-warning btn-sm"><i class="bi bi-arrow-left"></i> Back to Admin</a>
                <?php endif; ?>
                <a href="/dashboard/events/create" class="btn btn-brand btn-sm"><i class="bi bi-plus-lg"></i> New Event</a>
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
