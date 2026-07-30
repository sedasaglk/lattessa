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

    /**
     * Bildirim ayarlarını getir (cache ile)
     */
    public function getSetting(int $tenantId, string $event): ?object
    {
        return DB::table('notification_settings')
            ->where('tenant_id', $tenantId)
            ->where('event', $event)
            ->first();
    }

    /**
     * Event için şablon değişkenlerini doldur
     */
    public static function fillTemplate(string $template, array $vars): string
    {
        foreach ($vars as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }
        return $template;
    }

    /**
     * Mesaji oncelikle WhatsApp uzerinden, baglanti yoksa SMS uzerinden gonderir.
     * $event verilirse notification_settings tablosundan kanal ve enabled kontrolü yapılır.
     */
    public function notify(
        int $tenantId,
        string $phone,
        string $message,
        string $type = 'general',
        ?int $customerId = null,
        string $channel = 'auto', // auto, whatsapp, sms
        ?string $event = null
    ): array {
        // Bildirim ayarı kontrolü
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

        $sentViaWhatsApp = false;

        if (in_array($channel, ['auto', 'whatsapp'])) {
            $sentViaWhatsApp = $this->sendWhatsApp($tenantId, $phone, $message, $type, $customerId);
        }

        if ($sentViaWhatsApp) {
            return ['success' => true, 'channel' => 'whatsapp'];
        }

        if ($channel === 'whatsapp') {
            return ['success' => false, 'channel' => 'whatsapp'];
        }

        $smsSuccess = $this->smsService->sendToCustomer($tenantId, $phone, $message, $type, $customerId);

        return ['success' => $smsSuccess, 'channel' => 'sms'];
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

        $this->logWhatsApp($tenantId, $customerId, $phone, $message, $type, $result['success'] ? 'sent' : 'failed', $result['response'] ?? null);

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
                'tenant_id' => $tenantId,
                'customer_id' => $customerId,
                'phone' => $phone,
                'message' => $message,
                'type' => $type,
                'status' => $status,
                'response' => $response ? json_encode($response) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('WhatsApp log hatasi: ' . $e->getMessage());
        }
    }
}
