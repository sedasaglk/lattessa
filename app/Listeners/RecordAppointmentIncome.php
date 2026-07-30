<?php

namespace App\Listeners;

use App\Events\AppointmentCompleted;
use Illuminate\Support\Facades\DB;

class RecordAppointmentIncome
{
    public function handle(AppointmentCompleted $event): void
    {
        // Kasa kaydı artık AppointmentController::updateStatus() içinde yapılıyor.
        // Bu listener yalnızca fallback olarak çalışır (örn: API üzerinden tamamlanma).
        try {
            $exists = DB::table('cash_transactions')
                ->where('tenant_id', $event->tenantId)
                ->where('reference_type', 'appointment')
                ->where('reference_id', $event->appointmentId)
                ->exists();

            if ($exists || $event->price <= 0) return;

            $appointment = DB::table('appointments')->where('id', $event->appointmentId)->first();
            if (!$appointment) return;

            DB::table('cash_transactions')->insert([
                'tenant_id'        => $event->tenantId,
                'branch_id'        => $appointment->branch_id,
                'type'             => 'income',
                'category_id'      => null,
                'amount'           => $event->price,
                'description'      => 'Randevu #' . $event->appointmentId,
                'payment_method'   => 'cash',
                'customer_id'      => $event->customerId,
                'reference_type'   => 'appointment',
                'reference_id'     => $event->appointmentId,
                'appointment_id'   => $event->appointmentId,
                'created_by'       => DB::table('users')->where('tenant_id', $event->tenantId)->where('role', 'firma_sahibi')->value('id') ?? 1,
                'transaction_date' => now()->format('Y-m-d'),
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('RecordAppointmentIncome: ' . $e->getMessage());
        }
    }
}
