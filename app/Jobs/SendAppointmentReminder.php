<?php

namespace App\Jobs;

use App\Services\Notification\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendAppointmentReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public int $appointmentId,
        public string $reminderType // '24h' veya '2h'
    ) {}

    public function handle(NotificationService $notificationService): void
    {
        $appointment = DB::table('appointments')
            ->join('customers', 'appointments.customer_id', '=', 'customers.id')
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->join('users', 'appointments.staff_id', '=', 'users.id')
            ->where('appointments.id', $this->appointmentId)
            ->whereNull('appointments.deleted_at')
            ->whereIn('appointments.status', ['confirmed', 'pending'])
            ->select(
                'appointments.*',
                'customers.name as customer_name',
                'customers.phone as customer_phone',
                'services.name as service_name',
                'users.name as staff_name'
            )
            ->first();

        if (!$appointment) {
            Log::info("Randevu bulunamadi veya iptal edildi: #{$this->appointmentId}");
            return;
        }

        $startTime = \Carbon\Carbon::parse($appointment->start_time);
        $hourText = $startTime->format('H:i');
        $dateText = $startTime->format('d.m.Y');

        $message = "Sayin {$appointment->customer_name}, {$dateText} {$hourText} saatindeki "
            . "{$appointment->service_name} randevunuzu hatirlatiyoruz. "
            . "Iptal icin lutfen bizi arayin.";

        // WhatsApp varsa WhatsApp, yoksa otomatik SMS'e duser
        $result = $notificationService->notify(
            $appointment->tenant_id,
            $appointment->customer_phone,
            $message,
            'appointment_reminder',
            $appointment->customer_id,
            'auto'
        );

        if ($result['success']) {
            Log::info("Hatirlatma gonderildi ({$result['channel']}): Randevu #{$this->appointmentId} ({$this->reminderType})");
        } else {
            Log::warning("Hatirlatma gonderilemedi: Randevu #{$this->appointmentId}");
        }
    }
}
