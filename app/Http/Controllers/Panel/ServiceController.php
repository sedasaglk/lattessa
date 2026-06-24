<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ServiceController extends Controller
{
    public function index(TenantContext $ctx, string $tenant_slug): View
    {
        $tenant = $ctx->get();

        $services = \Illuminate\Support\Facades\DB::table('services')
            ->where('tenant_id', $tenant->id)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        $categoryIds = $services->pluck('category_id')->filter()->unique();
        $categories = \Illuminate\Support\Facades\DB::table('service_categories')
            ->whereIn('id', $categoryIds)
            ->pluck('name', 'id');

        return view('panel.services.index', compact('tenant', 'services', 'categories'));
    }

    public function create(TenantContext $ctx, string $tenant_slug): View
    {
        $tenant = $ctx->get();
        $categories = \Illuminate\Support\Facades\DB::table('service_categories')
            ->where('tenant_id', $tenant->id)
            ->orderBy('name')
            ->get();

        return view('panel.services.create', compact('tenant', 'categories'));
    }

    public function store(Request $request, TenantContext $ctx, string $tenant_slug): RedirectResponse
    {
        $tenant = $ctx->get();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer'],
            'duration_minutes' => ['required', 'integer', 'min:5'],
            'price' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:active,inactive'],
        ], [
            'name.required' => 'Hizmet adi zorunludur.',
            'duration_minutes.required' => 'Sure zorunludur.',
            'price.required' => 'Fiyat zorunludur.',
        ]);

        \Illuminate\Support\Facades\DB::table('services')->insert([
            'tenant_id' => $tenant->id,
            'name' => $validated['name'],
            'category_id' => $validated['category_id'] ?? null,
            'duration_minutes' => $validated['duration_minutes'],
            'price' => $validated['price'],
            'description' => $validated['description'] ?? null,
            'is_online_bookable' => $request->boolean('is_online_bookable') ? 1 : 0,
            'status' => $validated['status'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('panel.services.index', ['tenant_slug' => $tenant->slug])
            ->with('success', 'Hizmet basariyla eklendi.');
    }

    public function edit(TenantContext $ctx, string $tenant_slug, string $id): View
    {
        $tenant = $ctx->get();

        $service = \Illuminate\Support\Facades\DB::table('services')
            ->where('id', $id)
            ->where('tenant_id', $tenant->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$service) abort(404);

        $categories = \Illuminate\Support\Facades\DB::table('service_categories')
            ->where('tenant_id', $tenant->id)
            ->orderBy('name')
            ->get();

        return view('panel.services.edit', compact('tenant', 'service', 'categories'));
    }

    public function update(Request $request, TenantContext $ctx, string $tenant_slug, string $id): RedirectResponse
    {
        $tenant = $ctx->get();

        $service = \Illuminate\Support\Facades\DB::table('services')
            ->where('id', $id)
            ->where('tenant_id', $tenant->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$service) abort(404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer'],
            'duration_minutes' => ['required', 'integer', 'min:5'],
            'price' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        \Illuminate\Support\Facades\DB::table('services')
            ->where('id', $id)
            ->where('tenant_id', $tenant->id)
            ->update([
                'name' => $validated['name'],
                'category_id' => $validated['category_id'] ?? null,
                'duration_minutes' => $validated['duration_minutes'],
                'price' => $validated['price'],
                'description' => $validated['description'] ?? null,
                'is_online_bookable' => $request->boolean('is_online_bookable') ? 1 : 0,
                'status' => $validated['status'],
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('panel.services.index', ['tenant_slug' => $tenant->slug])
            ->with('success', 'Hizmet guncellendi.');
    }

    public function destroy(TenantContext $ctx, string $tenant_slug, string $id): RedirectResponse
    {
        $tenant = $ctx->get();

        \Illuminate\Support\Facades\DB::table('services')
            ->where('id', $id)
            ->where('tenant_id', $tenant->id)
            ->update(['deleted_at' => now()]);

        return redirect()
            ->route('panel.services.index', ['tenant_slug' => $tenant->slug])
            ->with('success', 'Hizmet silindi.');
    }
}
