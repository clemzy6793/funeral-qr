<?php
declare(strict_types=1);

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;

class EmailService
{
    public static function send(string $to, string $subject, string $html, ?string $text = null): bool
    {
        if (!SMTP_HOST || !SMTP_USER) {
            error_log("[EMAIL DEV] To: {$to} | Subject: {$subject}");
            return true;
        }

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = SMTP_PORT;
            $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = self::wrapHtml($subject, $html);
            if ($text) $mail->AltBody = $text;
            $mail->send();
            return true;
        } catch (\Exception $e) {
            error_log("[EMAIL ERROR] {$e->getMessage()}");
            return false;
        }
    }

    public static function sendVerification(string $to, string $name, string $token): bool
    {
        $url = APP_URL . '/verify?token=' . $token;
        return self::send($to, 'Verify Your Email — ' . APP_NAME,
            "<p>Hi {$name},</p><p>Please verify your email address by clicking the button below:</p>
             <p style='text-align:center'><a href='{$url}' style='display:inline-block;padding:12px 32px;background:#212529;color:#fff;text-decoration:none;border-radius:6px;font-weight:600'>Verify Email</a></p>
             <p style='color:#666;font-size:13px'>Or copy this link: {$url}</p>",
            "Hi {$name}, verify your email: {$url}"
        );
    }

    public static function sendPasswordReset(string $to, string $name, string $token): bool
    {
        $url = APP_URL . '/reset-password?token=' . $token;
        return self::send($to, 'Reset Your Password — ' . APP_NAME,
            "<p>Hi {$name},</p><p>You requested a password reset. Click the button below:</p>
             <p style='text-align:center'><a href='{$url}' style='display:inline-block;padding:12px 32px;background:#212529;color:#fff;text-decoration:none;border-radius:6px;font-weight:600'>Reset Password</a></p>
             <p style='color:#666;font-size:13px'>This link expires in 1 hour. If you didn't request this, ignore this email.</p>",
            "Hi {$name}, reset your password: {$url}"
        );
    }

    public static function sendWelcome(string $to, string $name, string $planName): bool
    {
        return self::send($to, "Welcome to " . APP_NAME,
            "<p>Hi {$name},</p><p>Welcome! Your account has been created on the <strong>{$planName}</strong> plan.</p>
             <p><a href='" . APP_URL . "/login' style='display:inline-block;padding:12px 32px;background:#212529;color:#fff;text-decoration:none;border-radius:6px;font-weight:600'>Go to Dashboard</a></p>",
            "Hi {$name}, welcome to " . APP_NAME . "! Your {$planName} plan is ready."
        );
    }

    private static function wrapHtml(string $title, string $body): string
    {
        $appName = htmlspecialchars(APP_NAME);
        return "<!DOCTYPE html><html><head><meta charset='utf-8'><title>{$title}</title></head>
        <body style='margin:0;padding:0;background:#f4f5f7;font-family:Arial,sans-serif'>
        <div style='max-width:600px;margin:24px auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.08)'>
        <div style='background:#212529;padding:20px 24px;color:#fff;font-weight:700;font-size:18px'>{$appName}</div>
        <div style='padding:24px 24px 32px'>{$body}</div>
        <div style='padding:16px 24px;background:#f9fafb;color:#999;font-size:12px;text-align:center;border-top:1px solid #eee'>&copy; " . date('Y') . " {$appName}</div>
        </div></body></html>";
    }
}
