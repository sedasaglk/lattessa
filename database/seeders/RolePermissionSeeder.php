<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'Randevu Görüntüleme', 'slug' => 'appointments.view', 'module' => 'appointments'],
            ['name' => 'Randevu Oluşturma', 'slug' => 'appointments.create', 'module' => 'appointments'],
            ['name' => 'Randevu Silme', 'slug' => 'appointments.delete', 'module' => 'appointments'],
            ['name' => 'CRM Görüntüleme', 'slug' => 'crm.view', 'module' => 'crm'],
            ['name' => 'CRM Düzenleme', 'slug' => 'crm.edit', 'module' => 'crm'],
            ['name' => 'Personel Yönetimi', 'slug' => 'staff.manage', 'module' => 'staff'],
            ['name' => 'Kasa Görüntüleme', 'slug' => 'cash.view', 'module' => 'cash'],
            ['name' => 'Kasa İşlem', 'slug' => 'cash.manage', 'module' => 'cash'],
            ['name' => 'Stok Yönetimi', 'slug' => 'inventory.manage', 'module' => 'inventory'],
            ['name' => 'Satış', 'slug' => 'sales.create', 'module' => 'sales'],
            ['name' => 'Raporlar', 'slug' => 'reports.view', 'module' => 'reports'],
            ['name' => 'Pazarlama Yönetimi', 'slug' => 'marketing.manage', 'module' => 'marketing'],
            ['name' => 'Faturalama Yönetimi', 'slug' => 'billing.manage', 'module' => 'billing'],
            ['name' => 'Ayarlar Yönetimi', 'slug' => 'settings.manage', 'module' => 'settings'],
            ['name' => 'Süper Admin Erişimi', 'slug' => 'admin.access', 'module' => 'admin'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
    }
}
