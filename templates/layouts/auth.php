<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle ?? 'Login') ?> — <?= htmlspecialchars($appName) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body{background:linear-gradient(135deg,#1a1a2e 0%,#16213e 50%,#0f3460 100%);min-height:100vh;display:flex;align-items:center;justify-content:center}
        .auth-card{background:#fff;border-radius:1rem;box-shadow:0 25px 60px rgba(0,0,0,.35);width:100%;max-width:440px;padding:2.5rem}
        .auth-logo{text-align:center;margin-bottom:1.5rem}
        .auth-logo i{font-size:2.5rem;color:#1a1a2e}
        .auth-logo h4{font-weight:700;margin-top:.5rem}
    </style>
</head>
<body>
    <div class="auth-card">
        <?php if (!empty($f)): ?>
        <div class="alert alert-<?= htmlspecialchars($f['type']) ?> py-2 small"><?= htmlspecialchars($f['message']) ?></div>
        <?php endif; ?>
        <?= $content ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function togglePw(btn){var i=btn.parentElement.querySelector('input'),ic=btn.querySelector('i');if(i.type==='password'){i.type='text';ic.className='bi bi-eye-slash'}else{i.type='password';ic.className='bi bi-eye'}}
    </script>
</body>
</html>
