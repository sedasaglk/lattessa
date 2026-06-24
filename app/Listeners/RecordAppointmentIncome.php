<?php

namespace App\Listeners;

use App\Events\AppointmentCompleted;
use Illuminate\Support\Facades\DB;

class RecordAppointmentIncome
{
    public function handle(AppointmentCompleted $event): void
    {
        // Randevu bilgilerini al
        $appointment = DB::table('appointments')
            ->where('id', $event->appointmentId)
            ->first();

        if (!$appointment || $event->price <= 0) {
            return;
        }

        // Ayni randevu icin daha once kasa kaydi olusturulmus mu kontrol et
        $exists = DB::table('cash_transactions')
            ->where('tenant_id', $event->tenantId)
            ->where('reference_type', 'appointment')
            ->where('reference_id', $event->appointmentId)
            ->exists();

        if ($exists) {
            return;
        }

        // Kasaya gelir kaydet
        DB::table('cash_transactions')->insert([
            'tenant_id' => $event->tenantId,
            'branch_id' => $appointment->branch_id,
            'type' => 'income',
            'category_id' => null,
            'amount' => $event->price,
            'description' => "Randevu #" . $event->appointmentId . " - Otomatik",
            'payment_method' => 'cash',
            'customer_id' => $event->customerId,
            'appointment_id' => $event->appointmentId,
            'created_by' => DB::table('users')
                ->where('tenant_id', $event->tenantId)
                ->where('role', 'firma_sahibi')
                ->value('id') ?? 1,
            'transaction_date' => now()->format('Y-m-d'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
