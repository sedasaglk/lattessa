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

        // Şube adresi → Google Maps linki
        $branch  = \Illuminate\Support\Facades\DB::table('branches')->where('id', $appointment->branch_id ?? null)->first();
        $address = $branch->address ?? '';
        $konum   = $address ? 'https://maps.google.com/?q=' . rawurlencode($address) : '';

        // Notification settings'ten template al
        $eventKey = $this->reminderType === '24h' ? 'appointment_reminder_24h' : 'appointment_reminder_2h';
        $setting  = \Illuminate\Support\Facades\DB::table('notification_settings')
            ->where('tenant_id', $appointment->tenant_id)
            ->where('event', $eventKey)
            ->first();
        $tenant   = \Illuminate\Support\Facades\DB::table('tenants')->where('id', $appointment->tenant_id)->first();

        $defaultTemplate = "Sayin {musteri_adi}, {tarih} {saat} saatindeki {hizmet_adi} randevunuzu hatirlatiyoruz. Iptal icin lutfen bizi arayin.";
        $template = ($setting && !empty($setting->template)) ? $setting->template : $defaultTemplate;

        $message = \App\Services\Notification\NotificationService::fillTemplate($template, [
            'musteri_adi'  => $appointment->customer_name,
            'tarih'        => $dateText,
            'saat'         => $hourText,
            'hizmet_adi'   => $appointment->service_name,
            'personel_adi' => $appointment->staff_name ?? '',
            'salon_adi'    => $tenant->company_name ?? 'Lattessa',
            'konum'        => $konum,
        ]);

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
