<?php
declare(strict_types=1);

namespace App\Payment\Providers;

use App\Payment\ProviderInterface;

class PaystackProvider implements ProviderInterface
{
    private string $secretKey = '';
    private string $publicKey = '';

    public function getName(): string { return 'Paystack'; }
    public function getSlug(): string { return 'paystack'; }
    public function supportsSubscription(): bool { return true; }
    public function supportsEventPayment(): bool { return true; }

    public function initialize(array $credentials): void
    {
        $this->secretKey = $credentials['secret_key'] ?? '';
        $this->publicKey = $credentials['public_key'] ?? '';
    }

    public function initPayment(float $amount, string $currency, string $reference, string $email, string $callbackUrl, array $meta = []): array
    {
        $res = $this->request('POST', '/transaction/initialize', [
            'amount'       => (int)($amount * 100),
            'currency'     => $currency,
            'reference'    => $reference,
            'email'        => $email,
            'callback_url' => $callbackUrl,
            'metadata'     => $meta,
        ]);

        if (!($res['status'] ?? false)) {
            return ['success' => false, 'error' => $res['message'] ?? 'Init failed'];
        }

        return [
            'success'      => true,
            'redirect_url' => $res['data']['authorization_url'],
            'reference'    => $res['data']['reference'],
            'access_code'  => $res['data']['access_code'],
        ];
    }

    public function verifyPayment(string $reference): array
    {
        $res = $this->request('GET', '/transaction/verify/' . urlencode($reference));

        if (!($res['status'] ?? false)) {
            return ['success' => false, 'error' => $res['message'] ?? 'Verify failed'];
        }

        $data = $res['data'];
        return [
            'success'   => $data['status'] === 'success',
            'amount'    => $data['amount'] / 100,
            'currency'  => $data['currency'],
            'reference' => $data['reference'],
            'provider_reference' => $data['id'] ?? '',
            'paid_at'   => $data['paid_at'] ?? null,
            'channel'   => $data['channel'] ?? '',
            'metadata'  => $data['metadata'] ?? [],
        ];
    }

    public function handleWebhook(array $payload): array
    {
        $event = $payload['event'] ?? '';
        $data = $payload['data'] ?? [];

        return match ($event) {
            'charge.success' => [
                'type'      => 'payment_success',
                'reference' => $data['reference'] ?? '',
                'amount'    => ($data['amount'] ?? 0) / 100,
            ],
            default => ['type' => 'unknown', 'event' => $event],
        };
    }

    private function request(string $method, string $path, ?array $body = null): array
    {
        $url = 'https://api.paystack.co' . $path;
        $opts = [
            'http' => [
                'method' => $method,
                'header' => "Authorization: Bearer {$this->secretKey}\r\nContent-Type: application/json\r\n",
                'timeout' => 30,
            ],
        ];
        if ($body) {
            $opts['http']['content'] = json_encode($body);
        }
        $result = @file_get_contents($url, false, stream_context_create($opts));
        return $result ? json_decode($result, true) : ['status' => false, 'message' => 'Request failed'];
    }
}
