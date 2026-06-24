<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VatanSmsService
{
    protected string $apiId;
    protected string $apiKey;
    protected string $sender;
    protected string $apiUrl = 'https://api.vatansms.net/api/v1';

    public function __construct(array $credentials)
    {
        $this->apiId = $credentials['username'];
        $this->apiKey = $credentials['password'];
        $this->sender = $credentials['sender'] ?? 'VATANSMS';
    }

    public function send(string $phone, string $message): array
    {
        try {
            $phone = $this->normalizePhone($phone);

            $response = Http::timeout(15)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("{$this->apiUrl}/1toN", [
                    'api_id' => $this->apiId,
                    'api_key' => $this->apiKey,
                    'sender' => $this->sender,
                    'message_type' => 'turkce',
                    'message' => $message,
                    'message_content_type' => 'bilgi',
                    'phones' => [$phone],
                ]);

            $body = $response->json();

            $success = $response->successful()
                && (
                    (isset($body['status']) && in_array($body['status'], ['success', true, 1]))
                    || isset($body['report_id'])
                );

            return [
                'success' => $success,
                'provider' => 'vatansms',
                'response' => $body,
                'report_id' => $body['report_id'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::error('VatanSMS hatasi: ' . $e->getMessage());
            return [
                'success' => false,
                'provider' => 'vatansms',
                'response' => ['error' => $e->getMessage()],
            ];
        }
    }

    public function sendBulk(array $phones, string $message): array
    {
        try {
            $phones = array_map([$this, 'normalizePhone'], $phones);

            $response = Http::timeout(30)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("{$this->apiUrl}/1toN", [
                    'api_id' => $this->apiId,
                    'api_key' => $this->apiKey,
                    'sender' => $this->sender,
                    'message_type' => 'turkce',
                    'message' => $message,
                    'message_content_type' => 'bilgi',
                    'phones' => $phones,
                ]);

            $body = $response->json();
            $success = $response->successful() && (isset($body['report_id']) || (isset($body['status']) && $body['status'] === 'success'));

            return [
                'success' => $success,
                'provider' => 'vatansms',
                'response' => $body,
                'sent_count' => $success ? count($phones) : 0,
            ];

        } catch (\Exception $e) {
            Log::error('VatanSMS toplu gonderim hatasi: ' . $e->getMessage());
            return [
                'success' => false,
                'provider' => 'vatansms',
                'response' => ['error' => $e->getMessage()],
                'sent_count' => 0,
            ];
        }
    }

    public function getBalance(): array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("{$this->apiUrl}/user/information", [
                    'api_id' => $this->apiId,
                    'api_key' => $this->apiKey,
                ]);

            $body = $response->json();

            return [
                'success' => $response->successful(),
                'balance' => $body['credit'] ?? $body['balance'] ?? 0,
                'response' => $body,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'balance' => 0,
                'response' => ['error' => $e->getMessage()],
            ];
        }
    }

    public function getSenders(): array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("{$this->apiUrl}/senders", [
                    'api_id' => $this->apiId,
                    'api_key' => $this->apiKey,
                ]);

            return [
                'success' => $response->successful(),
                'response' => $response->json(),
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'response' => ['error' => $e->getMessage()]];
        }
    }

    protected function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        if (str_starts_with($digits, '90')) {
            return $digits;
        }
        if (str_starts_with($digits, '0')) {
            return '90' . substr($digits, 1);
        }
        if (str_starts_with($digits, '5')) {
            return '90' . $digits;
        }

        return $digits;
    }
}
