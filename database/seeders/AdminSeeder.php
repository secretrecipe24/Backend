<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Keep sample data deterministic: only the requested super admin.
        Admin::withTrashed()
            ->where('email', '!=', 'admin@wellness.com')
            ->forceDelete();

        Admin::updateOrCreate(
            ['email' => 'admin@wellness.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
            ]
        );
    }
}

