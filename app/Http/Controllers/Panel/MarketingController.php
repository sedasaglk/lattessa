<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class MarketingController extends Controller
{
    public function index(TenantContext $ctx, string $tenant_slug): View
    {
        $tenant = $ctx->get();

        $campaigns = DB::table('campaigns')
            ->where('tenant_id', $tenant->id)
            ->orderByDesc('created_at')
            ->get();

        $coupons = DB::table('coupons')
            ->where('tenant_id', $tenant->id)
            ->orderByDesc('created_at')
            ->get();

        $totalCustomers = DB::table('customers')
            ->where('tenant_id', $tenant->id)
            ->whereNull('deleted_at')
            ->count();

        return view('panel.marketing.index', compact(
            'tenant', 'campaigns', 'coupons', 'totalCustomers'
        ));
    }

    // ============ KAMPANYALAR ============

    public function createCampaign(TenantContext $ctx, string $tenant_slug): View
    {
        $tenant = $ctx->get();

        $totalCustomers = DB::table('customers')
            ->where('tenant_id', $tenant->id)
            ->whereNull('deleted_at')
            ->count();

        $loyaltyTiers = DB::table('loyalty_tiers')
            ->where('tenant_id', $tenant->id)
            ->get();

        return view('panel.marketing.campaign-create', compact(
            'tenant', 'totalCustomers', 'loyaltyTiers'
        ));
    }

    public function storeCampaign(Request $request, TenantContext $ctx, string $tenant_slug): RedirectResponse
    {
        $tenant = $ctx->get();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:email,sms'],
            'content' => ['required', 'string'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
            'segment_type' => ['nullable', 'string'],
            'segment_value' => ['nullable'],
        ]);

        // Hedef segment
        $targetSegment = null;
        if ($request->segment_type) {
            $targetSegment = [
                'type' => $request->segment_type,
                'value' => $request->segment_value,
            ];
        }

        // Hedef musteri sayisi hesapla
        $recipientCount = $this->getRecipientCount($tenant->id, $targetSegment);

        $campaignId = DB::table('campaigns')->insertGetId([
            'tenant_id' => $tenant->id,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'content' => $validated['content'],
            'target_segment' => $targetSegment ? json_encode($targetSegment) : null,
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'status' => $validated['scheduled_at'] ? 'scheduled' : 'draft',
            'recipient_count' => $recipientCount,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('panel.marketing.index', ['tenant_slug' => $tenant->slug])
            ->with('success', 'Kampanya olusturuldu.');
    }

    public function sendCampaign(Request $request, TenantContext $ctx, string $tenant_slug, string $id): RedirectResponse
    {
        $tenant = $ctx->get();

        $campaign = DB::table('campaigns')
            ->where('id', $id)
            ->where('tenant_id', $tenant->id)
            ->first();

        if (!$campaign) abort(404);

        $targetSegment = $campaign->target_segment ? json_decode($campaign->target_segment, true) : null;
        $customers = $this->getRecipients($tenant->id, $targetSegment);

        // Email kampanyasi - Laravel mail sistemi ile gonder
        if ($campaign->type === 'email') {
            foreach ($customers as $customer) {
                if (!$customer->email) continue;

                try {
                    \Illuminate\Support\Facades\Mail::send([], [], function ($message) use ($customer, $campaign, $tenant) {
                        $message->to($customer->email, $customer->name)
                            ->subject("[{$tenant->company_name}] {$campaign->name}")
                            ->html("<div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;padding:20px'>
                                <h2>{$tenant->company_name}</h2>
                                <div>{$campaign->content}</div>
                                <hr><p style='color:#999;font-size:12px'>Bu email {$tenant->company_name} tarafindan gonderilmistir.</p>
                            </div>");
                    });
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Kampanya email gonderilemedi: {$customer->email}");
                }
            }
        }

        // SMS kampanyasi - log driver ile (gercek SMS deploy sonrasi)
        if ($campaign->type === 'sms') {
            foreach ($customers as $customer) {
                \Illuminate\Support\Facades\Log::info("SMS kampanyasi: {$customer->phone} - {$campaign->content}");
            }
        }

        DB::table('campaigns')
            ->where('id', $id)
            ->update([
                'status' => 'sent',
                'sent_at' => now(),
                'recipient_count' => $customers->count(),
                'updated_at' => now(),
            ]);

        return back()->with('success', "Kampanya {$customers->count()} musteriye gonderildi.");
    }

    public function destroyCampaign(TenantContext $ctx, string $tenant_slug, string $id): RedirectResponse
    {
        $tenant = $ctx->get();
        DB::table('campaigns')->where('id', $id)->where('tenant_id', $tenant->id)->delete();
        return back()->with('success', 'Kampanya silindi.');
    }

    // ============ KUPONLAR ============

    public function storeCoupon(Request $request, TenantContext $ctx, string $tenant_slug): RedirectResponse
    {
        $tenant = $ctx->get();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'discount_type' => ['required', 'in:percentage,fixed'],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'min_amount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'valid_from' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date'],
        ]);

        $code = $request->code
            ? strtoupper($request->code)
            : strtoupper(Str::random(8));

        DB::table('coupons')->insert([
            'tenant_id' => $tenant->id,
            'name' => $validated['name'],
            'code' => $code,
            'discount_type' => $validated['discount_type'],
            'discount_value' => $validated['discount_value'],
            'min_amount' => $validated['min_amount'] ?? 0,
            'usage_limit' => $validated['usage_limit'] ?? null,
            'valid_from' => $validated['valid_from'] ?? null,
            'valid_until' => $validated['valid_until'] ?? null,
            'status' => 'active',
            'used_count' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', "Kupon olusturuldu. Kod: {$code}");
    }

    public function toggleCoupon(TenantContext $ctx, string $tenant_slug, string $id): RedirectResponse
    {
        $tenant = $ctx->get();
        $coupon = DB::table('coupons')->where('id', $id)->where('tenant_id', $tenant->id)->first();
        if (!$coupon) abort(404);

        $newStatus = $coupon->status === 'active' ? 'inactive' : 'active';
        DB::table('coupons')->where('id', $id)->update(['status' => $newStatus, 'updated_at' => now()]);

        return back()->with('success', 'Kupon durumu guncellendi.');
    }

    public function destroyCoupon(TenantContext $ctx, string $tenant_slug, string $id): RedirectResponse
    {
        $tenant = $ctx->get();
        DB::table('coupons')->where('id', $id)->where('tenant_id', $tenant->id)->delete();
        return back()->with('success', 'Kupon silindi.');
    }

    // ============ YARDIMCI METODLAR ============

    protected function getRecipients(int $tenantId, ?array $segment)
    {
        $query = DB::table('customers')
            ->where('tenant_id', $tenantId)
            ->whereNull('deleted_at');

        if ($segment) {
            match($segment['type'] ?? '') {
                'last_visit_days' => $query->where('last_visit_at', '<=', now()->subDays($segment['value'])),
                'min_spent' => $query->where('total_spent', '>=', $segment['value']),
                'loyalty_tier' => $query->where('loyalty_tier_id', $segment['value']),
                'birthday_this_month' => $query->whereMonth('birth_date', now()->month),
                default => null,
            };
        }

        return $query->get();
    }

    protected function getRecipientCount(int $tenantId, ?array $segment): int
    {
        return $this->getRecipients($tenantId, $segment)->count();
    }
}
