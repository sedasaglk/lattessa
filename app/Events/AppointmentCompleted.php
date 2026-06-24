<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AppointmentCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $appointmentId,
        public int $customerId,
        public int $tenantId,
        public float $price
    ) {}
}
