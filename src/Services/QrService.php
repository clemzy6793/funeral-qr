<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\DB;
use App\Models\QrCode;
use chillerlan\QRCode\QRCode as QRCodeLib;
use chillerlan\QRCode\QROptions;

class QrService
{
    public static function generate(int $tenantId, int $eventId, string $slug, string $label): int
    {
        $code = DB::uuid();
        $url = APP_URL . '/qr/' . $code;
        $filename = $code . '.png';

        self::renderQrImage($url, $label, QR_DIR . '/' . $filename);

        return QrCode::create([
            'tenant_id' => $tenantId,
            'event_id'  => $eventId,
            'code'      => $code,
            'filename'  => $filename,
            'url'       => $url,
            'format'    => 'png',
        ]);
    }

    public static function regenerate(int $qrId, string $label): void
    {
        $qr = QrCode::find($qrId);
        if (!$qr) return;
        self::renderQrImage($qr['url'], $label, QR_DIR . '/' . $qr['filename']);
    }

    public static function renderQrImage(string $url, string $label, string $outputPath): void
    {
        $options = new QROptions([
            'outputType'       => QRCodeLib::OUTPUT_IMAGE_PNG,
            'eccLevel'         => QRCodeLib::ECC_L,
            'imageBase64'      => false,
            'scale'            => 10,
            'imageTransparent' => false,
        ]);

        $qrData = (new QRCodeLib($options))->render($url);
        $qrImg  = imagecreatefromstring($qrData);
        $qrW    = imagesx($qrImg);
        $qrH    = imagesy($qrImg);

        $font = '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';
        $fontAvailable = file_exists($font);
        $ptSize = 18;
        $padding = 20;

        if ($fontAvailable) {
            $bbox   = imagettfbbox($ptSize, 0, $font, $label);
            $textW  = $bbox[2] - $bbox[0];
            $textH  = $bbox[1] - $bbox[7];
        } else {
            $textW = imagefontwidth(5) * strlen($label);
            $textH = imagefontheight(5);
        }

        $canvasW = max($qrW, $textW + 40);
        $canvasH = $qrH + $textH + $padding * 2;

        $canvas = imagecreatetruecolor($canvasW, $canvasH);
        $white  = imagecolorallocate($canvas, 255, 255, 255);
        $black  = imagecolorallocate($canvas, 30, 30, 30);
        imagefill($canvas, 0, 0, $white);

        $qrX = (int)(($canvasW - $qrW) / 2);
        imagecopy($canvas, $qrImg, $qrX, 0, 0, 0, $qrW, $qrH);

        if ($fontAvailable) {
            $textX = (int)(($canvasW - $textW) / 2);
            $textY = $qrH + $padding + $textH;
            imagettftext($canvas, $ptSize, 0, $textX, $textY, $black, $font, $label);
        } else {
            $textX = (int)(($canvasW - $textW) / 2);
            $textY = $qrH + $padding;
            imagestring($canvas, 5, $textX, $textY, $label, $black);
        }

        imagepng($canvas, $outputPath);
        imagedestroy($qrImg);
        imagedestroy($canvas);
    }

    public static function serveQrImage(string $filename, bool $download = false, string $downloadName = 'qr.png'): void
    {
        $path = QR_DIR . '/' . $filename;
        if (!file_exists($path)) {
            http_response_code(404);
            die('QR not found');
        }
        header('Content-Type: image/png');
        if ($download) {
            header('Content-Disposition: attachment; filename="' . preg_replace('/[^a-zA-Z0-9._-]/', '', $downloadName) . '"');
        }
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }
}
