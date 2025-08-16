<?php

use App\Models\AuditLog;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('audit log has correct fillable attributes', function () {
    $auditLog = new AuditLog;

    expect($auditLog->getFillable())->toContain(
        'user_id',
        'action',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'context',
        'created_at'
    );
});

test('audit log casts attributes correctly', function () {
    $auditLog = AuditLog::factory()->create([
        'old_values' => ['status' => 'pending'],
        'new_values' => ['status' => 'approved'],
        'context' => ['reason' => 'test'],
        'created_at' => now(),
    ]);

    expect($auditLog->old_values)->toBeArray();
    expect($auditLog->new_values)->toBeArray();
    expect($auditLog->context)->toBeArray();
    expect($auditLog->created_at)->toBeInstanceOf(Carbon\Carbon::class);
});

test('audit log belongs to user', function () {
    $user = User::factory()->create();
    $auditLog = AuditLog::factory()->create(['user_id' => $user->id]);

    expect($auditLog->user)->toBeInstanceOf(User::class);
    expect($auditLog->user->id)->toBe($user->id);
});

test('audit log has polymorphic auditable relationship', function () {
    $tenant = Tenant::factory()->create();
    $auditLog = AuditLog::factory()->create([
        'auditable_type' => Tenant::class,
        'auditable_id' => $tenant->id,
    ]);

    expect($auditLog->auditable)->toBeInstanceOf(Tenant::class);
    expect($auditLog->auditable->id)->toBe($tenant->id);
});

test('audit log can be created using static log method', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->create();

    $this->actingAs($user);

    $auditLog = AuditLog::log(
        'updated',
        $tenant,
        ['name' => 'Old Name'],
        ['name' => 'New Name'],
        ['reason' => 'Test update']
    );

    expect($auditLog)->toBeInstanceOf(AuditLog::class);
    expect($auditLog->user_id)->toBe($user->id);
    expect($auditLog->action)->toBe('updated');
    expect($auditLog->auditable_type)->toBe(Tenant::class);
    expect($auditLog->auditable_id)->toBe($tenant->id);
    expect($auditLog->old_values)->toBe(['name' => 'Old Name']);
    expect($auditLog->new_values)->toBe(['name' => 'New Name']);
    expect($auditLog->context)->toBe(['reason' => 'Test update']);
});

test('audit log static log method handles null values', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->create();

    $this->actingAs($user);

    $auditLog = AuditLog::log('created', $tenant);

    expect($auditLog->old_values)->toBeNull();
    expect($auditLog->new_values)->toBeNull();
    expect($auditLog->context)->toBe([]);
});

test('audit log factory creates valid data', function () {
    $auditLog = AuditLog::factory()->create();

    expect($auditLog->user_id)->toBeInt();
    expect($auditLog->action)->toBeString();
    expect($auditLog->auditable_type)->toBeString();
    expect($auditLog->auditable_id)->toBeInt();
    expect($auditLog->ip_address)->toBeString();
    expect($auditLog->user_agent)->toBeString();
});

test('audit log factory can create for specific model', function () {
    $tenant = Tenant::factory()->create();
    $auditLog = AuditLog::factory()->forModel(Tenant::class, $tenant->id)->create();

    expect($auditLog->auditable_type)->toBe(Tenant::class);
    expect($auditLog->auditable_id)->toBe($tenant->id);
});

test('audit log factory can create with specific action', function () {
    $auditLog = AuditLog::factory()->withAction('custom_action')->create();

    expect($auditLog->action)->toBe('custom_action');
});

test('audit log factory can create by specific user', function () {
    $user = User::factory()->create();
    $auditLog = AuditLog::factory()->byUser($user)->create();

    expect($auditLog->user_id)->toBe($user->id);
});
