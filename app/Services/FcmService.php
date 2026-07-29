<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FcmService
{
    public function __construct(protected Messaging $messaging) {}

    public function sendToUser(int $userId, string $title, string $body, array $data = []): void
    {
        $tokens = DB::table('fcm_tokens')
            ->where('user_id', $userId)
            ->pluck('token')
            ->toArray();

        if (empty($tokens)) return;

        foreach ($tokens as $token) {
            try {
                $message = CloudMessage::withTarget('token', $token)
                    ->withNotification(Notification::create($title, $body))
                    ->withData($data);

                $this->messaging->send($message);
            } catch (\Throwable $e) {
                Log::warning("FCM gonderilemedi (user:{$userId}): " . $e->getMessage());
                // Gecersiz token sil
                if (str_contains($e->getMessage(), 'NOT_FOUND') || str_contains($e->getMessage(), 'INVALID_ARGUMENT')) {
                    DB::table('fcm_tokens')->where('token', $token)->delete();
                }
            }
        }
    }

    public function sendToTenant(int $tenantId, string $title, string $body, array $data = []): void
    {
        // Tenant'in firma_sahibi ve sube_muduru kullanicilarina gonder
        $userIds = DB::table('users')
            ->where('tenant_id', $tenantId)
            ->whereIn('role', ['firma_sahibi', 'sube_muduru'])
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->pluck('id');

        foreach ($userIds as $userId) {
            $this->sendToUser($userId, $title, $body, $data);
        }
    }

    public function saveToken(int $userId, string $token): void
    {
        DB::table('fcm_tokens')->upsert(
            ['user_id' => $userId, 'token' => $token, 'device' => 'web', 'updated_at' => now(), 'created_at' => now()],
            ['user_id', 'token'],
            ['updated_at']
        );
    }

    public function deleteToken(string $token): void
    {
        DB::table('fcm_tokens')->where('token', $token)->delete();
    }
}
