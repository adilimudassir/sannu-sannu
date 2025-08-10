<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Tenant::create([
            'name' => 'Sannu-Sannu',
            'slug' => 'sannu-sannu',
            'contact_name' => 'Adil Imudassir',
            'contact_email' => 'adil.imudassir@sannu-sannu.com',
        ]);

        Tenant::create([
            'name' => 'Example Corp',
            'slug' => 'example-corp',
            'contact_name' => 'John Doe',
            'contact_email' => 'john.doe@example.com',
        ]);
    }
}
