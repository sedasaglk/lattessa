<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\LoyaltyService;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class LoyaltyController extends Controller
{
    public function __construct(protected LoyaltyService $loyaltyService) {}

    public function index(TenantContext $ctx, string $tenant_slug): View
    {
        $tenant = $ctx->get();

        $tiers = DB::table('loyalty_tiers')
            ->where('tenant_id', $tenant->id)
            ->orderBy('min_points')
            ->get();

        $topCustomers = DB::table('customers')
            ->leftJoin('loyalty_tiers', 'customers.loyalty_tier_id', '=', 'loyalty_tiers.id')
            ->where('customers.tenant_id', $tenant->id)
            ->whereNull('customers.deleted_at')
            ->where('customers.loyalty_points', '>', 0)
            ->select('customers.*', 'loyalty_tiers.name as tier_name', 'loyalty_tiers.color as tier_color')
            ->orderByDesc('customers.loyalty_points')
            ->limit(10)
            ->get();

        $totalPointsIssued = DB::table('loyalty_transactions')
            ->where('tenant_id', $tenant->id)
            ->where('type', 'earn')
            ->sum('points');

        $totalPointsRedeemed = DB::table('loyalty_transactions')
            ->where('tenant_id', $tenant->id)
            ->where('type', 'redeem')
            ->sum(DB::raw('ABS(points)'));

        return view('panel.loyalty.index', compact(
            'tenant', 'tiers', 'topCustomers', 'totalPointsIssued', 'totalPointsRedeemed'
        ));
    }

    public function storeTier(Request $request, TenantContext $ctx, string $tenant_slug): RedirectResponse
    {
        $tenant = $ctx->get();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'min_points' => ['required', 'integer', 'min:0'],
            'discount_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'color' => ['required', 'string'],
        ]);

        DB::table('loyalty_tiers')->insert([
            'tenant_id' => $tenant->id,
            ...$validated,
            'sort_order' => $validated['min_points'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Sadakat seviyesi eklendi.');
    }

    public function destroyTier(TenantContext $ctx, string $tenant_slug, string $id): RedirectResponse
    {
        $tenant = $ctx->get();
        DB::table('loyalty_tiers')->where('id', $id)->where('tenant_id', $tenant->id)->delete();
        return back()->with('success', 'Seviye silindi.');
    }

    public function customerPoints(TenantContext $ctx, string $tenant_slug, string $customerId): View
    {
        $tenant = $ctx->get();

        $customer = DB::table('customers')
            ->leftJoin('loyalty_tiers', 'customers.loyalty_tier_id', '=', 'loyalty_tiers.id')
            ->where('customers.id', $customerId)
            ->where('customers.tenant_id', $tenant->id)
            ->select('customers.*', 'loyalty_tiers.name as tier_name', 'loyalty_tiers.color as tier_color', 'loyalty_tiers.discount_rate')
            ->first();

        if (!$customer) abort(404);

        $history = $this->loyaltyService->getCustomerHistory($tenant->id, $customerId);

        $tiers = DB::table('loyalty_tiers')
            ->where('tenant_id', $tenant->id)
            ->orderBy('min_points')
            ->get();

        return view('panel.loyalty.customer', compact('tenant', 'customer', 'history', 'tiers'));
    }

    public function addPoints(Request $request, TenantContext $ctx, string $tenant_slug, string $customerId): RedirectResponse
    {
        $tenant = $ctx->get();

        $request->validate([
            'points' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:200'],
        ]);

        DB::table('loyalty_transactions')->insert([
            'tenant_id' => $tenant->id,
            'customer_id' => $customerId,
            'type' => 'earn',
            'points' => $request->points,
            'description' => $request->description ?: 'Manuel puan ekleme',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('customers')
            ->where('id', $customerId)
            ->where('tenant_id', $tenant->id)
            ->update([
                'loyalty_points' => DB::raw("loyalty_points + {$request->points}"),
                'updated_at' => now(),
            ]);

        $this->loyaltyService->updateTier($tenant->id, $customerId);

        return back()->with('success', "{$request->points} puan eklendi.");
    }

    public function redeemPoints(Request $request, TenantContext $ctx, string $tenant_slug, string $customerId): RedirectResponse
    {
        $tenant = $ctx->get();

        $request->validate([
            'points' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:200'],
        ]);

        $success = $this->loyaltyService->redeemPoints(
            $tenant->id,
            $customerId,
            $request->points,
            $request->description ?: 'Puan kullanimi'
        );

        if (!$success) {
            return back()->with('error', 'Yeterli puan bulunmuyor.');
        }

        return back()->with('success', "{$request->points} puan kullanildi.");
    }
}
