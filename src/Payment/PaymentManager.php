<?php
declare(strict_types=1);

namespace App\Payment;

use App\Core\DB;

class PaymentManager
{
    private static array $providers = [];

    public static function register(string $slug, string $className): void
    {
        self::$providers[$slug] = $className;
    }

    public static function get(string $slug, ?array $credentials = null): ?ProviderInterface
    {
        $className = self::$providers[$slug] ?? null;
        if (!$className) {
            $row = DB::fetchOne("SELECT class_name FROM payment_providers WHERE slug = ?", [$slug]);
            if (!$row) return null;
            $className = 'App\\Payment\\Providers\\' . $row['class_name'];
        }
        if (!class_exists($className)) return null;

        $provider = new $className();
        if ($credentials) {
            $provider->initialize($credentials);
        }
        return $provider;
    }

    public static function getForTenant(string $slug, int $tenantId): ?ProviderInterface
    {
        $row = DB::fetchOne(
            "SELECT pp.class_name, tpp.credentials
             FROM payment_providers pp
             JOIN tenant_payment_providers tpp ON tpp.provider_id = pp.id
             WHERE pp.slug = ? AND tpp.tenant_id = ? AND tpp.is_active = 1 AND pp.is_active = 1",
            [$slug, $tenantId]
        );
        if (!$row) return null;

        $className = 'App\\Payment\\Providers\\' . $row['class_name'];
        if (!class_exists($className)) return null;

        $provider = new $className();
        $creds = json_decode($row['credentials'] ?: '{}', true);
        $provider->initialize($creds);
        return $provider;
    }

    public static function activeProviders(bool $platformOnly = false): array
    {
        if ($platformOnly) {
            return DB::fetchAll("SELECT * FROM payment_providers WHERE is_active = 1 ORDER BY sort_order");
        }
        return DB::fetchAll("SELECT * FROM payment_providers ORDER BY sort_order");
    }

    public static function tenantProviders(int $tenantId): array
    {
        return DB::fetchAll(
            "SELECT pp.*, tpp.is_active as tenant_active, tpp.credentials as tenant_credentials
             FROM payment_providers pp
             LEFT JOIN tenant_payment_providers tpp ON tpp.provider_id = pp.id AND tpp.tenant_id = ?
             WHERE pp.is_active = 1
             ORDER BY pp.sort_order",
            [$tenantId]
        );
    }

    public static function generateReference(string $prefix = 'EQR'): string
    {
        return $prefix . '-' . strtoupper(bin2hex(random_bytes(8)));
    }
}
