<?php
declare(strict_types=1);

namespace App\Payment;

interface ProviderInterface
{
    public function getName(): string;
    public function getSlug(): string;
    public function initialize(array $credentials): void;
    public function initPayment(float $amount, string $currency, string $reference, string $email, string $callbackUrl, array $meta = []): array;
    public function verifyPayment(string $reference): array;
    public function handleWebhook(array $payload): array;
    public function supportsSubscription(): bool;
    public function supportsEventPayment(): bool;
}
