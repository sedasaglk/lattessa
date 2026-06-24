<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class LoyaltyService
{
    // Her 1 TL harcama = 1 puan (tenant ayarlanabilir yapilabilir)
    const POINTS_PER_TL = 1;

    public function earnPoints(int $tenantId, int $customerId, float $amount, string $refType, int $refId): void
    {
        $points = (int) floor($amount * self::POINTS_PER_TL);
        if ($points <= 0) return;

        DB::table('loyalty_transactions')->insert([
            'tenant_id' => $tenantId,
            'customer_id' => $customerId,
            'type' => 'earn',
            'points' => $points,
            'description' => "Harcama puani: {$amount} TL",
            'reference_type' => $refType,
            'reference_id' => $refId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('customers')
            ->where('id', $customerId)
            ->where('tenant_id', $tenantId)
            ->update([
                'loyalty_points' => DB::raw("loyalty_points + {$points}"),
                'updated_at' => now(),
            ]);

        $this->updateTier($tenantId, $customerId);
    }

    public function redeemPoints(int $tenantId, int $customerId, int $points, string $description = ''): bool
    {
        $customer = DB::table('customers')->where('id', $customerId)->where('tenant_id', $tenantId)->first();
        if (!$customer || $customer->loyalty_points < $points) return false;

        DB::table('loyalty_transactions')->insert([
            'tenant_id' => $tenantId,
            'customer_id' => $customerId,
            'type' => 'redeem',
            'points' => -$points,
            'description' => $description ?: "Puan kullanimi",
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('customers')
            ->where('id', $customerId)
            ->where('tenant_id', $tenantId)
            ->update([
                'loyalty_points' => DB::raw("loyalty_points - {$points}"),
                'updated_at' => now(),
            ]);

        $this->updateTier($tenantId, $customerId);
        return true;
    }

    public function updateTier(int $tenantId, int $customerId): void
    {
        $customer = DB::table('customers')->where('id', $customerId)->where('tenant_id', $tenantId)->first();
        if (!$customer) return;

        $tier = DB::table('loyalty_tiers')
            ->where('tenant_id', $tenantId)
            ->where('min_points', '<=', $customer->loyalty_points)
            ->orderByDesc('min_points')
            ->first();

        DB::table('customers')
            ->where('id', $customerId)
            ->update(['loyalty_tier_id' => $tier?->id ?? null, 'updated_at' => now()]);
    }

    public function getCustomerHistory(int $tenantId, int $customerId)
    {
        return DB::table('loyalty_transactions')
            ->where('tenant_id', $tenantId)
            ->where('customer_id', $customerId)
            ->orderByDesc('created_at')
            ->get();
    }
}
