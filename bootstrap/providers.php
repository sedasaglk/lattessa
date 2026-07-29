<?php

use App\Providers\AppServiceProvider;
use App\Providers\EventServiceProvider;
use Kreait\Laravel\Firebase\ServiceProvider as FirebaseServiceProvider;

return [
    AppServiceProvider::class,
    EventServiceProvider::class,
    FirebaseServiceProvider::class,
];
