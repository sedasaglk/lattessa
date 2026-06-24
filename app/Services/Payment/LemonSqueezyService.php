<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;

class LemonSqueezyService
{
    protected string $apiKey;
    protected string $storeId;
    protected string $baseUrl = 'https://api.lemonsqueezy.com/v1';

    public function __construct()
    {
        $this->apiKey = config('lemonsqueezy.api_key');
        $this->storeId = config('lemonsqueezy.store_id');
    }

    protected function http()
    {
        return Http::withToken($this->apiKey)
            ->withHeaders(['Accept' => 'application/vnd.api+json'])
            ->baseUrl($this->baseUrl);
    }

    public function createCheckoutUrl(string $variantId, array $customerData, string $tenantSlug): string
    {
        $response = $this->http()->post('/checkouts', [
            'data' => [
                'type' => 'checkouts',
                'attributes' => [
                    'checkout_data' => [
                        'email' => $customerData['email'],
                        'name' => $customerData['name'],
                        'custom' => [
                            'tenant_slug' => (string) $tenantSlug,
                            'tenant_id' => (string) $customerData['tenant_id'],
                        ],
                    ],
                    'product_options' => [
                        'redirect_url' => url("/{$tenantSlug}/abonelik?success=1"),
                        'receipt_link_url' => url("/{$tenantSlug}/abonelik"),
                    ],
                ],
                'relationships' => [
                    'store' => [
                        'data' => ['type' => 'stores', 'id' => (string) $this->storeId],
                    ],
                    'variant' => [
                        'data' => ['type' => 'variants', 'id' => (string) $variantId],
                    ],
                ],
            ],
        ]);

        if (!$response->successful()) {
            throw new \Exception('Lemon Squeezy checkout olusturulamadi: ' . $response->body());
        }

        return $response->json('data.attributes.url');
    }

    public function verifyWebhook(string $payload, string $signature): bool
    {
        $secret = config('lemonsqueezy.webhook_secret');
        $computed = hash_hmac('sha256', $payload, $secret);
        return hash_equals($computed, $signature);
    }

    public function cancelSubscription(string $subscriptionId): bool
    {
        $response = $this->http()->delete("/subscriptions/{$subscriptionId}");
        return $response->successful();
    }
}
