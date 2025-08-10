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
        User::create([
            'tenant_id' => 1,
            'name' => 'Adil Imudassir',
            'email' => 'adil.imudassir@sannu-sannu.com',
            'password' => Hash::make('password'),
            'role' => 'tenant_admin',
        ]);

        User::create([
            'tenant_id' => 2,
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => Hash::make('password'),
            'role' => 'tenant_admin',
        ]);

        User::create([
            'tenant_id' => 2,
            'name' => 'Jane Doe',
            'email' => 'jane.doe@example.com',
            'password' => Hash::make('password'),
            'role' => 'project_manager',
        ]);
    }
}
