<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $admin = auth()->guard('super_admin')->user();

        // Temel metrikler
        $totalTenants = DB::table('tenants')->whereNull('deleted_at')->count();
        $activeTenants = DB::table('tenants')->whereNull('deleted_at')->where('status', 'active')->count();
        $trialTenants = DB::table('tenants')->whereNull('deleted_at')->where('status', 'trial')->count();
        $suspendedTenants = DB::table('tenants')->whereNull('deleted_at')->where('status', 'suspended')->count();

        // MRR hesapla (aktif abonelikler)
        $mrr = DB::table('subscriptions')
            ->join('packages', 'subscriptions.package_id', '=', 'packages.id')
            ->where('subscriptions.status', 'active')
            ->where('subscriptions.billing_cycle', 'monthly')
            ->sum('packages.price_monthly');

        $mrrYearly = DB::table('subscriptions')
            ->join('packages', 'subscriptions.package_id', '=', 'packages.id')
            ->where('subscriptions.status', 'active')
            ->where('subscriptions.billing_cycle', 'yearly')
            ->sum(DB::raw('packages.price_yearly / 12'));

        $totalMrr = $mrr + $mrrYearly;
        $arr = $totalMrr * 12;

        // Bu ay yeni kayitlar
        $newThisMonth = DB::table('tenants')
            ->whereNull('deleted_at')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Son kayit olan tenantlar
        $recentTenants = DB::table('tenants')
            ->whereNull('deleted_at')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Paket dagilimi
        $packageDistribution = DB::table('subscriptions')
            ->join('packages', 'subscriptions.package_id', '=', 'packages.id')
            ->whereIn('subscriptions.status', ['active', 'trial'])
            ->select('packages.name', DB::raw('count(*) as count'))
            ->groupBy('packages.name')
            ->get();

        return view('super-admin.dashboard', compact(
            'admin',
            'totalTenants',
            'activeTenants',
            'trialTenants',
            'suspendedTenants',
            'totalMrr',
            'arr',
            'newThisMonth',
            'recentTenants',
            'packageDistribution'
        ));
    }
}
