<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class AppointmentService
{
    public function checkConflict(int $staffId, Carbon $start, Carbon $end, ?int $excludeId = null): bool
    {
        return Appointment::where('staff_id', $staffId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->where(function ($q) use ($start, $end) {
                $q->where('start_time', '<', $end)
                  ->where('end_time', '>', $start);
            })
            ->exists();
    }

    public function create(array $data): Appointment
    {
        $service = Service::findOrFail($data['service_id']);
        $start = Carbon::parse($data['start_time']);
        $end = $start->copy()->addMinutes($service->duration_minutes);

        if ($this->checkConflict($data['staff_id'], $start, $end)) {
            throw ValidationException::withMessages([
                'start_time' => 'Secilen personel bu saat araliginda dolu.',
            ]);
        }

        return Appointment::create([
            'tenant_id' => app('current_tenant_id'),
            'branch_id' => $data['branch_id'],
            'customer_id' => $data['customer_id'],
            'staff_id' => $data['staff_id'],
            'service_id' => $data['service_id'],
            'start_time' => $start,
            'end_time' => $end,
            'status' => 'pending',
            'source' => 'panel',
            'notes' => $data['notes'] ?? null,
            'price' => $service->price,
        ]);
    }

    public function getAvailableSlots(User $staff, Service $service, Carbon $date): array
    {
        $schedule = StaffSchedule($staff, $date);

        if (!$schedule) {
            return [];
        }

        $workStart = $date->copy()->setTimeFromTimeString($schedule->start_time);
        $workEnd = $date->copy()->setTimeFromTimeString($schedule->end_time);
        $duration = $service->duration_minutes;

        $existing = Appointment::where('staff_id', $staff->id)
            ->whereDate('start_time', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('start_time')
            ->get(['start_time', 'end_time']);

        $slots = [];
        $cursor = $workStart->copy();

        while ($cursor->copy()->addMinutes($duration)->lte($workEnd)) {
            $slotEnd = $cursor->copy()->addMinutes($duration);
            $conflict = $existing->first(function ($appt) use ($cursor, $slotEnd) {
                return $cursor->lt($appt->end_time) && $slotEnd->gt($appt->start_time);
            });

            if (!$conflict && $cursor->gt(now())) {
                $slots[] = $cursor->format('H:i');
            }

            $cursor->addMinutes(15);
        }

        return $slots;
    }
}

function StaffSchedule(User $staff, Carbon $date): ?\App\Models\StaffSchedule
{
    return \App\Models\StaffSchedule::where('user_id', $staff->id)
        ->where('day_of_week', $date->dayOfWeek)
        ->where('is_day_off', false)
        ->first();
}
