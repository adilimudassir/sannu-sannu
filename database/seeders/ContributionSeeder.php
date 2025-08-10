<?php

namespace Database\Seeders;

use App\Models\Contribution;
use Illuminate\Database\Seeder;

class ContributionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Contribution::create([
            'tenant_id' => 2,
            'user_id' => 2,
            'project_id' => 1,
            'total_committed' => 5000,
            'payment_type' => 'installments',
            'installment_amount' => 1000,
            'total_installments' => 5,
            'joined_date' => now(),
        ]);

        Contribution::create([
            'tenant_id' => 2,
            'user_id' => 3,
            'project_id' => 1,
            'total_committed' => 10000,
            'payment_type' => 'full',
            'joined_date' => now(),
        ]);
    }
}
