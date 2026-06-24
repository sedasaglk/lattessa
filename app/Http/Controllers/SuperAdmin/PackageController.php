<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PackageController extends Controller
{
    public function index(): View
    {
        $packages = DB::table('packages')->orderBy('sort_order')->get();

        $stats = DB::table('subscriptions')
            ->join('packages', 'subscriptions.package_id', '=', 'packages.id')
            ->whereIn('subscriptions.status', ['trial', 'active'])
            ->select('package_id', DB::raw('COUNT(*) as count'))
            ->groupBy('package_id')
            ->pluck('count', 'package_id');

        return view('super-admin.packages.index', compact('packages', 'stats'));
    }

    public function edit(string $id): View
    {
        $package = DB::table('packages')->where('id', $id)->firstOrFail();
        return view('super-admin.packages.edit', compact('package'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'price_monthly' => ['required', 'numeric', 'min:0'],
            'price_yearly' => ['required', 'numeric', 'min:0'],
            'staff_limit' => ['nullable', 'integer', 'min:1'],
            'branch_limit' => ['nullable', 'integer', 'min:1'],
            'sms_limit' => ['required', 'integer', 'min:0'],
            'storage_limit_mb' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['required', 'integer'],
        ]);

        DB::table('packages')->where('id', $id)->update([
            'name' => $validated['name'],
            'price_monthly' => $validated['price_monthly'],
            'price_yearly' => $validated['price_yearly'],
            'staff_limit' => $validated['staff_limit'] ?? null,
            'branch_limit' => $validated['branch_limit'] ?? null,
            'sms_limit' => $validated['sms_limit'],
            'storage_limit_mb' => $validated['storage_limit_mb'],
            'is_active' => $request->boolean('is_active') ? 1 : 0,
            'sort_order' => $validated['sort_order'],
            'updated_at' => now(),
        ]);

        return redirect()->route('super-admin.packages.index')
            ->with('success', 'Paket guncellendi.');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'unique:packages,slug'],
            'price_monthly' => ['required', 'numeric', 'min:0'],
            'price_yearly' => ['required', 'numeric', 'min:0'],
            'staff_limit' => ['nullable', 'integer', 'min:1'],
            'branch_limit' => ['nullable', 'integer', 'min:1'],
            'sms_limit' => ['required', 'integer', 'min:0'],
            'storage_limit_mb' => ['required', 'integer', 'min:0'],
            'sort_order' => ['required', 'integer'],
        ]);

        DB::table('packages')->insert([
            ...$validated,
            'is_active' => 1,
            'features' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('super-admin.packages.index')
            ->with('success', 'Yeni paket olusturuldu.');
    }
}
