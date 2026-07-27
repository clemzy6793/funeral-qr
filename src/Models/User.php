<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\DB;

class User
{
    public static function create(array $data): int
    {
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
            unset($data['password']);
        }
        return DB::insert('users', $data);
    }

    public static function find(int $id): ?array
    {
        return DB::fetchOne("SELECT * FROM users WHERE id = ?", [$id]);
    }

    public static function findByEmail(string $email): ?array
    {
        return DB::fetchOne("SELECT * FROM users WHERE email = ?", [$email]);
    }

    public static function findByToken(string $field, string $token): ?array
    {
        return DB::fetchOne("SELECT * FROM users WHERE {$field} = ?", [$token]);
    }

    public static function update(int $id, array $data): void
    {
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
            unset($data['password']);
        }
        DB::update('users', $data, 'id = ?', [$id]);
    }

    public static function delete(int $id): void
    {
        DB::delete('users', 'id = ?', [$id]);
    }

    public static function attempt(string $email, string $password): ?array
    {
        $user = self::findByEmail($email);
        if (!$user) return null;
        if (!$user['is_active']) return null;
        if (!password_verify($password, $user['password_hash'])) return null;
        DB::update('users', ['last_login_at' => date('Y-m-d H:i:s')], 'id = ?', [$user['id']]);
        return $user;
    }

    public static function byTenant(int $tenantId, string $search = ''): array
    {
        $where = 'tenant_id = ?';
        $params = [$tenantId];
        if ($search) {
            $where .= ' AND (name LIKE ? OR email LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        return DB::fetchAll("SELECT * FROM users WHERE {$where} ORDER BY created_at DESC", $params);
    }

    public static function superAdmins(): array
    {
        return DB::fetchAll("SELECT * FROM users WHERE role = 'super_admin' ORDER BY name");
    }

    public static function generateVerificationToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    public static function generateResetToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}
