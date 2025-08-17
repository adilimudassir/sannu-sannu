<?php


use function Pest\Laravel\actingAs;
use function Pest\Laravel\patch;
use App\Models\User;
use App\Models\TenantApplication;
use App\Enums\TenantApplicationStatus;

it('system admin can approve a tenant application', function () {
    $admin = User::factory()->systemAdmin()->create();
    $application = TenantApplication::factory()->create(['status' => 'pending']);
    actingAs($admin);
    $response = patch("/admin/tenant-applications/{$application->id}/approve", [
        'notes' => 'Looks good.'
    ]);
    $response->assertRedirect("/admin/tenant-applications/{$application->id}");
    $application->refresh();
    expect($application->status)->toBe(TenantApplicationStatus::APPROVED);
    expect($application->notes)->toBe('Looks good.');
    expect($application->reviewer_id)->toBe($admin->id);
});

it('system admin can reject a tenant application with reason', function () {
    $admin = User::factory()->systemAdmin()->create();
    $application = TenantApplication::factory()->create(['status' => 'pending']);
    actingAs($admin);
    $response = patch("/admin/tenant-applications/{$application->id}/reject", [
        'rejection_reason' => 'Incomplete documents',
        'notes' => 'Missing registration certificate.'
    ]);
    $response->assertRedirect("/admin/tenant-applications/{$application->id}");
    $application->refresh();
    expect($application->status)->toBe(TenantApplicationStatus::REJECTED);
    expect($application->rejection_reason)->toBe('Incomplete documents');
    expect($application->notes)->toBe('Missing registration certificate.');
    expect($application->reviewer_id)->toBe($admin->id);
});
