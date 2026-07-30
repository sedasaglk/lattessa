<?php
// GEÇİCİ DEBUG — kullandıktan sonra silin!
define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$pdo = \Illuminate\Support\Facades\DB::connection()->getPdo();

echo '<pre style="font-family:monospace;background:#111;color:#0f0;padding:20px;font-size:13px;">';

// user_permissions tablosundaki tüm kayıtlar
echo "=== user_permissions tablosu ===\n";
$rows = \Illuminate\Support\Facades\DB::table('user_permissions')->get();
if ($rows->isEmpty()) {
    echo "TABLO BOŞ! Hiç kayıt yok.\n";
} else {
    foreach ($rows as $r) {
        echo "id={$r->id} tenant_id={$r->tenant_id} user_id={$r->user_id} permission={$r->permission}\n";
    }
}

echo "\n=== users tablosu (role != firma_sahibi) ===\n";
$users = \Illuminate\Support\Facades\DB::table('users')
    ->where('role', '!=', 'firma_sahibi')
    ->select('id','name','role','tenant_id')
    ->get();
foreach ($users as $u) {
    echo "id={$u->id} name={$u->name} role={$u->role} tenant_id={$u->tenant_id}\n";
}

echo "\n=== tenants tablosu ===\n";
$tenants = \Illuminate\Support\Facades\DB::table('tenants')->select('id','name','slug')->get();
foreach ($tenants as $t) {
    echo "id={$t->id} name={$t->name} slug={$t->slug}\n";
}

echo '</pre>';
echo '<p style="color:red;font-family:monospace;">Bu dosyayı kullandıktan sonra HEMEN silin: public/debug-perms.php</p>';
