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

        return redirect()->route('tenant.home', ['tenant_slug' => $tenant->slug])
            ->with('success', 'Kaydiniz basariyla olusturuldu! 14 gunluk ucretsiz deneme sureniz basladi.');
    }
}
