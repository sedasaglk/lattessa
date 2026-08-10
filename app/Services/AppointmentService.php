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

        $appointment = Appointment::create([
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

        // Panel'den eklenen randevularda push gönderilmez (source=panel)

        // WhatsApp onay mesaji gonder
        try {
            $customer = \App\Models\Customer::find($appointment->customer_id);
            $staff = \App\Models\User::find($appointment->staff_id);
            $tenant = \Illuminate\Support\Facades\DB::table('tenants')->where('id', $appointment->tenant_id)->first();

            if ($customer && $customer->phone) {
                $dateText = $start->format('d.m.Y');
                $timeText = $start->format('H:i');

                // Şube adresinden Google Maps linki
                $branch  = \Illuminate\Support\Facades\DB::table('branches')->where('id', $appointment->branch_id)->first();
                $address = $branch->address ?? '';
                $konum   = $address ? 'https://maps.google.com/?q=' . rawurlencode($address) : '';

                // Notification settings'ten template al, yoksa varsayılan kullan
                $setting = \Illuminate\Support\Facades\DB::table('notification_settings')
                    ->where('tenant_id', $appointment->tenant_id)
                    ->where('event', 'appointment_confirmation')
                    ->first();
                $defaultTemplate = "Sayin {musteri_adi}, randevunuz olusturulmustur.\n\nTarih: {tarih}\nSaat: {saat}\nHizmet: {hizmet_adi}\nPersonel: {personel_adi}\n\nIptal icin lutfen bizi arayin.\n- {salon_adi}";
                $template = ($setting && !empty($setting->template)) ? $setting->template : $defaultTemplate;

                $message = \App\Services\Notification\NotificationService::fillTemplate($template, [
                    'musteri_adi'  => $customer->name,
                    'tarih'        => $dateText,
                    'saat'         => $timeText,
                    'hizmet_adi'   => $service->name,
                    'personel_adi' => $staff->name ?? '',
                    'salon_adi'    => $tenant->company_name ?? 'Lattessa',
                    'konum'        => $konum,
                ]);

                $notificationService = app(\App\Services\Notification\NotificationService::class);
                $notificationService->notify(
                    $appointment->tenant_id,
                    $customer->phone,
                    $message,
                    'appointment_confirmation',
                    $customer->id,
                    'auto',
                    null,
                    $appointment->staff_id
                );
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Randevu onay mesaji gonderilemedi: ' . $e->getMessage());
        }

        return $appointment;
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
