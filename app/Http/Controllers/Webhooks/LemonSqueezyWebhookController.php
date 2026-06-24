<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\Payment\LemonSqueezyService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LemonSqueezyWebhookController extends Controller
{
    public function __construct(
        protected LemonSqueezyService $lemonSqueezy
    ) {}

    public function handle(Request $request): Response
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Signature');

        if (!$this->lemonSqueezy->verifyWebhook($payload, $signature)) {
            Log::warning('LemonSqueezy webhook imza dogrulamasi basarisiz');
            return response('Unauthorized', 401);
        }

        $data = json_decode($payload, true);
        $eventName = $data['meta']['event_name'] ?? null;
        $customData = $data['meta']['custom_data'] ?? [];

        Log::info("LemonSqueezy webhook: {$eventName}", $customData);

        match($eventName) {
            'subscription_created' => $this->handleSubscriptionCreated($data, $customData),
            'subscription_updated' => $this->handleSubscriptionUpdated($data, $customData),
            'subscription_cancelled' => $this->handleSubscriptionCancelled($data, $customData),
            'subscription_expired' => $this->handleSubscriptionExpired($data, $customData),
            'order_created' => $this->handleOrderCreated($data, $customData),
            default => Log::info("LemonSqueezy bilinmeyen event: {$eventName}"),
        };

        return response('OK', 200);
    }

    protected function handleSubscriptionCreated(array $data, array $custom): void
    {
        $tenantId = $custom['tenant_id'] ?? null;
        if (!$tenantId) return;

        $attrs = $data['data']['attributes'];
        $variantId = (string) $data['data']['relationships']['variant']['data']['id'];
        $packageSlug = $this->getPackageSlugByVariant($variantId);
        $package = DB::table('packages')->where('slug', $packageSlug)->first();

        if (!$package) return;

        // Aboneligi guncelle
        DB::table('subscriptions')
            ->where('tenant_id', $tenantId)
            ->update([
                'status' => 'active',
                'package_id' => $package->id,
                'billing_cycle' => 'monthly',
                'starts_at' => now(),
                'ends_at' => $attrs['renews_at'] ?? now()->addMonth(),
                'updated_at' => now(),
            ]);

        // Tenant'i aktif et
        DB::table('tenants')
            ->where('id', $tenantId)
            ->update([
                'status' => 'active',
                'current_package_id' => $package->id,
                'updated_at' => now(),
            ]);

        // Fatura olustur
        $this->createInvoice($tenantId, $attrs, $package);

        Log::info("Tenant #{$tenantId} abonelik aktif edildi: {$packageSlug}");
    }

    protected function handleSubscriptionUpdated(array $data, array $custom): void
    {
        $tenantId = $custom['tenant_id'] ?? null;
        if (!$tenantId) return;

        $attrs = $data['data']['attributes'];
        $status = $attrs['status'];

        $mappedStatus = match($status) {
            'active' => 'active',
            'past_due' => 'past_due',
            'cancelled' => 'cancelled',
            'expired' => 'expired',
            default => 'active',
        };

        DB::table('subscriptions')
            ->where('tenant_id', $tenantId)
            ->update([
                'status' => $mappedStatus,
                'ends_at' => $attrs['renews_at'] ?? $attrs['ends_at'] ?? null,
                'updated_at' => now(),
            ]);

        // Tenant durumunu guncelle
        if ($mappedStatus === 'active') {
            DB::table('tenants')->where('id', $tenantId)->update(['status' => 'active', 'updated_at' => now()]);
        } elseif (in_array($mappedStatus, ['cancelled', 'expired'])) {
            DB::table('tenants')->where('id', $tenantId)->update(['status' => 'suspended', 'updated_at' => now()]);
        }
    }

    protected function handleSubscriptionCancelled(array $data, array $custom): void
    {
        $tenantId = $custom['tenant_id'] ?? null;
        if (!$tenantId) return;

        DB::table('subscriptions')
            ->where('tenant_id', $tenantId)
            ->update(['status' => 'cancelled', 'cancelled_at' => now(), 'updated_at' => now()]);

        Log::info("Tenant #{$tenantId} abonelik iptal edildi");
    }

    protected function handleSubscriptionExpired(array $data, array $custom): void
    {
        $tenantId = $custom['tenant_id'] ?? null;
        if (!$tenantId) return;

        DB::table('subscriptions')
            ->where('tenant_id', $tenantId)
            ->update(['status' => 'expired', 'updated_at' => now()]);

        DB::table('tenants')
            ->where('id', $tenantId)
            ->update(['status' => 'suspended', 'updated_at' => now()]);

        Log::info("Tenant #{$tenantId} abonelik suresi doldu, askiya alindi");
    }

    protected function handleOrderCreated(array $data, array $custom): void
    {
        Log::info('LemonSqueezy order created', ['order' => $data['data']['id'] ?? null]);
    }

    protected function getPackageSlugByVariant(string $variantId): string
    {
        $variants = config('lemonsqueezy.variants');
        return match($variantId) {
            (string) $variants['baslangic'] => 'baslangic',
            (string) $variants['profesyonel'] => 'profesyonel',
            (string) $variants['kurumsal'] => 'kurumsal',
            default => 'profesyonel',
        };
    }

    protected function createInvoice(int $tenantId, array $attrs, object $package): void
    {
        $invoiceNumber = 'LTS-' . now()->format('Ym') . '-' . str_pad(
            (string)(DB::table('invoices')->count() + 1), 6, '0', STR_PAD_LEFT
        );

        DB::table('invoices')->insert([
            'tenant_id' => $tenantId,
            'invoice_number' => $invoiceNumber,
            'amount' => $package->price_monthly,
            'tax_amount' => round($package->price_monthly * 0.20, 2),
            'total_amount' => round($package->price_monthly * 1.20, 2),
            'currency' => 'TRY',
            'status' => 'paid',
            'payment_method' => 'lemonsqueezy',
            'paid_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
