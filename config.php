<?php
declare(strict_types=1);

define('BASE_DIR', __DIR__);
define('APP_NAME', getenv('APP_NAME') ?: 'Funeral QR');
define('APP_URL', rtrim(getenv('APP_URL') ?: 'http://localhost:8080', '/'));
define('DB_PATH', BASE_DIR . '/storage/brochures.db');
define('UPLOAD_DIR', BASE_DIR . '/storage/uploads');
define('QR_DIR', BASE_DIR . '/storage/qrcodes');
define('MAX_UPLOAD_SIZE', 20 * 1024 * 1024);

define('DEFAULT_ADMIN_USER', getenv('ADMIN_USER') ?: 'admin');
define('DEFAULT_ADMIN_PASS', getenv('ADMIN_PASS') ?: 'changeme123');

require_once BASE_DIR . '/vendor/autoload.php';
