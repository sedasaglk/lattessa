<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantResolver
{
    public function handle(Request $request, Closure $next): Response
    {
        $slug = $request->route('tenant_slug');

$debugTenant = \App\Models\Tenant::where('slug', $slug)->first();  
 if (!$slug) {
            abort(404, 'İşletme adresi bulunamadı.');
        }

        $tenant = Tenant::where('slug', $slug)->first();

        if (!$tenant) {
            abort(404, 'İşletme bulunamadı: ' . $slug);
        }

        if ($tenant->status === 'suspended') {
            return redirect()->route('subscription.expired', ['tenant_slug' => $slug]);
        }

        if ($tenant->status === 'deleted') {
            abort(404, 'İşletme bulunamadı.');
        }

        app(TenantContext::class)->set($tenant);

        return $next($request);
    }
}
