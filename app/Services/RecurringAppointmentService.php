<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RecurringAppointmentService
{
    public function createRecurring(array $data, string $rule, int $count): array
    {
        $created = [];
        $parentId = null;

        $startTime = Carbon::parse($data['start_time']);
        $endTime = Carbon::parse($data['end_time']);
        $duration = $startTime->diffInMinutes($endTime);

        for ($i = 0; $i < $count; $i++) {
            $currentStart = $this->getNextDate($startTime, $rule, $i);
            $currentEnd = $currentStart->copy()->addMinutes($duration);

            // Cakisma kontrolu
            $conflict = DB::table('appointments')
                ->where('tenant_id', $data['tenant_id'])
                ->where('staff_id', $data['staff_id'])
                ->whereNull('deleted_at')
                ->whereIn('status', ['pending', 'confirmed'])
                ->where('start_time', '<', $currentEnd)
                ->where('end_time', '>', $currentStart)
                ->exists();

            if ($conflict) {
                continue; // Cakisan randevuyu atla
            }

            $appointmentId = DB::table('appointments')->insertGetId([
                'tenant_id' => $data['tenant_id'],
                'branch_id' => $data['branch_id'],
                'customer_id' => $data['customer_id'],
                'staff_id' => $data['staff_id'],
                'service_id' => $data['service_id'],
                'start_time' => $currentStart,
                'end_time' => $currentEnd,
                'status' => 'confirmed',
                'source' => 'panel',
                'price' => $data['price'],
                'notes' => $data['notes'] ?? null,
                'is_recurring' => true,
                'recurrence_rule' => $rule,
                'recurrence_count' => $count,
                'parent_appointment_id' => $parentId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($i === 0) {
                $parentId = $appointmentId;
                // Parent kendi kendine referans versin
                DB::table('appointments')
                    ->where('id', $appointmentId)
                    ->update(['parent_appointment_id' => $appointmentId]);
            }

            $created[] = $appointmentId;
        }

        return $created;
    }

    public function getSeriesAppointments(int $parentId, int $tenantId): \Illuminate\Support\Collection
    {
        return DB::table('appointments')
            ->join('customers', 'appointments.customer_id', '=', 'customers.id')
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->where('appointments.tenant_id', $tenantId)
            ->whereNull('appointments.deleted_at')
            ->where(function ($q) use ($parentId) {
                $q->where('appointments.id', $parentId)
                  ->orWhere('appointments.parent_appointment_id', $parentId);
            })
            ->select(
                'appointments.*',
                'customers.name as customer_name',
                'services.name as service_name'
            )
            ->orderBy('appointments.start_time')
            ->get();
    }

    public function cancelSeries(int $parentId, int $tenantId): int
    {
        return DB::table('appointments')
            ->where('tenant_id', $tenantId)
            ->whereNull('deleted_at')
            ->where(function ($q) use ($parentId) {
                $q->where('id', $parentId)
                  ->orWhere('parent_appointment_id', $parentId);
            })
            ->whereIn('status', ['pending', 'confirmed'])
            ->update([
                'status' => 'cancelled',
                'updated_at' => now(),
            ]);
    }

    public function cancelFromDate(int $parentId, int $tenantId, string $fromDate): int
    {
        return DB::table('appointments')
            ->where('tenant_id', $tenantId)
            ->whereNull('deleted_at')
            ->where(function ($q) use ($parentId) {
                $q->where('id', $parentId)
                  ->orWhere('parent_appointment_id', $parentId);
            })
            ->where('start_time', '>=', $fromDate)
            ->whereIn('status', ['pending', 'confirmed'])
            ->update([
                'status' => 'cancelled',
                'updated_at' => now(),
            ]);
    }

    protected function getNextDate(Carbon $base, string $rule, int $index): Carbon
    {
        return match($rule) {
            'daily' => $base->copy()->addDays($index),
            'weekly' => $base->copy()->addWeeks($index),
            'biweekly' => $base->copy()->addWeeks($index * 2),
            'monthly' => $base->copy()->addMonths($index),
            default => $base->copy()->addWeeks($index),
        };
    }
}
