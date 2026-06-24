<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Package;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantRegistrationService
{
    public function register(array $data): Tenant
    {
        $slug = $this->generateUniqueSlug($data['company_name']);

        $tenant = Tenant::create([
            'slug' => $slug,
            'company_name' => $data['company_name'],
            'business_type' => $data['business_type'],
            'owner_name' => $data['owner_name'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(14),
            'referral_code' => $this->generateReferralCode(),
        ]);

        // Varsayılan şube oluştur
        $branch = Branch::create([
            'tenant_id' => $tenant->id,
            'name' => 'Merkez Şube',
            'status' => 'active',
        ]);

        // Firma sahibi kullanıcı oluştur
        User::create([
            'tenant_id' => $tenant->id,
            'name' => $data['owner_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'role' => 'firma_sahibi',
            'branch_id' => $branch->id,
            'status' => 'active',
        ]);

        // Profesyonel paket ile deneme aboneliği başlat (tüm özellikler açık)
        $package = Package::where('slug', 'profesyonel')->first();

        Subscription::create([
            'tenant_id' => $tenant->id,
            'package_id' => $package->id,
            'status' => 'trial',
            'billing_cycle' => 'monthly',
            'starts_at' => now(),
            'trial_ends_at' => now()->addDays(14),
            'ends_at' => now()->addDays(14),
        ]);

        return $tenant;
    }

    protected function generateUniqueSlug(string $companyName): string
    {
        $baseSlug = Str::slug($companyName);

        if (empty($baseSlug)) {
            $baseSlug = 'firma';
        }

        $slug = $baseSlug;
        $counter = 1;

        while (Tenant::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    protected function generateReferralCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (Tenant::where('referral_code', $code)->exists());

        return $code;
    }
}
