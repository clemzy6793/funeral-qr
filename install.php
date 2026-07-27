<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

use App\Core\DB;

foreach ([UPLOAD_DIR, QR_DIR, LOG_DIR] as $dir) {
    if (!is_dir($dir)) mkdir($dir, 0775, true);
}

$pdo = DB::get();

$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND role = 'super_admin'");
$stmt->execute([SUPER_ADMIN_EMAIL]);

if (!$stmt->fetch()) {
    $hash = password_hash(SUPER_ADMIN_PASS, PASSWORD_BCRYPT);
    $pdo->prepare(
        "INSERT INTO users (tenant_id, email, password_hash, name, role, is_active, email_verified_at, created_at)
         VALUES (NULL, ?, ?, ?, 'super_admin', 1, NOW(), NOW())"
    )->execute([SUPER_ADMIN_EMAIL, $hash, SUPER_ADMIN_NAME]);
    echo "Super admin created: " . SUPER_ADMIN_EMAIL . "\n";
} else {
    echo "Super admin already exists\n";
}

echo "Installation complete.\n";
