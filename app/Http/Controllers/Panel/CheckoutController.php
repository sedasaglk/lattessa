<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\Payment\LemonSqueezyService;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class CheckoutController extends Controller
{
    public function __construct(
        protected LemonSqueezyService $lemonSqueezy
    ) {}

    public function redirect(Request $request, TenantContext $ctx, string $tenant_slug): RedirectResponse
    {
        $tenant = $ctx->get();

        $request->validate([
            'package' => ['required', 'in:baslangic,profesyonel,kurumsal'],
        ]);

        $variantId = config('lemonsqueezy.variants.' . $request->package);

        if (!$variantId) {
            return back()->with('error', 'Gecersiz paket secimi.');
        }

        try {
            $checkoutUrl = $this->lemonSqueezy->createCheckoutUrl(
                variantId: (string) $variantId,
                customerData: [
                    'email' => $tenant->email,
                    'name' => $tenant->owner_name,
                    'tenant_id' => $tenant->id,
                ],
                tenantSlug: $tenant->slug
            );

            return redirect()->away($checkoutUrl);
        } catch (\Exception $e) {
            return back()->with('error', 'Odeme sayfasi olusturulamadi: ' . $e->getMessage());
        }
    }
}
