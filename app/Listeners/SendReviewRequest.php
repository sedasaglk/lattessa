<?php

namespace App\Listeners;

use App\Events\AppointmentCompleted;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SendReviewRequest
{
    public function handle(AppointmentCompleted $event): void
    {
        try {
            $appointment = DB::table('appointments')
                ->join('customers', 'appointments.customer_id', '=', 'customers.id')
                ->join('services', 'appointments.service_id', '=', 'services.id')
                ->where('appointments.id', $event->appointmentId)
                ->select('appointments.*', 'customers.name as customer_name', 'customers.phone as customer_phone', 'services.name as service_name')
                ->first();

            if (!$appointment || !$appointment->customer_phone) return;

            $tenant = DB::table('tenants')->where('id', $event->tenantId)->first();
            if (!$tenant) return;

            // Zaten review var mı?
            $exists = DB::table('reviews')->where('appointment_id', $event->appointmentId)->exists();
            if ($exists) return;

            $token = Str::random(32);

            DB::table('reviews')->insert([
                'tenant_id' => $event->tenantId,
                'appointment_id' => $event->appointmentId,
                'customer_id' => $event->customerId,
                'rating' => 0,
                'token' => $token,
                'is_published' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $link = url("/{$tenant->slug}/yorum/{$token}");

            $notificationService = app(NotificationService::class);

            // Özel şablon varsa kullan
            $setting = $notificationService->getSetting($event->tenantId, 'review_request');
            $defaultTemplate = "Sayin {musteri_adi}, {hizmet_adi} hizmetimizi aldiginiz icin tesekkur ederiz! Deneyiminizi paylasir misiniz? {link}";
            $template = ($setting && $setting->template) ? $setting->template : $defaultTemplate;
            $message = NotificationService::fillTemplate($template, [
                'musteri_adi' => $appointment->customer_name,
                'hizmet_adi'  => $appointment->service_name,
                'salon_adi'   => $tenant->company_name,
                'link'        => $link,
            ]);

            $notificationService->notify(
                $event->tenantId,
                $appointment->customer_phone,
                $message,
                'review_request',
                $event->customerId,
                'auto',
                'review_request'
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Review request gonderilemedi: ' . $e->getMessage());
        }
    }
}
