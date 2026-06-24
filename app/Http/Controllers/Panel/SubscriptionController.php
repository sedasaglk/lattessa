<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function index(TenantContext $ctx, string $tenant_slug): View
    {
        $tenant = $ctx->get();

        $subscription = DB::table('subscriptions')
            ->join('packages', 'subscriptions.package_id', '=', 'packages.id')
            ->where('subscriptions.tenant_id', $tenant->id)
            ->whereIn('subscriptions.status', ['trial', 'active', 'past_due'])
            ->select(
                'subscriptions.*',
                'packages.name as package_name',
                'packages.slug as package_slug',
                'packages.price_monthly',
                'packages.price_yearly',
                'packages.staff_limit',
                'packages.branch_limit',
                'packages.sms_limit',
                'packages.storage_limit_mb',
                'packages.features',
            )
            ->orderByDesc('subscriptions.created_at')
            ->first();

        $packages = DB::table('packages')
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->get();

        // Kullanim istatistikleri
        $usage = [
            'staff' => DB::table('users')
                ->where('tenant_id', $tenant->id)
                ->whereNull('deleted_at')
                ->whereIn('role', ['firma_sahibi', 'sube_muduru', 'sekreter', 'personel', 'muhasebe', 'cagri_merkezi'])
                ->count(),
            'branches' => DB::table('branches')
                ->where('tenant_id', $tenant->id)
                ->whereNull('deleted_at')
                ->count(),
            'sms_used' => DB::table('sms_logs')
                ->where('tenant_id', $tenant->id)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->where('status', 'sent')
                ->count(),
        ];

        $invoices = DB::table('invoices')
            ->where('tenant_id', $tenant->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $daysLeft = 0;
        if ($tenant->status === 'trial' && $tenant->trial_ends_at) {
            $daysLeft = max(0, (int) ceil(now()->diffInHours($tenant->trial_ends_at, false) / 24));
        }

        return view('panel.subscription.index', compact(
            'tenant', 'subscription', 'packages', 'usage', 'invoices', 'daysLeft'
        ));
    }
}
