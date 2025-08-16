<?php

use App\Enums\TenantApplicationStatus;
use App\Models\Tenant;
use App\Models\TenantApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('tenant application has correct fillable attributes', function () {
    $application = new TenantApplication;

    expect($application->getFillable())->toContain(
        'reference_number',
        'organization_name',
        'business_description',
        'industry_type',
        'contact_person_name',
        'contact_person_email',
        'contact_person_phone',
        'business_registration_number',
        'website_url',
        'status',
        'submitted_at',
        'reviewed_at',
        'reviewer_id',
        'rejection_reason',
        'notes'
    );
});

test('tenant application casts attributes correctly', function () {
    $application = TenantApplication::factory()->create([
        'status' => TenantApplicationStatus::PENDING,
        'submitted_at' => now(),
        'reviewed_at' => now(),
    ]);

    expect($application->status)->toBeInstanceOf(TenantApplicationStatus::class);
    expect($application->submitted_at)->toBeInstanceOf(Carbon\Carbon::class);
    expect($application->reviewed_at)->toBeInstanceOf(Carbon\Carbon::class);
});

test('tenant application belongs to reviewer', function () {
    $reviewer = User::factory()->create();
    $application = TenantApplication::factory()->create(['reviewer_id' => $reviewer->id]);

    expect($application->reviewer)->toBeInstanceOf(User::class);
    expect($application->reviewer->id)->toBe($reviewer->id);
});

test('tenant application has one tenant', function () {
    $application = TenantApplication::factory()->create();
    $tenant = Tenant::factory()->create();
    $tenant->update(['application_id' => $application->id]);

    expect($application->fresh()->tenant)->toBeInstanceOf(Tenant::class);
    expect($application->fresh()->tenant->id)->toBe($tenant->id);
});

test('tenant application can check if approved', function () {
    $approvedApplication = TenantApplication::factory()->approved()->create();
    $pendingApplication = TenantApplication::factory()->pending()->create();
    $rejectedApplication = TenantApplication::factory()->rejected()->create();

    expect($approvedApplication->isApproved())->toBeTrue();
    expect($pendingApplication->isApproved())->toBeFalse();
    expect($rejectedApplication->isApproved())->toBeFalse();
});

test('tenant application can check if rejected', function () {
    $approvedApplication = TenantApplication::factory()->approved()->create();
    $pendingApplication = TenantApplication::factory()->pending()->create();
    $rejectedApplication = TenantApplication::factory()->rejected()->create();

    expect($rejectedApplication->isRejected())->toBeTrue();
    expect($approvedApplication->isRejected())->toBeFalse();
    expect($pendingApplication->isRejected())->toBeFalse();
});

test('tenant application can check if pending', function () {
    $approvedApplication = TenantApplication::factory()->approved()->create();
    $pendingApplication = TenantApplication::factory()->pending()->create();
    $rejectedApplication = TenantApplication::factory()->rejected()->create();

    expect($pendingApplication->isPending())->toBeTrue();
    expect($approvedApplication->isPending())->toBeFalse();
    expect($rejectedApplication->isPending())->toBeFalse();
});

test('tenant application can check if it can be reviewed', function () {
    $approvedApplication = TenantApplication::factory()->approved()->create();
    $pendingApplication = TenantApplication::factory()->pending()->create();
    $rejectedApplication = TenantApplication::factory()->rejected()->create();

    expect($pendingApplication->canBeReviewed())->toBeTrue();
    expect($approvedApplication->canBeReviewed())->toBeFalse();
    expect($rejectedApplication->canBeReviewed())->toBeFalse();
});

test('tenant application generates reference number correctly', function () {
    $application = TenantApplication::factory()->create(['id' => 123]);

    $referenceNumber = $application->generateReferenceNumber();

    expect($referenceNumber)->toStartWith('TA-'.now()->format('Ymd').'-');
    expect($referenceNumber)->toEndWith('0123');
});
