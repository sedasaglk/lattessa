<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(string $tenant_slug): View
    {
        $tenant = Tenant::where('slug', $tenant_slug)->firstOrFail();
        return view('auth.login', ['tenant' => $tenant]);
    }

    public function store(Request $request, string $tenant_slug): RedirectResponse
    {
        $tenant = Tenant::where('slug', $tenant_slug)->firstOrFail();

        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'E-posta alani zorunludur.',
            'email.email' => 'Gecerli bir e-posta adresi girin.',
            'password.required' => 'Sifre alani zorunludur.',
        ]);

        $ip = $request->ip();
        $emailKey = 'login.email|' . strtolower($request->input('email')) . '|' . $tenant->id;
        $ipKey    = 'login.ip|' . $ip;

        // 1) IP bazlı global limit: 30 deneme / 15 dakika
        if (RateLimiter::tooManyAttempts($ipKey, 30)) {
            $seconds = RateLimiter::availableIn($ipKey);
            \Illuminate\Support\Facades\Log::warning('Login IP flood', ['ip' => $ip, 'tenant' => $tenant->slug]);
            return back()->withErrors([
                'email' => "Cok fazla istek. {$seconds} saniye sonra tekrar deneyin.",
            ])->onlyInput('email');
        }

        // 2) Email bazlı limit: 5 deneme / 15 dakika (hesap kilidi)
        if (RateLimiter::tooManyAttempts($emailKey, 5)) {
            $seconds = RateLimiter::availableIn($emailKey);
            return back()->withErrors([
                'email' => "Bu hesap gecici olarak kilitlendi. {$seconds} saniye sonra tekrar deneyin.",
            ])->onlyInput('email');
        }

        $user = \App\Models\User::where('email', $request->email)
            ->where('tenant_id', $tenant->id)
            ->first();

        if (!$user || !\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            RateLimiter::hit($ipKey, 900);    // 15 dakika
            RateLimiter::hit($emailKey, 900); // 15 dakika

            // Basarisiz denemeyi logla
            \Illuminate\Support\Facades\DB::table('activity_logs')->insert([
                'tenant_id'  => $tenant->id,
                'user_id'    => null,
                'description' => 'Basarisiz giris denemesi: ' . $request->input('email'),
                'ip_address' => $ip,
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return back()->withErrors([
                'email' => 'E-posta veya sifre hatali.',
            ])->onlyInput('email');
        }

        if ($user->status !== 'active') {
            return back()->withErrors([
                'email' => 'Hesabiniz aktif degil. Lutfen yonetici ile iletisime gecin.',
            ])->onlyInput('email');
        }

        // Basarili giris - rate limit sifirla
        RateLimiter::clear($emailKey);
        RateLimiter::clear($ipKey);

        Auth::login($user, $request->boolean('remember'));
        $user->update(['last_login_at' => now()]);
        $request->session()->regenerate();

        // Aktivite logu
        \Illuminate\Support\Facades\DB::table('activity_logs')->insert([
            'tenant_id'  => $tenant->id,
            'user_id'    => $user->id,
            'description' => $user->name . ' giris yapti',
            'ip_address' => $ip,
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('tenant.home', ['tenant_slug' => $tenant->slug]);
    }

    public function destroy(Request $request, string $tenant_slug): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login.form', ['tenant_slug' => $tenant_slug]);
    }
}
