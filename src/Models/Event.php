<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\DB;

class Event
{
    public static function create(array $data): int
    {
        return DB::insert('events', $data);
    }

    public static function find(int $id): ?array
    {
        return DB::fetchOne(
            "SELECT e.*, et.name as type_name, et.slug as type_slug, et.icon as type_icon, et.default_fields
             FROM events e
             JOIN event_types et ON et.id = e.event_type_id
             WHERE e.id = ?",
            [$id]
        );
    }

    public static function findBySlug(int $tenantId, string $slug): ?array
    {
        return DB::fetchOne(
            "SELECT e.*, et.name as type_name, et.slug as type_slug, et.icon as type_icon, et.default_fields
             FROM events e
             JOIN event_types et ON et.id = e.event_type_id
             WHERE e.tenant_id = ? AND e.slug = ?",
            [$tenantId, $slug]
        );
    }

    public static function findPublicBySlug(string $tenantSlug, string $eventSlug): ?array
    {
        return DB::fetchOne(
            "SELECT e.*, et.name as type_name, et.slug as type_slug, et.icon as type_icon, et.default_fields,
                    t.name as tenant_name, t.slug as tenant_slug, t.logo, t.favicon,
                    t.brand_color_primary, t.brand_color_secondary, t.business_name, t.footer_text
             FROM events e
             JOIN event_types et ON et.id = e.event_type_id
             JOIN tenants t ON t.id = e.tenant_id
             WHERE t.slug = ? AND e.slug = ? AND e.status = 'published' AND t.status = 'active'",
            [$tenantSlug, $eventSlug]
        );
    }

    public static function findLegacyBySlug(string $slug): ?array
    {
        return DB::fetchOne(
            "SELECT e.*, et.name as type_name, et.slug as type_slug, et.icon as type_icon,
                    t.name as tenant_name, t.slug as tenant_slug, t.logo, t.favicon,
                    t.brand_color_primary, t.brand_color_secondary, t.business_name, t.footer_text
             FROM events e
             JOIN event_types et ON et.id = e.event_type_id
             JOIN tenants t ON t.id = e.tenant_id
             WHERE e.slug = ? AND e.legacy_brochure_id IS NOT NULL",
            [$slug]
        );
    }

    public static function update(int $id, array $data): void
    {
        DB::update('events', $data, 'id = ?', [$id]);
    }

    public static function delete(int $id): void
    {
        DB::delete('events', 'id = ?', [$id]);
    }

    public static function byTenant(int $tenantId, string $search = '', string $status = '', int $typeId = 0): array
    {
        $where = ['e.tenant_id = ?'];
        $params = [$tenantId];
        if ($search) {
            $where[] = '(e.name LIKE ? OR e.venue LIKE ? OR e.description LIKE ?)';
            $params = array_merge($params, ["%{$search}%", "%{$search}%", "%{$search}%"]);
        }
        if ($status) { $where[] = 'e.status = ?'; $params[] = $status; }
        if ($typeId) { $where[] = 'e.event_type_id = ?'; $params[] = $typeId; }
        $w = implode(' AND ', $where);
        return DB::fetchAll(
            "SELECT e.*, et.name as type_name, et.icon as type_icon,
                    (SELECT COUNT(*) FROM qr_scans WHERE event_id = e.id) as scan_count
             FROM events e
             JOIN event_types et ON et.id = e.event_type_id
             WHERE {$w} ORDER BY e.created_at DESC",
            $params
        );
    }

    public static function countByTenant(int $tenantId): int
    {
        return DB::count('events', 'tenant_id = ?', [$tenantId]);
    }

    public static function generateSlug(int $tenantId, string $name): string
    {
        $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $name), '-'));
        if (!$slug) $slug = 'event';
        $base = $slug;
        $counter = 1;
        while (DB::fetchOne("SELECT id FROM events WHERE tenant_id = ? AND slug = ?", [$tenantId, $slug])) {
            $slug = $base . '-' . (++$counter);
        }
        return $slug;
    }

    public static function incrementScans(int $id): void
    {
        DB::query("UPDATE events SET total_scans = total_scans + 1 WHERE id = ?", [$id]);
    }

    public static function getMedia(int $eventId, ?string $type = null): array
    {
        if ($type) {
            return DB::fetchAll("SELECT * FROM media WHERE event_id = ? AND type = ? ORDER BY sort_order, created_at", [$eventId, $type]);
        }
        return DB::fetchAll("SELECT * FROM media WHERE event_id = ? ORDER BY sort_order, created_at", [$eventId]);
    }

    public static function getQrCode(int $eventId): ?array
    {
        return DB::fetchOne("SELECT * FROM qr_codes WHERE event_id = ?", [$eventId]);
    }

    public static function platformStats(): array
    {
        $row = DB::fetchOne("SELECT COUNT(*) as total,
            SUM(CASE WHEN status='published' THEN 1 ELSE 0 END) as published,
            SUM(CASE WHEN status='draft' THEN 1 ELSE 0 END) as draft,
            SUM(total_scans) as total_scans FROM events");
        return $row ?: ['total' => 0, 'published' => 0, 'draft' => 0, 'total_scans' => 0];
    }
}
