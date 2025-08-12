<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Enums\Role;
use App\Models\User;
use App\Models\Tenant;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserPolicyTest extends TestCase
{
    use RefreshDatabase;

    private UserPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new UserPolicy();
    }

    public function test_system_admin_can_view_any_users()
    {
        $systemAdmin = User::factory()->create([
            'role' => Role::SYSTEM_ADMIN
        ]);

        $this->assertTrue($this->policy->viewAny($systemAdmin));
    }

    public function test_tenant_admin_can_view_any_users()
    {
        $tenant = Tenant::factory()->create();
        $admin = User::factory()->create([
            'role' => Role::CONTRIBUTOR
        ]);
        
        // Create tenant admin role for the user
        $admin->tenantRoles()->create([
            'tenant_id' => $tenant->id,
            'role' => Role::TENANT_ADMIN,
            'is_active' => true,
        ]);

        $this->assertTrue($this->policy->viewAny($admin));
    }

    public function test_non_admin_cannot_view_any_users()
    {
        $user = User::factory()->create([
            'role' => Role::CONTRIBUTOR
        ]);

        $this->assertFalse($this->policy->viewAny($user));
    }

    public function test_user_can_view_own_profile()
    {
        $user = User::factory()->create([
            'role' => Role::CONTRIBUTOR
        ]);

        $this->assertTrue($this->policy->view($user, $user));
    }

    public function test_system_admin_can_view_any_user()
    {
        $systemAdmin = User::factory()->create([
            'role' => Role::SYSTEM_ADMIN
        ]);
        $otherUser = User::factory()->create([
            'role' => Role::CONTRIBUTOR
        ]);

        $this->assertTrue($this->policy->view($systemAdmin, $otherUser));
    }

    public function test_tenant_admin_can_view_user_in_shared_tenant()
    {
        $tenant = Tenant::factory()->create();
        $admin = User::factory()->create([
            'role' => Role::CONTRIBUTOR
        ]);
        $otherUser = User::factory()->create([
            'role' => Role::CONTRIBUTOR
        ]);
        
        // Both users have roles in the same tenant
        $admin->tenantRoles()->create([
            'tenant_id' => $tenant->id,
            'role' => Role::TENANT_ADMIN,
            'is_active' => true,
        ]);
        
        $otherUser->tenantRoles()->create([
            'tenant_id' => $tenant->id,
            'role' => Role::PROJECT_MANAGER,
            'is_active' => true,
        ]);

        $this->assertTrue($this->policy->view($admin, $otherUser));
    }

    public function test_user_cannot_view_other_user_profile()
    {
        $user1 = User::factory()->create([
            'role' => Role::CONTRIBUTOR
        ]);
        $user2 = User::factory()->create([
            'role' => Role::CONTRIBUTOR
        ]);

        $this->assertFalse($this->policy->view($user1, $user2));
    }

    public function test_system_admin_can_change_any_user_role()
    {
        $systemAdmin = User::factory()->create([
            'role' => Role::SYSTEM_ADMIN
        ]);
        $otherUser = User::factory()->create([
            'role' => Role::CONTRIBUTOR
        ]);

        $this->assertTrue($this->policy->changeRole($systemAdmin, $otherUser));
    }

    public function test_tenant_admin_can_change_user_roles()
    {
        $tenant = Tenant::factory()->create();
        $admin = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => Role::TENANT_ADMIN
        ]);
        $otherUser = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => Role::CONTRIBUTOR
        ]);

        $this->assertTrue($this->policy->changeRole($admin, $otherUser));
    }

    public function test_tenant_admin_cannot_change_own_role()
    {
        $tenant = Tenant::factory()->create();
        $admin = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => Role::TENANT_ADMIN
        ]);

        $this->assertFalse($this->policy->changeRole($admin, $admin));
    }

    public function test_non_admin_cannot_change_roles()
    {
        $tenant = Tenant::factory()->create();
        $user1 = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => Role::PROJECT_MANAGER
        ]);
        $user2 = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => Role::CONTRIBUTOR
        ]);

        $this->assertFalse($this->policy->changeRole($user1, $user2));
    }

    public function test_tenant_admin_cannot_delete_themselves()
    {
        $tenant = Tenant::factory()->create();
        $admin = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => Role::TENANT_ADMIN
        ]);

        $this->assertFalse($this->policy->delete($admin, $admin));
    }

    public function test_tenant_admin_can_delete_other_users()
    {
        $tenant = Tenant::factory()->create();
        $admin = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => Role::TENANT_ADMIN
        ]);
        $otherUser = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => Role::CONTRIBUTOR
        ]);

        $this->assertTrue($this->policy->delete($admin, $otherUser));
    }

    public function test_system_admin_can_assign_system_admin_role()
    {
        $systemAdmin = User::factory()->create([
            'role' => Role::SYSTEM_ADMIN
        ]);
        $otherUser = User::factory()->create([
            'role' => Role::CONTRIBUTOR
        ]);

        $this->assertTrue($this->policy->assignSystemAdminRole($systemAdmin, $otherUser));
    }

    public function test_system_admin_cannot_assign_system_admin_role_to_themselves()
    {
        $systemAdmin = User::factory()->create([
            'role' => Role::SYSTEM_ADMIN
        ]);

        $this->assertFalse($this->policy->assignSystemAdminRole($systemAdmin, $systemAdmin));
    }

    public function test_tenant_admin_cannot_assign_system_admin_role()
    {
        $tenant = Tenant::factory()->create();
        $tenantAdmin = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => Role::TENANT_ADMIN
        ]);
        $otherUser = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => Role::CONTRIBUTOR
        ]);

        $this->assertFalse($this->policy->assignSystemAdminRole($tenantAdmin, $otherUser));
    }

    public function test_system_admin_cannot_delete_themselves()
    {
        $systemAdmin = User::factory()->create([
            'role' => Role::SYSTEM_ADMIN
        ]);

        $this->assertFalse($this->policy->delete($systemAdmin, $systemAdmin));
    }

    public function test_system_admin_can_delete_other_users()
    {
        $systemAdmin = User::factory()->create([
            'role' => Role::SYSTEM_ADMIN
        ]);
        $otherUser = User::factory()->create([
            'role' => Role::CONTRIBUTOR
        ]);

        $this->assertTrue($this->policy->delete($systemAdmin, $otherUser));
    }}
