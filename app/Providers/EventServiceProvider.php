<?php

namespace App\Providers;

use App\Events\AppointmentCompleted;
use App\Listeners\UpdateCustomerStats;
use App\Listeners\RecordAppointmentIncome;
use App\Listeners\AwardLoyaltyPoints;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        AppointmentCompleted::class => [
            UpdateCustomerStats::class,
            RecordAppointmentIncome::class,
            AwardLoyaltyPoints::class,
        ],
    ];

    public function boot(): void
    {
        parent::boot();
    }
}
