<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\DB;

class Media
{
    public static function create(array $data): int
    {
        return DB::insert('media', $data);
    }

    public static function find(int $id): ?array
    {
        return DB::fetchOne("SELECT * FROM media WHERE id = ?", [$id]);
    }

    public static function delete(int $id): void
    {
        $media = self::find($id);
        if ($media) {
            $path = BASE_DIR . '/' . $media['file_path'];
            if (file_exists($path)) @unlink($path);
            DB::delete('media', 'id = ?', [$id]);
        }
    }

    public static function byEvent(int $eventId, ?string $type = null): array
    {
        if ($type) {
            return DB::fetchAll("SELECT * FROM media WHERE event_id = ? AND type = ? ORDER BY sort_order, created_at", [$eventId, $type]);
        }
        return DB::fetchAll("SELECT * FROM media WHERE event_id = ? ORDER BY sort_order, created_at", [$eventId]);
    }

    public static function byTenant(int $tenantId): array
    {
        return DB::fetchAll("SELECT * FROM media WHERE tenant_id = ? ORDER BY created_at DESC", [$tenantId]);
    }

    public static function storageUsed(int $tenantId): int
    {
        $row = DB::fetchOne("SELECT COALESCE(SUM(file_size), 0) as total FROM media WHERE tenant_id = ?", [$tenantId]);
        return (int) ($row['total'] ?? 0);
    }

    public static function saveUpload(array $file, int $tenantId, ?int $eventId, string $type = 'other'): int
    {
        if (($file['error'] ?? 4) !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('File upload failed');
        }
        if ($file['size'] > MAX_UPLOAD_SIZE) {
            throw new \RuntimeException('File too large (max 50MB)');
        }

        $year = date('Y');
        $month = date('m');
        $dir = "storage/uploads/{$year}/{$month}/{$tenantId}";
        $fullDir = BASE_DIR . '/' . $dir;
        if (!is_dir($fullDir)) mkdir($fullDir, 0775, true);

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'bin';
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $path = $dir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], BASE_DIR . '/' . $path)) {
            throw new \RuntimeException('Failed to save file');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file(BASE_DIR . '/' . $path);

        return self::create([
            'tenant_id'     => $tenantId,
            'event_id'      => $eventId,
            'type'          => $type,
            'filename'      => $filename,
            'original_name' => $file['name'],
            'mime_type'     => $mime,
            'file_size'     => $file['size'],
            'file_path'     => $path,
        ]);
    }
}
