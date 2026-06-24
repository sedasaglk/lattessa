<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        Package::create([
            'name' => 'Başlangıç',
            'slug' => 'baslangic',
            'price_monthly' => 299,
            'price_yearly' => 2990,
            'staff_limit' => 5,
            'user_limit' => 5,
            'branch_limit' => 1,
            'sms_limit' => 100,
            'storage_limit_mb' => 1024,
            'features' => ['appointments', 'crm', 'inventory', 'sales', 'reports'],
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Package::create([
            'name' => 'Profesyonel',
            'slug' => 'profesyonel',
            'price_monthly' => 799,
            'price_yearly' => 7990,
            'staff_limit' => 20,
            'user_limit' => 20,
            'branch_limit' => 5,
            'sms_limit' => 500,
            'storage_limit_mb' => 5120,
            'features' => ['appointments', 'crm', 'inventory', 'sales', 'reports', 'loyalty', 'marketing'],
            'is_active' => true,
            'sort_order' => 2,
        ]);

        Package::create([
            'name' => 'Kurumsal',
            'slug' => 'kurumsal',
            'price_monthly' => 1999,
            'price_yearly' => 19990,
            'staff_limit' => null,
            'user_limit' => null,
            'branch_limit' => null,
            'sms_limit' => 2000,
            'storage_limit_mb' => 25600,
            'features' => ['appointments', 'crm', 'inventory', 'sales', 'reports', 'loyalty', 'marketing', 'affiliate'],
            'is_active' => true,
            'sort_order' => 3,
        ]);
    }
}
