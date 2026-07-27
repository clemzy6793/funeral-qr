<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\DB;

class Tenant
{
    public static function create(array $data): int
    {
        $data['uuid'] = DB::uuid();
        $data['referral_code'] = strtoupper(substr(bin2hex(random_bytes(5)), 0, 8));
        return DB::insert('tenants', $data);
    }

    public static function find(int $id): ?array
    {
        return DB::fetchOne("SELECT * FROM tenants WHERE id = ?", [$id]);
    }

    public static function findBySlug(string $slug): ?array
    {
        return DB::fetchOne("SELECT * FROM tenants WHERE slug = ?", [$slug]);
    }

    public static function findByUuid(string $uuid): ?array
    {
        return DB::fetchOne("SELECT * FROM tenants WHERE uuid = ?", [$uuid]);
    }

    public static function findByDomain(string $domain): ?array
    {
        return DB::fetchOne("SELECT * FROM tenants WHERE custom_domain = ?", [$domain]);
    }

    public static function findByReferralCode(string $code): ?array
    {
        return DB::fetchOne("SELECT * FROM tenants WHERE referral_code = ?", [$code]);
    }

    public static function update(int $id, array $data): void
    {
        DB::update('tenants', $data, 'id = ?', [$id]);
    }

    public static function delete(int $id): void
    {
        DB::delete('tenants', 'id = ?', [$id]);
    }

    public static function all(string $search = '', string $status = '', int $limit = 50, int $offset = 0): array
    {
        $where = ['1=1'];
        $params = [];
        if ($search) {
            $where[] = '(t.name LIKE ? OR t.email LIKE ? OR t.slug LIKE ?)';
            $params = array_merge($params, ["%{$search}%", "%{$search}%", "%{$search}%"]);
        }
        if ($status) {
            $where[] = 't.status = ?';
            $params[] = $status;
        }
        $w = implode(' AND ', $where);
        $params[] = $limit;
        $params[] = $offset;
        return DB::fetchAll(
            "SELECT t.*, s.plan_id, s.status as sub_status, p.name as plan_name,
                    (SELECT COUNT(*) FROM events WHERE tenant_id = t.id) as event_count,
                    (SELECT COUNT(*) FROM users WHERE tenant_id = t.id) as user_count
             FROM tenants t
             LEFT JOIN subscriptions s ON s.tenant_id = t.id AND s.status IN ('active','trial')
             LEFT JOIN plans p ON p.id = s.plan_id
             WHERE {$w}
             ORDER BY t.created_at DESC LIMIT ? OFFSET ?",
            $params
        );
    }

    public static function count(string $status = ''): int
    {
        if ($status) {
            return DB::count('tenants', 'status = ?', [$status]);
        }
        return DB::count('tenants');
    }

    public static function generateSlug(string $name): string
    {
        $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $name), '-'));
        if (!$slug) $slug = 'tenant';
        $base = $slug;
        $counter = 1;
        while (DB::fetchOne("SELECT id FROM tenants WHERE slug = ?", [$slug])) {
            $slug = $base . '-' . (++$counter);
        }
        return $slug;
    }

    public static function updateStorage(int $id): void
    {
        $bytes = DB::fetchOne(
            "SELECT COALESCE(SUM(file_size), 0) as total FROM media WHERE tenant_id = ?", [$id]
        );
        DB::update('tenants', ['storage_used' => $bytes['total'] ?? 0], 'id = ?', [$id]);
    }

    public static function getSubscription(int $tenantId): ?array
    {
        return DB::fetchOne(
            "SELECT s.*, p.name as plan_name, p.slug as plan_slug, p.*
             FROM subscriptions s
             JOIN plans p ON p.id = s.plan_id
             WHERE s.tenant_id = ? ORDER BY s.created_at DESC LIMIT 1",
            [$tenantId]
        );
    }

    public static function stats(): array
    {
        return [
            'total'     => self::count(),
            'active'    => self::count('active'),
            'suspended' => self::count('suspended'),
            'pending'   => self::count('pending'),
        ];
    }
}
