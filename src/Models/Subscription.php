<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\DB;

class Subscription
{
    public static function create(int $tenantId, int $planId, string $cycle = 'monthly', bool $isTrial = false): int
    {
        $plan = Plan::find($planId);
        $now = date('Y-m-d H:i:s');
        $data = [
            'tenant_id'     => $tenantId,
            'plan_id'       => $planId,
            'billing_cycle' => $cycle,
            'starts_at'     => $now,
        ];

        if ($isTrial && $plan && $plan['trial_days'] > 0) {
            $data['status'] = 'trial';
            $data['trial_ends_at'] = date('Y-m-d H:i:s', strtotime("+{$plan['trial_days']} days"));
            $data['ends_at'] = $data['trial_ends_at'];
        } elseif ($plan && Plan::getPrice($plan, $cycle) == 0) {
            $data['status'] = 'active';
            $data['ends_at'] = null;
        } else {
            $data['status'] = 'active';
            $data['ends_at'] = self::calcEndDate($cycle);
        }

        return DB::insert('subscriptions', $data);
    }

    public static function find(int $id): ?array
    {
        return DB::fetchOne(
            "SELECT s.*, p.name as plan_name, p.slug as plan_slug FROM subscriptions s JOIN plans p ON p.id = s.plan_id WHERE s.id = ?",
            [$id]
        );
    }

    public static function active(int $tenantId): ?array
    {
        return DB::fetchOne(
            "SELECT s.*, p.* FROM subscriptions s
             JOIN plans p ON p.id = s.plan_id
             WHERE s.tenant_id = ? AND s.status IN ('active','trial')
             ORDER BY s.created_at DESC LIMIT 1",
            [$tenantId]
        );
    }

    public static function renew(int $subId): void
    {
        $sub = self::find($subId);
        if (!$sub) return;
        DB::update('subscriptions', [
            'status'   => 'active',
            'starts_at' => date('Y-m-d H:i:s'),
            'ends_at'  => self::calcEndDate($sub['billing_cycle']),
        ], 'id = ?', [$subId]);
    }

    public static function suspend(int $subId): void
    {
        DB::update('subscriptions', ['status' => 'suspended'], 'id = ?', [$subId]);
    }

    public static function cancel(int $subId): void
    {
        DB::update('subscriptions', ['status' => 'cancelled', 'cancelled_at' => date('Y-m-d H:i:s')], 'id = ?', [$subId]);
    }

    public static function getExpiring(int $days = 7): array
    {
        $date = date('Y-m-d H:i:s', strtotime("+{$days} days"));
        return DB::fetchAll(
            "SELECT s.*, t.name as tenant_name, t.email as tenant_email, p.name as plan_name
             FROM subscriptions s
             JOIN tenants t ON t.id = s.tenant_id
             JOIN plans p ON p.id = s.plan_id
             WHERE s.status IN ('active','trial') AND s.ends_at IS NOT NULL AND s.ends_at <= ?",
            [$date]
        );
    }

    public static function getExpired(): array
    {
        return DB::fetchAll(
            "SELECT s.*, t.name as tenant_name, t.email as tenant_email
             FROM subscriptions s
             JOIN tenants t ON t.id = s.tenant_id
             WHERE s.status IN ('active','trial') AND s.ends_at IS NOT NULL AND s.ends_at < NOW()"
        );
    }

    private static function calcEndDate(string $cycle): string
    {
        return match ($cycle) {
            'quarterly' => date('Y-m-d H:i:s', strtotime('+3 months')),
            'yearly'    => date('Y-m-d H:i:s', strtotime('+1 year')),
            'lifetime'  => date('Y-m-d H:i:s', strtotime('+100 years')),
            default     => date('Y-m-d H:i:s', strtotime('+1 month')),
        };
    }
}
