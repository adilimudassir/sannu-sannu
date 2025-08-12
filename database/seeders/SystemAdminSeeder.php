<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SystemAdminSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Check if system admin already exists
        if (User::where('email', 'admin@sannu-sannu.com')->exists()) {
            $this->command->info('System admin user already exists!');
            return;
        }

        // Create a system admin user (global user, no tenant association)
        User::create([
            'name' => 'System Administrator',
            'email' => 'admin@sannu-sannu.com',
            'password' => Hash::make('password'),
            'role' => Role::SYSTEM_ADMIN,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->command->info('System admin user created successfully!');
        $this->command->info('Email: admin@sannu-sannu.com');
        $this->command->info('Password: password');
        $this->command->warn('Please change the password after first login!');
    }
}
