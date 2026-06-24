<?php

namespace App\Console\Commands;

use App\Jobs\SendAppointmentReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendAppointmentReminders extends Command
{
    protected $signature = 'lattessa:send-appointment-reminders';
    protected $description = 'Yaklasan randevular icin SMS hatirlatma gonderir';

    public function handle(): void
    {
        $this->sendReminders(24, '24h');
        $this->sendReminders(2, '2h');
    }

    protected function sendReminders(int $hours, string $type): void
    {
        $from = now()->addHours($hours)->subMinutes(10);
        $to = now()->addHours($hours)->addMinutes(10);

        $appointments = DB::table('appointments')
            ->whereBetween('start_time', [$from, $to])
            ->whereNull('deleted_at')
            ->whereIn('status', ['confirmed', 'pending'])
            ->whereNotExists(function ($q) use ($type) {
                $q->select(DB::raw(1))
                    ->from('sms_logs')
                    ->whereColumn('sms_logs.tenant_id', 'appointments.tenant_id')
                    ->whereRaw("JSON_EXTRACT(sms_logs.provider_response, '$.appointment_id') = appointments.id")
                    ->where('sms_logs.type', 'appointment_reminder')
                    ->where('sms_logs.status', 'sent')
                    ->where('sms_logs.created_at', '>=', now()->subHours($hours + 1));
            })
            ->pluck('id');

        foreach ($appointments as $id) {
            SendAppointmentReminder::dispatch($id, $type);
        }

        $this->info("{$type} hatirlatma: {$appointments->count()} randevu kuyruga eklendi.");
    }
}
