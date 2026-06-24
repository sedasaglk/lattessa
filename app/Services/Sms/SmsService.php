<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public function sendToCustomer(
        int $tenantId,
        string $phone,
        string $message,
        string $type = 'general',
        ?int $customerId = null
    ): bool {
        $provider = $this->getProvider($tenantId);

        if (!$provider) {
            Log::warning("SMS saglayici bulunamadi. Tenant: {$tenantId}");
            $this->logSms($tenantId, $customerId, $phone, $message, $type, null, 'failed', null);
            return false;
        }

        $credentials = json_decode($provider->credentials, true);
        $smsClient = new VatanSmsService($credentials);

        $result = $smsClient->send($phone, $message);

        $this->logSms(
            $tenantId,
            $customerId,
            $phone,
            $message,
            $type,
            $provider->provider,
            $result['success'] ? 'sent' : 'failed',
            $result['response']
        );

        return $result['success'];
    }

    public function sendBulk(int $tenantId, array $recipients, string $message, string $type = 'campaign'): array
    {
        $provider = $this->getProvider($tenantId);

        if (!$provider) {
            return ['success' => false, 'sent' => 0, 'failed' => count($recipients)];
        }

        $credentials = json_decode($provider->credentials, true);
        $smsClient = new VatanSmsService($credentials);

        $phones = array_column($recipients, 'phone');
        $result = $smsClient->sendBulk($phones, $message);

        foreach ($recipients as $recipient) {
            $this->logSms(
                $tenantId,
                $recipient['customer_id'] ?? null,
                $recipient['phone'],
                $message,
                $type,
                $provider->provider,
                $result['success'] ? 'sent' : 'failed',
                null
            );
        }

        return [
            'success' => $result['success'],
            'sent' => $result['sent_count'] ?? 0,
            'failed' => count($recipients) - ($result['sent_count'] ?? 0),
        ];
    }

    public function getBalance(int $tenantId): array
    {
        $provider = $this->getProvider($tenantId);

        if (!$provider) {
            return ['success' => false, 'balance' => 0];
        }

        $credentials = json_decode($provider->credentials, true);
        $smsClient = new VatanSmsService($credentials);

        return $smsClient->getBalance();
    }

    protected function getProvider(int $tenantId): ?object
    {
        $provider = DB::table('sms_providers')
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('priority')
            ->first();

        if ($provider) return $provider;

        return DB::table('sms_providers')
            ->whereNull('tenant_id')
            ->where('is_active', true)
            ->where('is_system_default', true)
            ->first()
            ?? DB::table('sms_providers')
                ->whereNull('tenant_id')
                ->where('is_active', true)
                ->orderBy('priority')
                ->first();
    }

    protected function logSms(
        int $tenantId,
        ?int $customerId,
        string $phone,
        string $message,
        string $type,
        ?string $provider,
        string $status,
        ?array $response
    ): void {
        try {
            DB::table('sms_logs')->insert([
                'tenant_id' => $tenantId,
                'customer_id' => $customerId,
                'phone' => $phone,
                'message' => $message,
                'type' => $type,
                'provider' => $provider,
                'status' => $status,
                'provider_response' => $response ? json_encode($response) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('SMS log hatasi: ' . $e->getMessage());
        }
    }
}
