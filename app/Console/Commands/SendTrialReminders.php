<?php

namespace App\Console\Commands;

use App\Mail\TrialEndingReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendTrialReminders extends Command
{
    protected $signature = 'lattessa:send-trial-reminders';
    protected $description = 'Deneme suresi bitmek uzere olan firmalara hatirlatma emaili gonder';

    public function handle(): void
    {
        $reminderDays = [7, 3, 1];

        foreach ($reminderDays as $days) {
            $targetDate = now()->addDays($days)->format('Y-m-d');

            $tenants = DB::table('tenants')
                ->where('status', 'trial')
                ->whereNull('deleted_at')
                ->whereDate('trial_ends_at', $targetDate)
                ->get();

            foreach ($tenants as $tenant) {
                try {
                    Mail::to($tenant->email)->send(new TrialEndingReminder(
                        companyName: $tenant->company_name,
                        ownerName: $tenant->owner_name,
                        daysLeft: $days,
                        tenantSlug: $tenant->slug
                    ));
                    $this->info("Hatirlatma gonderildi: {$tenant->email} ({$days} gun kala)");
                } catch (\Exception $e) {
                    $this->error("Email gonderilemedi: {$tenant->email} - " . $e->getMessage());
                }
            }

            $this->info("{$days} gun hatirlatmasi: {$tenants->count()} firma.");
        }
    }
}
