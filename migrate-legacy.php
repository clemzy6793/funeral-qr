<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

use App\Core\DB;

$sqlitePath = DB_PATH;
if (!file_exists($sqlitePath)) {
    echo "No legacy SQLite database found at {$sqlitePath}. Skipping migration.\n";
    exit(0);
}

echo "Legacy Data Migration: SQLite → PostgreSQL\n";
echo "============================================\n\n";

try {
    $sqlite = new PDO('sqlite:' . $sqlitePath);
    $sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Cannot open SQLite DB: {$e->getMessage()}\n";
    exit(1);
}

$pdo = DB::get();

$check = $pdo->query("SELECT COUNT(*) FROM events WHERE legacy_brochure_id IS NOT NULL")->fetchColumn();
if ($check > 0) {
    echo "Legacy data already migrated ({$check} events found). Skipping.\n";
    exit(0);
}

$legacyTenantId = null;
$row = $pdo->query("SELECT id FROM tenants WHERE slug = 'legacy-funeral' LIMIT 1")->fetch();
if ($row) {
    $legacyTenantId = (int)$row['id'];
} else {
    $stmt = $pdo->prepare("INSERT INTO tenants (uuid, name, slug, email, status, account_type, country, referral_code, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW()) RETURNING id");
    $stmt->execute([DB::uuid(), 'Legacy Funeral Service', 'legacy-funeral', SUPER_ADMIN_EMAIL, 'active', 'individual', 'Ghana', substr(md5(uniqid()), 0, 8)]);
    $legacyTenantId = (int)$stmt->fetchColumn();
    echo "Created legacy tenant (ID: {$legacyTenantId})\n";

    $defaultPlan = $pdo->query("SELECT id FROM plans WHERE is_default = 1 LIMIT 1")->fetch();
    if ($defaultPlan) {
        $pdo->prepare("INSERT INTO subscriptions (tenant_id, plan_id, status, billing_cycle, starts_at, ends_at, created_at) VALUES (?, ?, 'active', 'lifetime', NOW(), '2099-12-31', NOW())")
            ->execute([$legacyTenantId, $defaultPlan['id']]);
    }
}

$funeralType = $pdo->query("SELECT id FROM event_types WHERE slug = 'funeral' LIMIT 1")->fetch();
$funeralTypeId = $funeralType ? (int)$funeralType['id'] : 1;

$brochures = $sqlite->query("SELECT * FROM brochures ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
echo "Found " . count($brochures) . " legacy brochure(s)\n\n";

$migrated = 0;
foreach ($brochures as $b) {
    echo "Migrating: {$b['deceased_name']} (slug: {$b['slug']})... ";

    $eventSlug = $b['slug'];
    $existing = $pdo->prepare("SELECT id FROM events WHERE tenant_id = ? AND slug = ?");
    $existing->execute([$legacyTenantId, $eventSlug]);
    if ($existing->fetch()) {
        echo "SKIP (already exists)\n";
        continue;
    }

    $dynamicFields = json_encode([
        'title' => $b['title'] ?? '',
    ]);

    $stmt = $pdo->prepare(
        "INSERT INTO events (tenant_id, event_type_id, slug, name, venue, digital_address, dynamic_fields, status, legacy_brochure_id, total_scans, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, 'published', ?, 0, ?, NOW()) RETURNING id"
    );
    $stmt->execute([
        $legacyTenantId,
        $funeralTypeId,
        $eventSlug,
        $b['deceased_name'],
        $b['funeral_location'] ?? null,
        $b['digital_address'] ?? null,
        $dynamicFields,
        $b['id'],
        $b['created_at'] ?? date('Y-m-d H:i:s'),
    ]);
    $eventId = (int)$stmt->fetchColumn();

    $pdfPath = !empty($b['pdf_filename']) ? 'storage/uploads/' . $b['pdf_filename'] : null;
    if ($pdfPath && file_exists(BASE_DIR . '/' . $pdfPath)) {
        $fileSize = filesize(BASE_DIR . '/' . $pdfPath);
        $pdo->prepare(
            "INSERT INTO media (tenant_id, event_id, type, filename, file_path, original_name, mime_type, file_size, created_at)
             VALUES (?, ?, 'pdf', ?, ?, ?, 'application/pdf', ?, NOW())"
        )->execute([
            $legacyTenantId,
            $eventId,
            $b['pdf_filename'],
            $pdfPath,
            $b['pdf_filename'],
            $fileSize,
        ]);
    }

    $qrPath = !empty($b['qr_filename']) ? 'storage/qrcodes/' . $b['qr_filename'] : null;
    if ($qrPath && file_exists(BASE_DIR . '/' . $qrPath)) {
        $qrCode = DB::uuid();
        $pdo->prepare(
            "INSERT INTO qr_codes (tenant_id, event_id, code, filename, url, file_path, format, created_at)
             VALUES (?, ?, ?, ?, ?, ?, 'png', NOW())"
        )->execute([
            $legacyTenantId,
            $eventId,
            $qrCode,
            $b['qr_filename'],
            rtrim(APP_URL, '/') . '/brochure/' . $eventSlug,
            $qrPath,
        ]);
    }

    echo "OK (event #{$eventId})\n";
    $migrated++;
}

if ($migrated > 0) {
    $storageUsed = 0;
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(file_size), 0) FROM media WHERE tenant_id = ?");
    $stmt->execute([$legacyTenantId]);
    $storageUsed = (int)$stmt->fetchColumn();
    $pdo->prepare("UPDATE tenants SET storage_used = ? WHERE id = ?")->execute([$storageUsed, $legacyTenantId]);
}

echo "\nMigrated {$migrated} brochure(s) to EventQR.\n";
echo "Legacy URLs (/brochure/{slug}) will continue to work via legacy_brochure_id lookup.\n";
