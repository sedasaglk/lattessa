<?php

namespace App\Listeners;

use App\Events\AppointmentCompleted;
use App\Services\LoyaltyService;

class AwardLoyaltyPoints
{
    public function __construct(protected LoyaltyService $loyaltyService) {}

    public function handle(AppointmentCompleted $event): void
    {
        if ($event->price <= 0) return;

        $this->loyaltyService->earnPoints(
            tenantId: $event->tenantId,
            customerId: $event->customerId,
            amount: $event->price,
            refType: 'appointment',
            refId: $event->appointmentId
        );
    }
}
