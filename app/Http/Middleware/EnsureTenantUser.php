<?php

namespace App\Http\Middleware;

use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = app(TenantContext::class)->get();

        if (!Auth::check()) {
            return redirect()->route('login.form', ['tenant_slug' => $tenant->slug]);
        }

        if (Auth::user()->tenant_id !== $tenant->id) {
            Auth::logout();
            return redirect()->route('login.form', ['tenant_slug' => $tenant->slug]);
        }

        return $next($request);
    }
}
