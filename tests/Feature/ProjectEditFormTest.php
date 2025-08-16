<?php

use App\Models\Project;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::create([
        'name' => 'Test Tenant',
        'slug' => 'test-tenant',
        'domain' => 'test-tenant.example.com',
        'contact_name' => 'Test Contact',
        'contact_email' => 'contact@test-tenant.com',
        'platform_fee_percentage' => 5.0,
        'status' => 'active',
        'is_active' => true,
    ]);
    
    $this->user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'email_verified_at' => now(),
        'role' => 'system_admin', // Make user system admin to bypass authorization
    ]);
    
    $this->project = Project::create([
        'tenant_id' => $this->tenant->id,
        'name' => 'Test Project',
        'slug' => 'test-project',
        'description' => 'Test Description',
        'visibility' => 'public',
        'requires_approval' => false,
        'total_amount' => 1000.00,
        'payment_options' => ['full'],
        'installment_frequency' => 'monthly',
        'start_date' => '2025-08-15 10:30:22',
        'end_date' => '2025-12-31 23:59:59',
        'registration_deadline' => '2025-08-01 12:00:00',
        'created_by' => $this->user->id,
        'status' => 'draft',
    ]);
});

describe('Project Edit Form Date Fields', function () {
    it('displays project edit form with properly formatted dates', function () {
        $this->actingAs($this->user)
            ->get(route('tenant.projects.edit', [$this->tenant->slug, $this->project->id]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('tenant/projects/edit')
                ->has('project')
                ->where('project.id', $this->project->id)
                ->where('project.name', 'Test Project')
                ->where('project.start_date', '2025-08-15 10:30:22')
                ->where('project.end_date', '2025-12-31 23:59:59')
                ->where('project.registration_deadline', '2025-08-01 12:00:00')
            );
    });

    it('can update project with new dates', function () {
        $updateData = [
            'name' => 'Updated Project Name',
            'description' => 'Updated Description',
            'visibility' => 'public',
            'requires_approval' => false,
            'total_amount' => 1500.00,
            'payment_options' => ['full'],
            'installment_frequency' => 'monthly',
            'start_date' => '2025-09-01',
            'end_date' => '2026-01-31',
            'registration_deadline' => '2025-08-15',
            'products' => [
                [
                    'name' => 'Test Product',
                    'description' => 'Test Product Description',
                    'price' => 1500.00,
                    'sort_order' => 0,
                ]
            ],
        ];

        $this->actingAs($this->user)
            ->put(route('tenant.projects.update', [$this->tenant->slug, $this->project->id]), $updateData)
            ->assertRedirect();

        $this->project->refresh();

        expect($this->project->name)->toBe('Updated Project Name');
        expect($this->project->start_date->format('Y-m-d'))->toBe('2025-09-01');
        expect($this->project->end_date->format('Y-m-d'))->toBe('2026-01-31');
        expect($this->project->registration_deadline->format('Y-m-d'))->toBe('2025-08-15');
    });

    it('handles null registration deadline correctly', function () {
        $updateData = [
            'name' => 'Updated Project Name',
            'description' => 'Updated Description',
            'visibility' => 'public',
            'requires_approval' => false,
            'total_amount' => 1500.00,
            'payment_options' => ['full'],
            'installment_frequency' => 'monthly',
            'start_date' => '2025-09-01',
            'end_date' => '2026-01-31',
            'registration_deadline' => '', // Empty string should be treated as null
            'products' => [
                [
                    'name' => 'Test Product',
                    'description' => 'Test Product Description',
                    'price' => 1500.00,
                    'sort_order' => 0,
                ]
            ],
        ];

        $this->actingAs($this->user)
            ->put(route('tenant.projects.update', [$this->tenant->slug, $this->project->id]), $updateData)
            ->assertRedirect();

        $this->project->refresh();

        expect($this->project->registration_deadline)->toBeNull();
    });

    it('validates date format requirements', function () {
        $updateData = [
            'name' => 'Updated Project Name',
            'description' => 'Updated Description',
            'visibility' => 'public',
            'requires_approval' => false,
            'total_amount' => 1500.00,
            'payment_options' => ['full'],
            'installment_frequency' => 'monthly',
            'start_date' => 'invalid-date',
            'end_date' => '2026-01-31',
            'products' => [
                [
                    'name' => 'Test Product',
                    'description' => 'Test Product Description',
                    'price' => 1500.00,
                    'sort_order' => 0,
                ]
            ],
        ];

        $this->actingAs($this->user)
            ->put(route('tenant.projects.update', [$this->tenant->slug, $this->project->id]), $updateData)
            ->assertSessionHasErrors(['start_date']);
    });
});