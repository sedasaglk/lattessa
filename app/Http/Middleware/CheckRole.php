<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login.form', ['tenant_slug' => $request->route('tenant_slug')]);
        }

        if (!in_array($user->role, $roles)) {
            abort(403, 'Bu sayfaya erisim yetkiniz bulunmuyor.');
        }

        return $next($request);
    }
}
