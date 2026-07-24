<?php
declare(strict_types=1);

namespace App;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class Brochure
{
    public static function getAll(string $search = ''): array
    {
        $db = Database::get();
        if ($search) {
            $stmt = $db->prepare(
                'SELECT * FROM brochures
                 WHERE deceased_name LIKE ? OR funeral_location LIKE ? OR title LIKE ?
                 ORDER BY created_at DESC'
            );
            $like = "%{$search}%";
            $stmt->execute([$like, $like, $like]);
        } else {
            $stmt = $db->query('SELECT * FROM brochures ORDER BY created_at DESC');
        }
        return $stmt->fetchAll();
    }

    public static function count(): int
    {
        return (int) Database::get()->query('SELECT COUNT(*) FROM brochures')->fetchColumn();
    }

    public static function getById(int $id): ?array
    {
        $stmt = Database::get()->prepare('SELECT * FROM brochures WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function getBySlug(string $slug): ?array
    {
        $stmt = Database::get()->prepare('SELECT * FROM brochures WHERE slug = ?');
        $stmt->execute([$slug]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data, array $file): int
    {
        self::validateFields($data);
        $slug = self::generateSlug($data['deceased_name']);
        $pdfFilename = self::saveUpload($file);
        $qrFilename = self::generateQR($slug);

        $stmt = Database::get()->prepare(
            'INSERT INTO brochures (slug, deceased_name, funeral_location, title, pdf_filename, qr_filename)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $slug,
            trim($data['deceased_name']),
            trim($data['funeral_location']),
            trim($data['title'] ?? '') ?: null,
            $pdfFilename,
            $qrFilename,
        ]);
        return (int) Database::get()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        self::validateFields($data);
        $stmt = Database::get()->prepare(
            "UPDATE brochures SET deceased_name=?, funeral_location=?, title=?, updated_at=datetime('now') WHERE id=?"
        );
        $stmt->execute([
            trim($data['deceased_name']),
            trim($data['funeral_location']),
            trim($data['title'] ?? '') ?: null,
            $id,
        ]);
    }

    public static function replacePdf(int $id, array $file): void
    {
        $brochure = self::getById($id);
        if (!$brochure) throw new \RuntimeException('Brochure not found');

        $pdfFilename = self::saveUpload($file);

        // Delete old PDF after new one saved successfully
        @unlink(UPLOAD_DIR . '/' . $brochure['pdf_filename']);

        $stmt = Database::get()->prepare("UPDATE brochures SET pdf_filename=?, updated_at=datetime('now') WHERE id=?");
        $stmt->execute([$pdfFilename, $id]);
    }

    public static function delete(int $id): void
    {
        $brochure = self::getById($id);
        if (!$brochure) return;

        @unlink(UPLOAD_DIR . '/' . $brochure['pdf_filename']);
        @unlink(QR_DIR . '/' . $brochure['qr_filename']);

        $stmt = Database::get()->prepare('DELETE FROM brochures WHERE id = ?');
        $stmt->execute([$id]);
    }

    private static function validateFields(array $data): void
    {
        if (empty(trim($data['deceased_name'] ?? ''))) {
            throw new \RuntimeException('Deceased name is required');
        }
        if (empty(trim($data['funeral_location'] ?? ''))) {
            throw new \RuntimeException('Funeral location is required');
        }
    }

    private static function generateSlug(string $name): string
    {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');
        if (!$slug) $slug = 'brochure';

        $db = Database::get();
        $base = $slug;
        $counter = 1;
        while (true) {
            $stmt = $db->prepare('SELECT id FROM brochures WHERE slug = ?');
            $stmt->execute([$slug]);
            if (!$stmt->fetch()) break;
            $slug = $base . '-' . (++$counter);
        }
        return $slug;
    }

    private static function saveUpload(array $file): string
    {
        if (($file['error'] ?? 4) !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('PDF file is required');
        }
        if ($file['size'] > MAX_UPLOAD_SIZE) {
            throw new \RuntimeException('File too large (max 20MB)');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if ($mime !== 'application/pdf') {
            throw new \RuntimeException('Only PDF files are allowed');
        }

        $filename = bin2hex(random_bytes(16)) . '.pdf';
        if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . '/' . $filename)) {
            throw new \RuntimeException('Failed to save file');
        }
        return $filename;
    }

    private static function generateQR(string $slug): string
    {
        $url = APP_URL . '/brochure/' . $slug;
        $filename = $slug . '.png';

        $options = new QROptions([
            'outputType'       => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel'         => QRCode::ECC_L,
            'imageBase64'      => false,
            'scale'            => 10,
            'imageTransparent' => false,
        ]);

        $data = (new QRCode($options))->render($url);
        file_put_contents(QR_DIR . '/' . $filename, $data);
        return $filename;
    }
}
