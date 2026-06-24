<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use App\Services\TenantContext;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TenantContext::class);
        $this->app->singleton(\App\Services\Sms\SmsService::class);
        $this->app->singleton(\App\Services\Notification\NotificationService::class);
    }

    public function boot(): void
    {
        $this->configureRateLimiting();
    }

    protected function configureRateLimiting(): void
    {
        // Login: IP + email kombinasyonu, 5 dakikada 5 deneme
        RateLimiter::for('login', function (Request $request) {
            return [
                Limit::perMinutes(5, 5)
                    ->by($request->ip() . '|' . strtolower($request->input('email', '')))
                    ->response(function () {
                        return back()->withErrors([
                            'email' => 'Cok fazla basarisiz giris denemesi. Lutfen 5 dakika sonra tekrar deneyin.',
                        ])->onlyInput('email');
                    }),
            ];
        });

        // Super admin login: daha siki, 10 dakikada 3 deneme
        RateLimiter::for('super-admin-login', function (Request $request) {
            return [
                Limit::perMinutes(10, 3)
                    ->by('super-admin|' . $request->ip())
                    ->response(function () {
                        return back()->withErrors([
                            'email' => 'Cok fazla basarisiz giris denemesi. Lutfen 10 dakika sonra tekrar deneyin.',
                        ])->onlyInput('email');
                    }),
            ];
        });

        // Online randevu: IP basina dakikada 10 istek
        RateLimiter::for('online-booking', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        // API: kullanici basina dakikada 60 istek
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Sifre sifirlama: 15 dakikada 3 deneme
        RateLimiter::for('password-reset', function (Request $request) {
            return Limit::perMinutes(15, 3)->by($request->ip());
        });
    }
}
