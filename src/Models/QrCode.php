<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\DB;

class QrCode
{
    public static function create(array $data): int
    {
        return DB::insert('qr_codes', $data);
    }

    public static function find(int $id): ?array
    {
        return DB::fetchOne("SELECT * FROM qr_codes WHERE id = ?", [$id]);
    }

    public static function findByCode(string $code): ?array
    {
        return DB::fetchOne(
            "SELECT q.*, e.tenant_id, e.slug as event_slug, e.name as event_name, e.status as event_status,
                    e.payment_required, e.qr_destination, e.qr_custom_url,
                    t.slug as tenant_slug, t.status as tenant_status
             FROM qr_codes q
             JOIN events e ON e.id = q.event_id
             JOIN tenants t ON t.id = e.tenant_id
             WHERE q.code = ?",
            [$code]
        );
    }

    public static function findByEvent(int $eventId): ?array
    {
        return DB::fetchOne("SELECT * FROM qr_codes WHERE event_id = ?", [$eventId]);
    }

    public static function delete(int $id): void
    {
        $qr = self::find($id);
        if ($qr) {
            $path = QR_DIR . '/' . $qr['filename'];
            if (file_exists($path)) @unlink($path);
            DB::delete('qr_codes', 'id = ?', [$id]);
        }
    }

    public static function byTenant(int $tenantId): array
    {
        return DB::fetchAll(
            "SELECT q.*, e.name as event_name, e.slug as event_slug
             FROM qr_codes q
             JOIN events e ON e.id = q.event_id
             WHERE q.tenant_id = ?
             ORDER BY q.created_at DESC",
            [$tenantId]
        );
    }

    public static function recordScan(int $qrId, int $eventId, int $tenantId): void
    {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $device = 'desktop';
        if (preg_match('/Mobile|Android|iPhone/i', $ua)) $device = 'mobile';
        elseif (preg_match('/Tablet|iPad/i', $ua)) $device = 'tablet';

        $browser = 'other';
        if (preg_match('/Chrome/i', $ua)) $browser = 'Chrome';
        elseif (preg_match('/Firefox/i', $ua)) $browser = 'Firefox';
        elseif (preg_match('/Safari/i', $ua)) $browser = 'Safari';
        elseif (preg_match('/Edge/i', $ua)) $browser = 'Edge';

        $os = 'other';
        if (preg_match('/Windows/i', $ua)) $os = 'Windows';
        elseif (preg_match('/Mac/i', $ua)) $os = 'macOS';
        elseif (preg_match('/Linux/i', $ua)) $os = 'Linux';
        elseif (preg_match('/Android/i', $ua)) $os = 'Android';
        elseif (preg_match('/iPhone|iPad/i', $ua)) $os = 'iOS';

        DB::insert('qr_scans', [
            'tenant_id'  => $tenantId,
            'event_id'   => $eventId,
            'qr_code_id' => $qrId,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => substr($ua, 0, 500),
            'device'     => $device,
            'browser'    => $browser,
            'os'         => $os,
            'referrer'   => substr($_SERVER['HTTP_REFERER'] ?? '', 0, 500),
        ]);

        Event::incrementScans($eventId);
    }
}
