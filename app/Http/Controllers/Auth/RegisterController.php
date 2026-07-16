<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterTenantRequest;
use App\Services\TenantRegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function __construct(
        protected TenantRegistrationService $registrationService
    ) {}

    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterTenantRequest $request): RedirectResponse
    {
        $tenant = $this->registrationService->register($request->validated());

        $owner = \App\Models\User::where('tenant_id', $tenant->id)
            ->where('role', 'firma_sahibi')
            ->first();

        Auth::login($owner);

        // Hos geldin maili gonder
        try {
            \Illuminate\Support\Facades\Mail::to($owner->email)->send(
                new \App\Mail\WelcomeMail(
                    companyName: $tenant->company_name,
                    ownerName: $owner->name,
                    tenantSlug: $tenant->slug,
                    loginUrl: route('login.form', ['tenant_slug' => $tenant->slug]),
                    bookingUrl: route('booking.show', ['tenant_slug' => $tenant->slug]),
                )
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Hos geldin maili gonderilemedi: ' . $e->getMessage());
        }

        return redirect()->route('tenant.home', ['tenant_slug' => $tenant->slug])
            ->with('success', 'Kaydiniz basariyla olusturuldu! 14 gunluk ucretsiz deneme sureniz basladi.');
    }
}
