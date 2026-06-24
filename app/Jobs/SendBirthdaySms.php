<?php

namespace App\Jobs;

use App\Services\Notification\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendBirthdaySms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct(
        public int $customerId,
        public int $tenantId
    ) {}

    public function handle(NotificationService $notificationService): void
    {
        $customer = DB::table('customers')
            ->where('id', $this->customerId)
            ->where('tenant_id', $this->tenantId)
            ->whereNull('deleted_at')
            ->first();

        if (!$customer || !$customer->phone) return;

        $tenant = DB::table('tenants')->where('id', $this->tenantId)->first();

        $message = "Sayin {$customer->name}, dogum gununuz kutlu olsun! 🎂 "
            . "Size ozel %10 indirim icin bizi arayin. "
            . "- {$tenant->company_name}";

        $notificationService->notify(
            $this->tenantId,
            $customer->phone,
            $message,
            'birthday',
            $this->customerId,
            'auto'
        );
    }
}
