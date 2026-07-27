<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle ?? '') ?></title>
    <meta name="description" content="<?= htmlspecialchars($metaDescription ?? '') ?>">
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle ?? '') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($metaDescription ?? '') ?>">
    <?php if (!empty($ogImage)): ?>
    <meta property="og:image" content="<?= htmlspecialchars($ogImage) ?>">
    <?php endif; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/style.css" rel="stylesheet">
    <?php if (!empty($brandPrimary)): ?>
    <style>:root{--brand-primary:<?= htmlspecialchars($brandPrimary) ?>;--brand-secondary:<?= htmlspecialchars($brandSecondary ?? '#6c757d') ?>}.btn-brand{background:var(--brand-primary);color:#fff;border:0}.btn-brand:hover{opacity:.9;color:#fff}</style>
    <?php endif; ?>
</head>
<body class="public-body">
    <?= $content ?>
    <?php if (!empty($tenantFooter)): ?>
    <footer class="text-center text-muted small py-3"><?= htmlspecialchars($tenantFooter) ?></footer>
    <?php endif; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
