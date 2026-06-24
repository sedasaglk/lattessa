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
     * Mesaji oncelikle WhatsApp uzerinden, baglanti yoksa SMS uzerinden gonderir.
     */
    public function notify(
        int $tenantId,
        string $phone,
        string $message,
        string $type = 'general',
        ?int $customerId = null,
        string $channel = 'auto' // auto, whatsapp, sms
    ): array {
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
