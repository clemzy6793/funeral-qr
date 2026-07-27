<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\DB;

class EventType
{
    public static function all(?int $tenantId = null): array
    {
        if ($tenantId) {
            return DB::fetchAll(
                "SELECT * FROM event_types WHERE (is_system = 1 OR tenant_id = ?) AND is_active = 1 ORDER BY is_system DESC, name",
                [$tenantId]
            );
        }
        return DB::fetchAll("SELECT * FROM event_types WHERE is_system = 1 AND is_active = 1 ORDER BY name");
    }

    public static function find(int $id): ?array
    {
        return DB::fetchOne("SELECT * FROM event_types WHERE id = ?", [$id]);
    }

    public static function findBySlug(string $slug, ?int $tenantId = null): ?array
    {
        if ($tenantId) {
            return DB::fetchOne("SELECT * FROM event_types WHERE slug = ? AND (is_system = 1 OR tenant_id = ?)", [$slug, $tenantId]);
        }
        return DB::fetchOne("SELECT * FROM event_types WHERE slug = ? AND is_system = 1", [$slug]);
    }

    public static function create(array $data): int
    {
        return DB::insert('event_types', $data);
    }

    public static function update(int $id, array $data): void
    {
        DB::update('event_types', $data, 'id = ?', [$id]);
    }

    public static function delete(int $id): void
    {
        DB::delete('event_types', 'id = ? AND is_system = 0', [$id]);
    }

    public static function getDefaultFields(int $id): array
    {
        $row = self::find($id);
        if (!$row || !$row['default_fields']) return [];
        $decoded = json_decode($row['default_fields'], true);
        return is_array($decoded) ? $decoded : [];
    }
}
