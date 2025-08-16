<?php

use App\Models\OnboardingProgress;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('onboarding progress has correct fillable attributes', function () {
    $progress = new OnboardingProgress;

    expect($progress->getFillable())->toContain(
        'tenant_id',
        'step_key',
        'step_name',
        'completed',
        'completed_at',
        'data'
    );
});

test('onboarding progress casts attributes correctly', function () {
    $progress = OnboardingProgress::factory()->create([
        'completed' => true,
        'completed_at' => now(),
        'data' => ['key' => 'value'],
    ]);

    expect($progress->completed)->toBeTrue();
    expect($progress->completed_at)->toBeInstanceOf(Carbon\Carbon::class);
    expect($progress->data)->toBeArray();
    expect($progress->data)->toBe(['key' => 'value']);
});

test('onboarding progress belongs to tenant', function () {
    $tenant = Tenant::factory()->create();
    $progress = OnboardingProgress::factory()->create(['tenant_id' => $tenant->id]);

    expect($progress->tenant)->toBeInstanceOf(Tenant::class);
    expect($progress->tenant->id)->toBe($tenant->id);
});

test('onboarding progress can be marked as completed', function () {
    $progress = OnboardingProgress::factory()->incomplete()->create();

    expect($progress->completed)->toBeFalse();
    expect($progress->completed_at)->toBeNull();

    $progress->markCompleted();

    expect($progress->fresh()->completed)->toBeTrue();
    expect($progress->fresh()->completed_at)->not->toBeNull();
});

test('onboarding progress can be marked as incomplete', function () {
    $progress = OnboardingProgress::factory()->completed()->create();

    expect($progress->completed)->toBeTrue();
    expect($progress->completed_at)->not->toBeNull();

    $progress->markIncomplete();

    expect($progress->fresh()->completed)->toBeFalse();
    expect($progress->fresh()->completed_at)->toBeNull();
});

test('onboarding progress factory creates valid data', function () {
    $progress = OnboardingProgress::factory()->create();

    expect($progress->tenant_id)->toBeInt();
    expect($progress->step_key)->toBeString();
    expect($progress->step_name)->toBeString();
    expect($progress->completed)->toBeBool();
});

test('onboarding progress factory can create completed state', function () {
    $progress = OnboardingProgress::factory()->completed()->create();

    expect($progress->completed)->toBeTrue();
    expect($progress->completed_at)->not->toBeNull();
});

test('onboarding progress factory can create incomplete state', function () {
    $progress = OnboardingProgress::factory()->incomplete()->create();

    expect($progress->completed)->toBeFalse();
    expect($progress->completed_at)->toBeNull();
});

test('onboarding progress factory can create for specific step', function () {
    $progress = OnboardingProgress::factory()
        ->forStep('custom_step', 'Custom Step Name')
        ->create();

    expect($progress->step_key)->toBe('custom_step');
    expect($progress->step_name)->toBe('Custom Step Name');
});
