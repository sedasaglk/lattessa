<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CustomerController extends Controller
{
    public function index(TenantContext $ctx, string $tenant_slug): View
    {
        $tenant = $ctx->get();
        $search = request('search');

        $customers = Customer::when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(20);

        return view('panel.customers.index', compact('tenant', 'customers', 'search'));
    }

    public function create(TenantContext $ctx, string $tenant_slug): View
    {
        $tenant = $ctx->get();
        return view('panel.customers.create', compact('tenant'));
    }

    public function store(Request $request, TenantContext $ctx, string $tenant_slug): RedirectResponse
    {
        $tenant = $ctx->get();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:male,female,other'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'name.required' => 'Musteri adi zorunludur.',
            'phone.required' => 'Telefon numarasi zorunludur.',
        ]);

        $validated['tenant_id'] = $tenant->id;

        Customer::create($validated);

        return redirect()
            ->route('panel.customers.index', ['tenant_slug' => $tenant->slug])
            ->with('success', 'Musteri basariyla eklendi.');
    }

    public function show(TenantContext $ctx, string $tenant_slug, string $id): View
    {
        $tenant = $ctx->get();
        $customer = Customer::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
            ->where('id', $id)
            ->where('tenant_id', $tenant->id)
            ->firstOrFail();

        $appointments = \App\Models\Appointment::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
            ->with(['service', 'staff'])
            ->where('customer_id', $customer->id)
            ->where('tenant_id', $tenant->id)
            ->orderByDesc('start_time')
            ->get();

        return view('panel.customers.show', compact('tenant', 'customer', 'appointments'));
    }

    public function edit(TenantContext $ctx, string $tenant_slug, string $id): View
    {
        $tenant = $ctx->get();
        $customer = Customer::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
            ->where('id', $id)
            ->where('tenant_id', $tenant->id)
            ->firstOrFail();

        return view('panel.customers.edit', compact('tenant', 'customer'));
    }

    public function update(Request $request, TenantContext $ctx, string $tenant_slug, string $id): RedirectResponse
    {
        $tenant = $ctx->get();
        $customer = Customer::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
            ->where('id', $id)
            ->where('tenant_id', $tenant->id)
            ->firstOrFail();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:male,female,other'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $customer->update($validated);

        return redirect()
            ->route('panel.customers.show', ['tenant_slug' => $tenant->slug, 'id' => $customer->id])
            ->with('success', 'Musteri bilgileri guncellendi.');
    }

    public function destroy(TenantContext $ctx, string $tenant_slug, string $id): RedirectResponse
    {
        $tenant = $ctx->get();
        $customer = Customer::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
            ->where('id', $id)
            ->where('tenant_id', $tenant->id)
            ->firstOrFail();

        $customer->delete();

        return redirect()
            ->route('panel.customers.index', ['tenant_slug' => $tenant->slug])
            ->with('success', 'Musteri silindi.');
    }
}
