<?php

use Illuminate\Support\Facades\Schedule;

// Deneme suresi hatirlatmalari - her gun 09:00
Schedule::command('lattessa:send-trial-reminders')->dailyAt('09:00');

// Deneme suresi bitisi - her gun 00:05
Schedule::command('lattessa:process-trial-expirations')->dailyAt('00:05');

// Randevu hatirlatma SMS - her 15 dakikada
Schedule::command('lattessa:send-appointment-reminders')->everyFifteenMinutes();

// Dogum gunu SMS - her gun 09:30
Schedule::command('lattessa:send-birthday-sms')->dailyAt('09:30');
