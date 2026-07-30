<?php

namespace App\Listeners;

use App\Events\AppointmentCompleted;
use Illuminate\Support\Facades\DB;

class UpdateCustomerStats
{
    public function handle(AppointmentCompleted $event): void
    {
        try {
            DB::table('customers')
                ->where('id', $event->customerId)
                ->where('tenant_id', $event->tenantId)
                ->update([
                    'visit_count' => DB::raw('visit_count + 1'),
                    'total_spent' => DB::raw("total_spent + {$event->price}"),
                    'last_visit_at' => now(),
                    'updated_at' => now(),
                ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('UpdateCustomerStats hatasi: ' . $e->getMessage());
        }
    }
}
