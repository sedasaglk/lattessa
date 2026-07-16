<?php

namespace App\Services\Notification;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    protected string $expoApiUrl = 'https://exp.host/--/api/v2/push/send';

    public function sendToTenant(int $tenantId, string $title, string $body, array $data = []): bool
    {
        $tokens = $this->getTenantTokens($tenantId);
        if (empty($tokens)) return false;

        return $this->send($tokens, $title, $body, $data);
    }

    public function sendToUser(int $userId, string $title, string $body, array $data = []): bool
    {
        $tokens = $this->getUserTokens($userId);
        if (empty($tokens)) return false;

        return $this->send($tokens, $title, $body, $data);
    }

    public function send(array $tokens, string $title, string $body, array $data = []): bool
    {
        try {
            $messages = array_map(fn($token) => [
                'to' => $token,
                'title' => $title,
                'body' => $body,
                'data' => $data,
                'sound' => 'default',
                'badge' => 1,
                'channelId' => 'default',
            ], $tokens);

            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Accept-Encoding' => 'gzip, deflate',
            ])->post($this->expoApiUrl, $messages);

            $result = $response->json();
            Log::info('Push notification gonderildi', ['tokens' => count($tokens), 'result' => $result]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Push notification hatasi: ' . $e->getMessage());
            return false;
        }
    }

    protected function getTenantTokens(int $tenantId): array
    {
        return DB::table('push_tokens')
            ->join('users', 'push_tokens.user_id', '=', 'users.id')
            ->where('users.tenant_id', $tenantId)
            ->where('push_tokens.is_active', true)
            ->pluck('push_tokens.token')
            ->toArray();
    }

    protected function getUserTokens(int $userId): array
    {
        return DB::table('push_tokens')
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->pluck('token')
            ->toArray();
    }

    public function saveToken(int $userId, string $token): void
    {
        DB::table('push_tokens')->upsert([
            'user_id' => $userId,
            'token' => $token,
            'is_active' => true,
            'updated_at' => now(),
            'created_at' => now(),
        ], ['user_id', 'token'], ['is_active', 'updated_at']);
    }
}
