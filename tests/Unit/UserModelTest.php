<?php

namespace Tests\Unit;

use App\Enums\Role;
use App\Models\User;
use App\Models\Tenant;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test tenant
        $this->tenant = Tenant::factory()->create();
        app()->instance('tenant', $this->tenant);
    }

    public function test_user_has_role_method_works_correctly()
    {
        $user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => Role::TENANT_ADMIN
        ]);

        $this->assertTrue($user->hasRole(Role::TENANT_ADMIN));
        $this->assertFalse($user->hasRole(Role::PROJECT_MANAGER));
        $this->assertFalse($user->hasRole(Role::CONTRIBUTOR));
    }

    public function test_user_has_any_role_method_works_correctly()
    {
        $user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => Role::PROJECT_MANAGER
        ]);

        $this->assertTrue($user->hasAnyRole([Role::TENANT_ADMIN, Role::PROJECT_MANAGER]));
        $this->assertTrue($user->hasAnyRole([Role::PROJECT_MANAGER, Role::CONTRIBUTOR]));
        $this->assertFalse($user->hasAnyRole([Role::TENANT_ADMIN, Role::CONTRIBUTOR]));
    }

    public function test_user_role_helper_methods_work_correctly()
    {
        $tenantAdmin = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => Role::TENANT_ADMIN
        ]);

        $projectManager = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => Role::PROJECT_MANAGER
        ]);

        $contributor = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => Role::CONTRIBUTOR
        ]);

        // Test tenant admin
        $this->assertTrue($tenantAdmin->isTenantAdmin());
        $this->assertFalse($tenantAdmin->isProjectManager());
        $this->assertFalse($tenantAdmin->isContributor());
        $this->assertTrue($tenantAdmin->canManageProjects());
        $this->assertTrue($tenantAdmin->canManageTenant());

        // Test project manager
        $this->assertFalse($projectManager->isTenantAdmin());
        $this->assertTrue($projectManager->isProjectManager());
        $this->assertFalse($projectManager->isContributor());
        $this->assertTrue($projectManager->canManageProjects());
        $this->assertFalse($projectManager->canManageTenant());

        // Test contributor
        $this->assertFalse($contributor->isTenantAdmin());
        $this->assertFalse($contributor->isProjectManager());
        $this->assertTrue($contributor->isContributor());
        $this->assertFalse($contributor->canManageProjects());
        $this->assertFalse($contributor->canManageTenant());
    }

    public function test_user_scopes_work_correctly()
    {
        // Create users with different roles
        $tenantAdmin = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => Role::TENANT_ADMIN
        ]);

        $projectManager = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => Role::PROJECT_MANAGER
        ]);

        $contributor = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => Role::CONTRIBUTOR
        ]);

        // Test role scope
        $admins = User::withRole(Role::TENANT_ADMIN)->get();
        $this->assertCount(1, $admins);
        $this->assertTrue($admins->first()->is($tenantAdmin));

        $managers = User::withRole(Role::PROJECT_MANAGER)->get();
        $this->assertCount(1, $managers);
        $this->assertTrue($managers->first()->is($projectManager));

        // Test active scope
        $contributor->update(['is_active' => false]);
        $activeUsers = User::active()->get();
        $this->assertCount(2, $activeUsers);
        $this->assertFalse($activeUsers->contains($contributor));
    }

    public function test_user_belongs_to_tenant_correctly()
    {
        $user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => Role::CONTRIBUTOR
        ]);

        $this->assertTrue($user->belongsToCurrentTenant());
        $this->assertTrue($user->belongsToTenant($this->tenant->id));
        $this->assertFalse($user->belongsToTenant(999));
    }

    public function test_tenant_scoping_works_automatically()
    {
        // Create another tenant
        $otherTenant = Tenant::factory()->create();

        // Temporarily remove tenant binding to create users for both tenants
        app()->forgetInstance('tenant');

        // Create users for both tenants
        $userInCurrentTenant = User::withoutTenantScope()->create([
            'tenant_id' => $this->tenant->id,
            'role' => Role::CONTRIBUTOR,
            'name' => 'Current Tenant User',
            'email' => 'current@example.com',
            'password' => 'password'
        ]);

        $userInOtherTenant = User::withoutTenantScope()->create([
            'tenant_id' => $otherTenant->id,
            'role' => Role::CONTRIBUTOR,
            'name' => 'Other Tenant User',
            'email' => 'other@example.com',
            'password' => 'password'
        ]);

        // Verify both users exist without scoping
        $allUsers = User::withoutTenantScope()->get();
        $this->assertCount(2, $allUsers);

        // Set tenant context back
        app()->instance('tenant', $this->tenant);

        // Query should only return users from current tenant
        $users = User::all();
        $this->assertCount(1, $users);
        $this->assertTrue($users->first()->is($userInCurrentTenant));
        $this->assertFalse($users->contains($userInOtherTenant));
    }

    public function test_without_tenant_scope_returns_all_users()
    {
        // Create another tenant
        $otherTenant = Tenant::factory()->create();

        // Create users for both tenants
        $userInCurrentTenant = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => Role::CONTRIBUTOR
        ]);

        $userInOtherTenant = User::factory()->create([
            'tenant_id' => $otherTenant->id,
            'role' => Role::CONTRIBUTOR
        ]);

        // Query without tenant scope should return all users
        $allUsers = User::withoutTenantScope()->get();
        $this->assertCount(2, $allUsers);
        $this->assertTrue($allUsers->contains($userInCurrentTenant));
        $this->assertTrue($allUsers->contains($userInOtherTenant));
    }
}