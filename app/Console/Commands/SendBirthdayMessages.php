<?php

namespace App\Console\Commands;

use App\Jobs\SendBirthdaySms;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendBirthdayMessages extends Command
{
    protected $signature = 'lattessa:send-birthday-sms';
    protected $description = 'Bugun dogum gunu olan musterilere SMS gonderir';

    public function handle(): void
    {
        $today = now()->format('m-d');

        $customers = DB::table('customers')
            ->whereNull('deleted_at')
            ->whereNotNull('birth_date')
            ->whereNotNull('phone')
            ->whereRaw("DATE_FORMAT(birth_date, '%m-%d') = ?", [$today])
            ->select('id', 'tenant_id', 'name')
            ->get();

        foreach ($customers as $customer) {
            // Bugun zaten gonderilmis mi?
            $alreadySent = DB::table('sms_logs')
                ->where('customer_id', $customer->id)
                ->where('type', 'birthday')
                ->whereDate('created_at', today())
                ->exists();

            if (!$alreadySent) {
                SendBirthdaySms::dispatch($customer->id, $customer->tenant_id);
            }
        }

        $this->info("Dogum gunu SMS: {$customers->count()} musteri islendi.");
    }
}
