<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tenant;
use App\Enums\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantSelectionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that users with tenant roles are redirected to tenant selection after login.
     *
     * @return void
     */
    public function test_users_with_tenant_roles_are_redirected_to_tenant_selection_after_login()
    {
        // Create a user with contributor role (global role)
        $user = User::factory()->create([
            'role' => Role::CONTRIBUTOR,
        ]);

        // Create a tenant
        $tenant = Tenant::factory()->create();

        // Assign tenant admin role to user
        $user->tenantRoles()->create([
            'tenant_id' => $tenant->id,
            'role' => Role::TENANT_ADMIN,
            'is_active' => true,
        ]);

        // Attempt to login
        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Should redirect to tenant selection page
        $response->assertRedirect(route('tenant.select'));
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test that contributors are redirected to global dashboard after login.
     *
     * @return void
     */
    public function test_contributors_are_redirected_to_global_dashboard_after_login()
    {
        // Create a user with only contributor role (no tenant roles)
        $user = User::factory()->create([
            'role' => Role::CONTRIBUTOR,
        ]);

        // Attempt to login
        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Should redirect to global dashboard
        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test that system admins are redirected to admin dashboard after login.
     *
     * @return void
     */
    public function test_system_admins_are_redirected_to_admin_dashboard_after_login()
    {
        // Create a system admin user
        $user = User::factory()->create([
            'role' => Role::SYSTEM_ADMIN,
        ]);

        // Attempt to login
        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Should redirect to admin dashboard
        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test that users can select a tenant and get redirected to tenant dashboard.
     *
     * @return void
     */
    public function test_users_can_select_tenant_and_are_redirected_to_tenant_dashboard()
    {
        // Create a user with tenant admin role
        $user = User::factory()->create([
            'role' => Role::CONTRIBUTOR,
        ]);

        // Create a tenant
        $tenant = Tenant::factory()->create([
            'slug' => 'test-org'
        ]);

        // Assign tenant admin role to user
        $user->tenantRoles()->create([
            'tenant_id' => $tenant->id,
            'role' => Role::TENANT_ADMIN,
            'is_active' => true,
        ]);

        // Login first
        $this->actingAs($user);

        // Post to tenant selection
        $response = $this->post(route('tenant.select.store'), [
            'tenant_id' => $tenant->id,
        ]);

        // Should redirect to tenant dashboard
        $response->assertRedirect(route('tenant.dashboard', ['tenant' => $tenant->slug]));
    }

    /**
     * Test that users cannot select tenants they don't have access to.
     *
     * @return void
     */
    public function test_users_cannot_select_tenants_they_dont_have_access_to()
    {
        // Create a user with contributor role (no tenant roles)
        $user = User::factory()->create([
            'role' => Role::CONTRIBUTOR,
        ]);

        // Create a tenant
        $tenant = Tenant::factory()->create();

        // Login first
        $this->actingAs($user);

        // Try to select a tenant the user doesn't have access to
        $response = $this->post(route('tenant.select.store'), [
            'tenant_id' => $tenant->id,
        ]);

        // Should get forbidden response
        $response->assertStatus(403);
    }

    /**
     * Test that tenant selection page shows correct tenants for user.
     *
     * @return void
     */
    public function test_tenant_selection_page_shows_correct_tenants_for_user()
    {
        // Create a user with contributor role
        $user = User::factory()->create([
            'role' => Role::CONTRIBUTOR,
        ]);

        // Create multiple tenants
        $tenant1 = Tenant::factory()->create([
            'name' => 'First Organization',
            'slug' => 'first-org'
        ]);

        $tenant2 = Tenant::factory()->create([
            'name' => 'Second Organization',
            'slug' => 'second-org'
        ]);

        // Assign tenant roles to user
        $user->tenantRoles()->create([
            'tenant_id' => $tenant1->id,
            'role' => Role::TENANT_ADMIN,
            'is_active' => true,
        ]);

        $user->tenantRoles()->create([
            'tenant_id' => $tenant2->id,
            'role' => Role::PROJECT_MANAGER,
            'is_active' => true,
        ]);

        // Create another tenant that user doesn't have access to
        $tenant3 = Tenant::factory()->create([
            'name' => 'Third Organization',
            'slug' => 'third-org'
        ]);

        // Login and visit tenant selection page
        $response = $this->actingAs($user)->get(route('tenant.select'));

        // Should show the tenants user has access to
        $response->assertInertia(fn ($page) => 
            $page->component('auth/select-tenant')
                 ->has('tenants', 2)
                 ->where('tenants.0.name', 'First Organization')
                 ->where('tenants.0.role', 'tenant_admin')
                 ->where('tenants.1.name', 'Second Organization')
                 ->where('tenants.1.role', 'project_manager')
        );
    }
}