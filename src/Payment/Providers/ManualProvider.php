<?php
declare(strict_types=1);

namespace App\Payment\Providers;

use App\Payment\ProviderInterface;

class ManualProvider implements ProviderInterface
{
    private array $config = [];

    public function getName(): string { return 'Manual Payment'; }
    public function getSlug(): string { return 'manual'; }
    public function supportsSubscription(): bool { return true; }
    public function supportsEventPayment(): bool { return true; }

    public function initialize(array $credentials): void
    {
        $this->config = $credentials;
    }

    public function initPayment(float $amount, string $currency, string $reference, string $email, string $callbackUrl, array $meta = []): array
    {
        return [
            'success'      => true,
            'type'         => 'manual',
            'reference'    => $reference,
            'instructions' => $this->config['instructions'] ?? 'Please contact the administrator to complete payment.',
            'bank_name'    => $this->config['bank_name'] ?? '',
            'account_number' => $this->config['account_number'] ?? '',
            'account_name' => $this->config['account_name'] ?? '',
        ];
    }

    public function verifyPayment(string $reference): array
    {
        return ['success' => false, 'error' => 'Manual payments require admin verification'];
    }

    public function handleWebhook(array $payload): array
    {
        return ['type' => 'unsupported'];
    }
}
