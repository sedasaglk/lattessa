<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('super-admin.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Super admin icin siki rate limiting: 10 dakikada 3 deneme
        $rateLimitKey = 'super-admin-login|' . $request->ip();

        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            $minutes = ceil($seconds / 60);
            return back()->withErrors([
                'email' => "Cok fazla basarisiz deneme. {$minutes} dakika sonra tekrar deneyin.",
            ])->onlyInput('email');
        }

        if (Auth::guard('super_admin')->attempt($request->only('email', 'password'))) {
            RateLimiter::clear($rateLimitKey);

            $user = Auth::guard('super_admin')->user();

            // 2FA aktif mi?
            if ($user->two_factor_enabled) {
                // Gercek girisi geri al, 2FA challenge'a yon
                Auth::guard('super_admin')->logout();
                session(['2fa_user_id' => $user->id]);
                return redirect()->route('super-admin.2fa.challenge');
            }

            $request->session()->regenerate();

            \Illuminate\Support\Facades\DB::table('activity_logs')->insert([
                'tenant_id' => null,
                'user_id' => $user->id,
                'description' => 'Super admin giris yapti',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return redirect()->route('super-admin.dashboard');
        }

        RateLimiter::hit($rateLimitKey, 600); // 10 dakika

        return back()->withErrors([
            'email' => 'E-posta veya sifre hatali.',
        ])->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('super_admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('super-admin.login');
    }
}
