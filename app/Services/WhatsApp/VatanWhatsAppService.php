<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VatanWhatsAppService
{
    protected string $clientId;
    protected string $clientSecret;
    protected string $apiUrl = 'https://api.vatansms.com/api/whatsapp/v1';

    public function __construct(?string $clientId = null, ?string $clientSecret = null)
    {
        $this->clientId = $clientId ?? config('services.vatan_whatsapp.client_id');
        $this->clientSecret = $clientSecret ?? config('services.vatan_whatsapp.client_secret');
    }

    protected function headers(): array
    {
        return [
            'client-id' => $this->clientId,
            'client-secret' => $this->clientSecret,
            'Content-Type' => 'application/json',
        ];
    }

    public function loginWithPhone(string $phone): array
    {
        try {
            $response = Http::timeout(15)
                ->withHeaders($this->headers())
                ->post("{$this->apiUrl}/login/phone", [
                    'phone' => $this->normalizePhone($phone),
                ]);

            $body = $response->json();

            return [
                'success' => $response->successful() && ($body['status'] ?? '') === 'success',
                'response' => $body,
                'reg_id' => $body['data']['regId'] ?? null,
                'code' => $body['data']['code'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp login/phone hatasi: ' . $e->getMessage());
            return ['success' => false, 'response' => ['error' => $e->getMessage()]];
        }
    }

    public function loginWithQr(): array
    {
        try {
            $response = Http::timeout(15)
                ->withHeaders($this->headers())
                ->post("{$this->apiUrl}/login/qr");

            $body = $response->json();

            return [
                'success' => $response->successful() && ($body['status'] ?? '') === 'success',
                'response' => $body,
                'reg_id' => $body['data']['regId'] ?? null,
                'qr' => $body['data']['qr'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp login/qr hatasi: ' . $e->getMessage());
            return ['success' => false, 'response' => ['error' => $e->getMessage()]];
        }
    }

    public function checkActiveLogin(string $regId): array
    {
        try {
            $response = Http::timeout(15)
                ->withHeaders($this->headers())
                ->post("{$this->apiUrl}/login/check/active", [
                    'reg_id' => $regId,
                ]);

            $body = $response->json();

            return [
                'success' => $response->successful(),
                'is_connected' => $body['data']['data']['is_connect'] ?? $body['data']['is_connect'] ?? $body['is_connect'] ?? false,
                'response' => $body,
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'is_connected' => false, 'response' => ['error' => $e->getMessage()]];
        }
    }

    public function checkLogin(string $regId): array
    {
        try {
            $response = Http::timeout(35)
                ->withHeaders($this->headers())
                ->post("{$this->apiUrl}/login/check", [
                    'reg_id' => $regId,
                ]);

            $body = $response->json();

            return [
                'success' => $response->successful(),
                'is_connected' => $body['data']['data']['is_connect'] ?? $body['data']['is_connect'] ?? false,
                'response' => $body,
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'is_connected' => false, 'response' => ['error' => $e->getMessage()]];
        }
    }

    public function send(string $regId, string $phone, string $message, ?int $reportId = null): array
    {
        try {
            $payload = [
                'reg_id' => $regId,
                'to' => $this->normalizePhone($phone, true),
                'message' => $message,
                'send_speed' => 1,
            ];
            if ($reportId) $payload['report_id'] = $reportId;

            $response = Http::timeout(20)
                ->withHeaders($this->headers())
                ->post("{$this->apiUrl}/messages/send", $payload);

            $body = $response->json();

            return [
                'success' => $response->successful() && ($body['status'] ?? '') === 'success',
                'response' => $body,
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp gonderim hatasi: ' . $e->getMessage());
            return ['success' => false, 'response' => ['error' => $e->getMessage()]];
        }
    }

    public function getDevices(): array
    {
        try {
            $response = Http::timeout(15)
                ->withHeaders($this->headers())
                ->get("{$this->apiUrl}/devices");

            return [
                'success' => $response->successful(),
                'response' => $response->json(),
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'response' => ['error' => $e->getMessage()]];
        }
    }

    public function logout(string $regId): array
    {
        try {
            $response = Http::timeout(15)
                ->withHeaders($this->headers())
                ->post("{$this->apiUrl}/logout", ['reg_id' => $regId]);

            return [
                'success' => $response->successful(),
                'response' => $response->json(),
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'response' => ['error' => $e->getMessage()]];
        }
    }

    protected function normalizePhone(string $phone, bool $withPlus = false): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        if (str_starts_with($digits, '0')) {
            $digits = '90' . substr($digits, 1);
        } elseif (str_starts_with($digits, '5')) {
            $digits = '90' . $digits;
        }

        return $withPlus ? '+' . $digits : $digits;
    }
}
