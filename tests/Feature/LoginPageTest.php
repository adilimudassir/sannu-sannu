<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tenant;
use App\Enums\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_can_be_rendered(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('auth/login')
                ->has('canResetPassword')
        );
    }

    public function test_contributor_redirects_to_global_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => Role::CONTRIBUTOR,
        ]);

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_system_admin_redirects_to_admin_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => Role::SYSTEM_ADMIN,
        ]);

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_credentials_show_error(): void
    {
        $response = $this->post(route('login.store'), [
            'email' => 'invalid@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_validation_errors_are_shown(): void
    {
        $response = $this->post(route('login.store'), [
            'email' => '',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
    }

    public function test_user_with_tenant_roles_redirects_to_tenant_selection(): void
    {
        $user = User::factory()->create([
            'role' => Role::CONTRIBUTOR,
        ]);

        // Create a tenant first
        $tenant = Tenant::factory()->create();

        // Create a tenant role for the user
        $user->tenantRoles()->create([
            'tenant_id' => $tenant->id,
            'role' => Role::TENANT_ADMIN,
            'is_active' => true,
        ]);

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('tenant.select'));
        $this->assertAuthenticatedAs($user);
    }
}