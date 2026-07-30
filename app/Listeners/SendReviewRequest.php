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
            $message = "Sayin {$appointment->customer_name}, {$appointment->service_name} hizmetimizi aldiginiz icin tesekkur ederiz! Deneyiminizi paylasir misiniz? {$link}";

            $notificationService = app(NotificationService::class);
            $notificationService->notify(
                $event->tenantId,
                $appointment->customer_phone,
                $message,
                'review_request',
                $event->customerId,
                'auto'
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Review request gonderilemedi: ' . $e->getMessage());
        }
    }
}
