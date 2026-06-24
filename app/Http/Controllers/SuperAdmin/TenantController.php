<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class TenantController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->search;
        $status = $request->status;

        $tenants = DB::table('tenants')
            ->whereNull('deleted_at')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('company_name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%")
                       ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('super-admin.tenants.index', compact('tenants', 'search', 'status'));
    }

    public function show(string $id): View
    {
        $tenant = DB::table('tenants')->where('id', $id)->first();
        if (!$tenant) abort(404);

        $subscription = DB::table('subscriptions')
            ->join('packages', 'subscriptions.package_id', '=', 'packages.id')
            ->where('subscriptions.tenant_id', $id)
            ->select('subscriptions.*', 'packages.name as package_name', 'packages.price_monthly')
            ->orderByDesc('subscriptions.created_at')
            ->first();

        $stats = [
            'total_appointments' => DB::table('appointments')->where('tenant_id', $id)->whereNull('deleted_at')->count(),
            'total_customers' => DB::table('customers')->where('tenant_id', $id)->whereNull('deleted_at')->count(),
            'total_staff' => DB::table('users')->where('tenant_id', $id)->whereNull('deleted_at')->count(),
        ];

        return view('super-admin.tenants.show', compact('tenant', 'subscription', 'stats'));
    }

    public function updateStatus(Request $request, string $id): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'in:trial,active,suspended,cancelled'],
        ]);

        DB::table('tenants')->where('id', $id)->update([
            'status' => $request->status,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Firma durumu guncellendi.');
    }
}
