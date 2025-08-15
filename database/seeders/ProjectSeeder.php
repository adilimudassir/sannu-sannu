<?php

namespace Database\Seeders;

use App\Enums\ProjectStatus;
use App\Enums\ProjectVisibility;
use App\Models\Project;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Project::create([
            'tenant_id' => 1,
            'name' => 'New Office Equipment',
            'slug' => 'new-office-equipment',
            'total_amount' => 50000,
            'minimum_contribution' => 1000,
            'payment_options' => json_encode(['full', 'installments']),
            'start_date' => now(),
            'end_date' => now()->addMonths(6),
            'created_by' => 2,
            'status' => ProjectStatus::ACTIVE,
            'visibility' => ProjectVisibility::PUBLIC,
        ]);

        Project::create([
            'tenant_id' => 1,
            'name' => 'End of Year',
            'slug' => 'end-of-year',
            'total_amount' => 50000,
            'minimum_contribution' => 1000,
            'payment_options' => json_encode(['full', 'installments']),
            'start_date' => now(),
            'end_date' => now()->addMonths(6),
            'created_by' => 2,
            'status' => ProjectStatus::ACTIVE,
            'visibility' => ProjectVisibility::INVITE_ONLY,
        ]);

        Project::create([
            'tenant_id' => 2,
            'name' => 'Company Retreat',
            'slug' => 'company-retreat',
            'total_amount' => 100000,
            'minimum_contribution' => 5000,
            'payment_options' => json_encode(['full']),
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'created_by' => 3,
            'status' => ProjectStatus::ACTIVE,
            'visibility' => ProjectVisibility::PUBLIC,
        ]);

        Project::create([
            'tenant_id' => 2,
            'name' => 'Vegetables',
            'slug' => 'vegetables',
            'total_amount' => 100000,
            'minimum_contribution' => 5000,
            'payment_options' => json_encode(['full']),
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'created_by' => 3,
            'status' => ProjectStatus::DRAFT,
            'visibility' => ProjectVisibility::PRIVATE,
        ]);
    }
}
