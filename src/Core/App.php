<?php
declare(strict_types=1);

namespace App\Core;

class App
{
    private static ?array $currentUser = null;
    private static ?array $currentTenant = null;
    private static ?array $currentPlan = null;

    public static function setUser(?array $user): void { self::$currentUser = $user; }
    public static function user(): ?array { return self::$currentUser; }

    public static function setTenant(?array $tenant): void
    {
        self::$currentTenant = $tenant;
        DB::setTenantId($tenant ? (int)$tenant['id'] : null);
    }
    public static function tenant(): ?array { return self::$currentTenant; }

    public static function setPlan(?array $plan): void { self::$currentPlan = $plan; }
    public static function plan(): ?array { return self::$currentPlan; }

    public static function isSuperAdmin(): bool
    {
        return self::$currentUser && self::$currentUser['role'] === 'super_admin';
    }

    public static function isTenantOwner(): bool
    {
        $u = self::$currentUser;
        return $u && in_array($u['role'], ['tenant_owner', 'tenant_admin']);
    }

    public static function tenantId(): ?int
    {
        return self::$currentTenant ? (int)self::$currentTenant['id'] : null;
    }

    public static function checkLimit(string $resource): bool
    {
        $plan = self::$currentPlan;
        if (!$plan) return false;
        $tenant = self::$currentTenant;
        if (!$tenant) return false;

        return match ($resource) {
            'events' => $plan['event_limit'] < 0 || DB::count('events', 'tenant_id = ?', [self::tenantId()]) < $plan['event_limit'],
            'storage' => $plan['storage_limit_mb'] < 0 || ($tenant['storage_used'] / 1048576) < $plan['storage_limit_mb'],
            'qr_codes' => $plan['qr_code_limit'] < 0 || DB::count('qr_codes', 'tenant_id = ?', [self::tenantId()]) < $plan['qr_code_limit'],
            'users' => $plan['user_limit'] < 0 || DB::count('users', 'tenant_id = ?', [self::tenantId()]) < $plan['user_limit'],
            default => true,
        };
    }

    public static function setting(string $key, string $default = ''): string
    {
        static $cache = [];
        if (!isset($cache[$key])) {
            $row = DB::fetchOne("SELECT setting_value FROM platform_settings WHERE setting_key = ?", [$key]);
            $cache[$key] = $row ? $row['setting_value'] : $default;
        }
        return $cache[$key] ?: $default;
    }

    public static function flash(string $message, string $type = 'success'): void
    {
        $_SESSION['flash'] = ['message' => $message, 'type' => $type];
    }

    public static function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrf(): void
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!hash_equals(self::csrfToken(), $token)) {
            http_response_code(403);
            die('Invalid security token.');
        }
    }

    public static function redirect(string $url, string $flash = '', string $type = 'success'): void
    {
        if ($flash) self::flash($flash, $type);
        header("Location: {$url}");
        exit;
    }

    public static function render(string $template, array $vars = [], string $layout = 'layouts/tenant'): void
    {
        $vars['csrfToken']    = self::csrfToken();
        $vars['currentUser']  = self::$currentUser;
        $vars['currentTenant'] = self::$currentTenant;
        $vars['currentPlan']  = self::$currentPlan;
        $vars['appName']      = self::setting('platform_name', APP_NAME);
        $vars['isSuperAdmin'] = self::isSuperAdmin();
        extract($vars);
        $f = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        ob_start();
        require BASE_DIR . "/templates/{$template}.php";
        $content = ob_get_clean();
        $pageTitle = $pageTitle ?? self::setting('platform_name', APP_NAME);
        require BASE_DIR . "/templates/{$layout}.php";
        exit;
    }

    public static function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public static function abort(int $code = 404, string $message = 'Not Found'): void
    {
        http_response_code($code);
        die($message);
    }

    public static function log(string $action, ?string $entityType = null, ?int $entityId = null, ?array $meta = null): void
    {
        try {
            DB::insert('activity_log', array_filter([
                'tenant_id'   => self::tenantId(),
                'user_id'     => self::$currentUser['id'] ?? null,
                'action'      => $action,
                'entity_type' => $entityType,
                'entity_id'   => $entityId,
                'metadata'    => $meta ? json_encode($meta) : null,
                'ip_address'  => $_SERVER['REMOTE_ADDR'] ?? null,
            ], fn($v) => $v !== null));
        } catch (\Exception $e) {}
    }
}
