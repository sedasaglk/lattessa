<?php

namespace Database\Seeders;

use App\Models\SuperAdmin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        SuperAdmin::create([
            'name' => 'Lattessa Admin',
            'email' => 'admin@lattessa.com',
            'password' => Hash::make('lattessa_admin_2026'),
        ]);
    }
}
