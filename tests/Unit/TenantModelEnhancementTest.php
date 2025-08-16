<?php

use App\Enums\TenantStatus;
use App\Models\Contribution;
use App\Models\OnboardingProgress;
use App\Models\Project;
use App\Models\Tenant;
use App\Models\TenantApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('tenant has enhanced fillable attributes', function () {
    $tenant = new Tenant;

    expect($tenant->getFillable())->toContain(
        'application_id',
        'suspended_at',
        'suspended_reason',
        'suspended_by'
    );
});

test('tenant casts status to enum', function () {
    $tenant = Tenant::factory()->create(['status' => 'active']);

    expect($tenant->status)->toBeInstanceOf(TenantStatus::class);
    expect($tenant->status)->toBe(TenantStatus::ACTIVE);
});

test('tenant casts suspended_at to datetime', function () {
    $tenant = Tenant::factory()->create(['suspended_at' => now()]);

    expect($tenant->suspended_at)->toBeInstanceOf(Carbon\Carbon::class);
});

test('tenant belongs to application', function () {
    $application = TenantApplication::factory()->create();
    $tenant = Tenant::factory()->create(['application_id' => $application->id]);

    expect($tenant->application)->toBeInstanceOf(TenantApplication::class);
    expect($tenant->application->id)->toBe($application->id);
});

test('tenant belongs to suspended by user', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->create(['suspended_by' => $user->id]);

    expect($tenant->suspendedBy)->toBeInstanceOf(User::class);
    expect($tenant->suspendedBy->id)->toBe($user->id);
});

test('tenant has many onboarding progress records', function () {
    $tenant = Tenant::factory()->create();
    $progress1 = OnboardingProgress::factory()->create(['tenant_id' => $tenant->id]);
    $progress2 = OnboardingProgress::factory()->create(['tenant_id' => $tenant->id]);

    expect($tenant->onboardingProgress)->toHaveCount(2);
    expect($tenant->onboardingProgress->first())->toBeInstanceOf(OnboardingProgress::class);
});

test('tenant can check if active', function () {
    $activeTenant = Tenant::factory()->create([
        'is_active' => true,
        'status' => TenantStatus::ACTIVE,
    ]);

    $inactiveTenant = Tenant::factory()->create([
        'is_active' => false,
        'status' => TenantStatus::ACTIVE,
    ]);

    $suspendedTenant = Tenant::factory()->create([
        'is_active' => true,
        'status' => TenantStatus::SUSPENDED,
    ]);

    expect($activeTenant->isActive())->toBeTrue();
    expect($inactiveTenant->isActive())->toBeFalse();
    expect($suspendedTenant->isActive())->toBeFalse();
});

test('tenant can check if suspended', function () {
    $activeTenant = Tenant::factory()->create(['status' => TenantStatus::ACTIVE]);
    $suspendedTenant = Tenant::factory()->create(['status' => TenantStatus::SUSPENDED]);
    $inactiveTenant = Tenant::factory()->create(['status' => TenantStatus::INACTIVE]);

    expect($suspendedTenant->isSuspended())->toBeTrue();
    expect($activeTenant->isSuspended())->toBeFalse();
    expect($inactiveTenant->isSuspended())->toBeFalse();
});

test('tenant can get metrics', function () {
    $tenant = Tenant::factory()->create();

    // Create some related data
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $tenant->users()->attach($user1->id, ['role' => 'admin', 'is_active' => true]);
    $tenant->users()->attach($user2->id, ['role' => 'member', 'is_active' => true]);

    $project1 = Project::factory()->create(['tenant_id' => $tenant->id, 'status' => 'active']);
    $project2 = Project::factory()->create(['tenant_id' => $tenant->id, 'status' => 'completed']);

    // Create unique users for contributions to avoid unique constraint violation
    $contributorUser1 = User::factory()->create();
    $contributorUser2 = User::factory()->create();

    $contribution1 = Contribution::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project1->id,
        'user_id' => $contributorUser1->id,
        'total_paid' => 100,
    ]);
    $contribution2 = Contribution::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project2->id,
        'user_id' => $contributorUser2->id,
        'total_paid' => 200,
    ]);

    $metrics = $tenant->getMetrics();

    expect($metrics)->toHaveKey('total_users');
    expect($metrics)->toHaveKey('total_projects');
    expect($metrics)->toHaveKey('active_projects');
    expect($metrics)->toHaveKey('total_contributions');
    expect($metrics)->toHaveKey('total_revenue');

    expect($metrics['total_users'])->toBe(2);
    expect($metrics['total_projects'])->toBe(2);
    expect($metrics['active_projects'])->toBe(1);
    expect($metrics['total_contributions'])->toBe(2);
    expect($metrics['total_revenue'])->toBe(300);
});
