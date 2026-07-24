<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

use App\Database;

foreach ([UPLOAD_DIR, QR_DIR] as $dir) {
    if (!is_dir($dir)) mkdir($dir, 0775, true);
}

Database::init();

$db   = Database::get();
$stmt = $db->prepare('SELECT id FROM admins WHERE username = ?');
$stmt->execute([DEFAULT_ADMIN_USER]);

if (!$stmt->fetch()) {
    $hash = password_hash(DEFAULT_ADMIN_PASS, PASSWORD_BCRYPT);
    $db->prepare('INSERT INTO admins (username, password_hash) VALUES (?, ?)')
       ->execute([DEFAULT_ADMIN_USER, $hash]);
    echo "Admin created: " . DEFAULT_ADMIN_USER . "\n";
} else {
    echo "Admin already exists\n";
}

echo "Installation complete.\n";
