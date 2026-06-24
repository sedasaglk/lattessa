<?php

namespace App\Console\Commands;

use App\Mail\TrialExpired;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ProcessTrialExpirations extends Command
{
    protected $signature = 'lattessa:process-trial-expirations';
    protected $description = 'Suresi dolan deneme hesaplarini askiya al ve email gonder';

    public function handle(): void
    {
        $expiredTenants = DB::table('tenants')
            ->where('status', 'trial')
            ->whereNull('deleted_at')
            ->where('trial_ends_at', '<=', now())
            ->get();

        foreach ($expiredTenants as $tenant) {
            DB::table('tenants')
                ->where('id', $tenant->id)
                ->update([
                    'status' => 'suspended',
                    'updated_at' => now(),
                ]);

            DB::table('subscriptions')
                ->where('tenant_id', $tenant->id)
                ->where('status', 'trial')
                ->update([
                    'status' => 'expired',
                    'updated_at' => now(),
                ]);

            try {
                Mail::to($tenant->email)->send(new TrialExpired(
                    companyName: $tenant->company_name,
                    ownerName: $tenant->owner_name,
                    tenantSlug: $tenant->slug
                ));
                $this->info("Askiya alindi ve email gonderildi: {$tenant->company_name}");
            } catch (\Exception $e) {
                $this->error("Email gonderilemedi: {$tenant->email} - " . $e->getMessage());
            }
        }

        $this->info("Toplam {$expiredTenants->count()} firma askiya alindi.");
    }
}
