<?php

namespace App\Services\Notification;

use App\Services\Sms\SmsService;
use App\Services\WhatsApp\VatanWhatsAppService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function __construct(
        protected SmsService $smsService
    ) {}

    public function getSetting(int $tenantId, string $event): ?object
    {
        return DB::table('notification_settings')
            ->where('tenant_id', $tenantId)
            ->where('event', $event)
            ->first();
    }

    public static function fillTemplate(string $template, array $vars): string
    {
        foreach ($vars as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }
        return $template;
    }

    /**
     * auto modda WhatsApp VE SMS aynı anda gönderilir.
     * channel = 'auto'     → WhatsApp + SMS (her ikisi birden)
     * channel = 'whatsapp' → Yalnızca WhatsApp
     * channel = 'sms'      → Yalnızca SMS
     * channel = 'none'     → Hiçbiri
     */
    public function notify(
        int $tenantId,
        string $phone,
        string $message,
        string $type = 'general',
        ?int $customerId = null,
        string $channel = 'auto',
        ?string $event = null
    ): array {
        if ($event) {
            $setting = $this->getSetting($tenantId, $event);
            if ($setting) {
                if (!$setting->enabled) {
                    return ['success' => false, 'channel' => 'disabled'];
                }
                if ($setting->channel !== 'auto') {
                    $channel = $setting->channel;
                }
            }
        }

        if ($channel === 'none') {
            return ['success' => false, 'channel' => 'none'];
        }

        $wpSent  = false;
        $smsSent = false;

        if (in_array($channel, ['auto', 'whatsapp'])) {
            try {
                $wpSent = $this->sendWhatsApp($tenantId, $phone, $message, $type, $customerId);
            } catch (\Throwable $e) {
                Log::warning('WhatsApp gonderim hatasi: ' . $e->getMessage());
            }
        }

        // auto modda WhatsApp sonucundan bağımsız SMS de gönderilir
        if (in_array($channel, ['auto', 'sms'])) {
            try {
                $smsSent = $this->smsService->sendToCustomer($tenantId, $phone, $message, $type, $customerId);
            } catch (\Throwable $e) {
                Log::warning('SMS gonderim hatasi: ' . $e->getMessage());
            }
        }

        $sentChannels = array_filter([
            $wpSent  ? 'whatsapp' : null,
            $smsSent ? 'sms'      : null,
        ]);

        return [
            'success'  => $wpSent || $smsSent,
            'channel'  => implode('+', $sentChannels) ?: 'none',
            'whatsapp' => $wpSent,
            'sms'      => $smsSent,
        ];
    }

    protected function sendWhatsApp(
        int $tenantId,
        string $phone,
        string $message,
        string $type,
        ?int $customerId
    ): bool {
        $connection = DB::table('whatsapp_connections')
            ->where('tenant_id', $tenantId)
            ->where('status', 'connected')
            ->first();

        if (!$connection) {
            $connection = DB::table('whatsapp_connections')
                ->whereNull('tenant_id')
                ->where('status', 'connected')
                ->first();
        }

        if (!$connection) {
            return false;
        }

        $whatsAppService = new VatanWhatsAppService();
        $result = $whatsAppService->send($connection->reg_id, $phone, $message);

        $this->logWhatsApp(
            $tenantId, $customerId, $phone, $message, $type,
            $result['success'] ? 'sent' : 'failed',
            $result['response'] ?? null
        );

        return $result['success'];
    }

    protected function logWhatsApp(
        int $tenantId,
        ?int $customerId,
        string $phone,
        string $message,
        string $type,
        string $status,
        ?array $response
    ): void {
        try {
            DB::table('whatsapp_logs')->insert([
                'tenant_id'   => $tenantId,
                'customer_id' => $customerId,
                'phone'       => $phone,
                'message'     => $message,
                'type'        => $type,
                'status'      => $status,
                'response'    => $response ? json_encode($response) : null,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('WhatsApp log hatasi: ' . $e->getMessage());
        }
    }
}
