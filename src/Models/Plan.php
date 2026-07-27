<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\DB;

class Plan
{
    public static function all(bool $activeOnly = false): array
    {
        $where = $activeOnly ? 'WHERE is_active = 1' : '';
        return DB::fetchAll("SELECT * FROM plans {$where} ORDER BY sort_order, price_monthly");
    }

    public static function find(int $id): ?array
    {
        return DB::fetchOne("SELECT * FROM plans WHERE id = ?", [$id]);
    }

    public static function findBySlug(string $slug): ?array
    {
        return DB::fetchOne("SELECT * FROM plans WHERE slug = ?", [$slug]);
    }

    public static function getDefault(): ?array
    {
        return DB::fetchOne("SELECT * FROM plans WHERE is_default = 1 AND is_active = 1 LIMIT 1");
    }

    public static function create(array $data): int
    {
        return DB::insert('plans', $data);
    }

    public static function update(int $id, array $data): void
    {
        if (!empty($data['is_default'])) {
            DB::query("UPDATE plans SET is_default = 0 WHERE id != ?", [$id]);
        }
        DB::update('plans', $data, 'id = ?', [$id]);
    }

    public static function delete(int $id): void
    {
        $subs = DB::count('subscriptions', 'plan_id = ?', [$id]);
        if ($subs > 0) {
            DB::update('plans', ['is_active' => 0], 'id = ?', [$id]);
        } else {
            DB::delete('plans', 'id = ?', [$id]);
        }
    }

    public static function getPrice(array $plan, string $cycle): float
    {
        return (float) match ($cycle) {
            'quarterly' => $plan['price_quarterly'],
            'yearly'    => $plan['price_yearly'],
            'lifetime'  => $plan['price_lifetime'],
            default     => $plan['price_monthly'],
        };
    }

    public static function subscriberCount(int $planId): int
    {
        return DB::count('subscriptions', "plan_id = ? AND status IN ('active','trial')", [$planId]);
    }
}
