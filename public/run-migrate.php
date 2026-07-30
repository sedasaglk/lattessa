<?php
// GEÇİCİ — migration bittikten sonra bu dosyayı silin!
define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
\Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
echo '<pre style="font-family:monospace;background:#111;color:#0f0;padding:20px;">';
echo \Illuminate\Support\Facades\Artisan::output();
echo '</pre>';
echo '<p style="font-family:monospace;color:red;">Bu dosyayı hemen silin: public/run-migrate.php</p>';
