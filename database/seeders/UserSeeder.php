<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create global users (contributors by default)
        $adil = User::create([
            'name' => 'Adil Imudassir',
            'email' => 'adil.imudassir@sannu-sannu.com',
            'password' => Hash::make('password'),
            'role' => 'contributor', // Global role
        ]);

        $john = User::create([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => Hash::make('password'),
            'role' => 'contributor', // Global role
        ]);

        $jane = User::create([
            'name' => 'Jane Doe',
            'email' => 'jane.doe@example.com',
            'password' => Hash::make('password'),
            'role' => 'contributor', // Global role
        ]);

        // Assign tenant-specific roles
        // Adil is tenant admin for tenant 1
        $adil->tenantRoles()->create([
            'tenant_id' => 1,
            'role' => 'tenant_admin',
            'is_active' => true,
        ]);

        // John is tenant admin for tenant 2
        $john->tenantRoles()->create([
            'tenant_id' => 2,
            'role' => 'tenant_admin',
            'is_active' => true,
        ]);

        // Jane is project manager for tenant 2
        $jane->tenantRoles()->create([
            'tenant_id' => 2,
            'role' => 'project_manager',
            'is_active' => true,
        ]);
    }
}
