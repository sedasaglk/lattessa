<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Rol bazlı varsayılan yetkiler.
     * Firma sahibi her şeye erişebilir (middleware'e girmeden geçer).
     */
    public static array $roleDefaults = [
        'sube_muduru' => [
            'sales', 'cash', 'packages', 'loyalty', 'inventory',
            'crm', 'waiting', 'marketing',
            'services', 'salon_photos', 'reviews',
            'staff', 'payroll', 'reports',
            'whatsapp', 'settings', 'notification_settings',
        ],
        'sekreter' => [
            'sales', 'cash', 'packages', 'loyalty', 'inventory',
            'crm', 'waiting', 'marketing',
        ],
        'personel' => [],
    ];

    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login.form', ['tenant_slug' => $request->route('tenant_slug')]);
        }

        // Firma sahibi her şeye erişebilir
        if ($user->role === 'firma_sahibi') {
            return $next($request);
        }

        // Özel yetki konfigürasyonu var mı?
        $hasCustom = DB::table('user_permissions')
            ->where('user_id', $user->id)
            ->where('tenant_id', $user->tenant_id)
            ->exists();

        if ($hasCustom) {
            $granted = DB::table('user_permissions')
                ->where('user_id', $user->id)
                ->where('tenant_id', $user->tenant_id)
                ->where('permission', $permission)
                ->exists();

            if (!$granted) {
                abort(403, 'Bu sayfaya erişim yetkiniz bulunmuyor.');
            }
        } else {
            // Özel yetki yok → rol varsayılanlarını kullan
            $defaults = self::$roleDefaults[$user->role] ?? [];
            if (!in_array($permission, $defaults)) {
                abort(403, 'Bu sayfaya erişim yetkiniz bulunmuyor.');
            }
        }

        return $next($request);
    }
}
