<?php
declare(strict_types=1);

define('BASE_DIR', __DIR__);
define('APP_NAME', getenv('APP_NAME') ?: 'EventQR');
define('APP_URL', rtrim(getenv('APP_URL') ?: 'http://localhost:8080', '/'));
define('UPLOAD_DIR', BASE_DIR . '/storage/uploads');
define('QR_DIR', BASE_DIR . '/storage/qrcodes');
define('LOG_DIR', BASE_DIR . '/storage/logs');
define('MAX_UPLOAD_SIZE', 50 * 1024 * 1024);

// Legacy SQLite (kept for migration only)
define('DB_PATH', BASE_DIR . '/storage/brochures.db');
define('DEFAULT_ADMIN_USER', getenv('ADMIN_USER') ?: 'admin');
define('DEFAULT_ADMIN_PASS', getenv('ADMIN_PASS') ?: 'changeme123');

// PostgreSQL
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '5432');
define('DB_NAME', getenv('DB_NAME') ?: 'eventqr');
define('DB_USER', getenv('DB_USER') ?: 'eventqr');
define('DB_PASS', getenv('DB_PASS') ?: 'eventqr');

// Super Admin
define('SUPER_ADMIN_EMAIL', getenv('SUPER_ADMIN_EMAIL') ?: 'admin@eventqr.com');
define('SUPER_ADMIN_PASS', getenv('SUPER_ADMIN_PASS') ?: 'changeme123');
define('SUPER_ADMIN_NAME', getenv('SUPER_ADMIN_NAME') ?: 'Super Admin');

// SMTP
define('SMTP_HOST', getenv('SMTP_HOST') ?: '');
define('SMTP_PORT', (int)(getenv('SMTP_PORT') ?: 587));
define('SMTP_USER', getenv('SMTP_USER') ?: '');
define('SMTP_PASS', getenv('SMTP_PASS') ?: '');
define('SMTP_FROM', getenv('SMTP_FROM') ?: 'noreply@eventqr.com');
define('SMTP_FROM_NAME', getenv('SMTP_FROM_NAME') ?: 'EventQR');

require_once BASE_DIR . '/vendor/autoload.php';
