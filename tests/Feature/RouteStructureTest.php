<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use App\Enums\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RouteStructureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test tenant
        $this->tenant = Tenant::factory()->create([
            'slug' => 'test-org',
            'name' => 'Test Organization'
        ]);
    }

    /** @test */
    public function global_routes_work_without_tenant_context()
    {
        // Test global login route
        $response = $this->get('/login');
        $response->assertStatus(200);
        
        // Test global register route
        $response = $this->get('/register');
        $response->assertStatus(200);
        
        // Test global dashboard (should redirect to login for guests)
        $response = $this->get('/dashboard');
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function tenant_routes_work_with_path_based_routing()
    {
        $user = User::factory()->create(['role' => Role::CONTRIBUTOR]);
        
        // Test tenant dashboard with authentication
        $response = $this->actingAs($user)->get('/test-org/dashboard');
        $response->assertStatus(200);
        
        // Test tenant projects route
        $response = $this->actingAs($user)->get('/test-org/projects');
        $response->assertStatus(200);
    }

    /** @test */
    public function tenant_routes_fail_with_invalid_tenant()
    {
        $user = User::factory()->create(['role' => Role::CONTRIBUTOR]);
        
        // Test with non-existent tenant
        $response = $this->actingAs($user)->get('/invalid-org/dashboard');
        $response->assertRedirect();
        $response->assertRedirectContains('tenant-not-found');
    }

    /** @test */
    public function middleware_execution_order_is_correct()
    {
        $user = User::factory()->create(['role' => Role::CONTRIBUTOR]);
        
        // Test that tenant middleware sets context before auth middleware
        $response = $this->actingAs($user)->get('/test-org/dashboard');
        $response->assertStatus(200);
        
        // Verify tenant context is available
        $this->assertNotNull(app('tenant'));
        $this->assertEquals('test-org', app('tenant')->slug);
    }

    /** @test */
    public function role_middleware_works_with_global_authentication()
    {
        // Create system admin user
        $admin = User::factory()->create(['role' => Role::SYSTEM_ADMIN]);
        
        // Test system admin routes
        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        
        // Test that regular user cannot access admin routes
        $user = User::factory()->create(['role' => Role::CONTRIBUTOR]);
        $response = $this->actingAs($user)->get('/admin/dashboard');
        $response->assertStatus(403);
    }

    /** @test */
    public function tenant_selection_redirects_work_correctly()
    {
        // Create user with tenant admin role
        $user = User::factory()->create(['role' => Role::CONTRIBUTOR]);
        $user->tenantRoles()->create([
            'tenant_id' => $this->tenant->id,
            'role' => Role::TENANT_ADMIN,
            'is_active' => true
        ]);
        
        // Test that admin users are redirected to tenant selection
        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password'
        ]);
        
        // Should redirect to tenant selection for users with admin roles
        $response->assertRedirect(route('tenant.select'));
    }

    /** @test */
    public function error_handling_works_for_tenant_routes()
    {
        // Test tenant not found error page
        $response = $this->get('/tenant-not-found?slug=invalid-org');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('errors/tenant-not-found')
                 ->where('slug', 'invalid-org')
                 ->has('message')
        );
    }

    /** @test */
    public function route_names_are_properly_prefixed()
    {
        // Test global route names
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('login'));
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('register'));
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('dashboard'));
        
        // Test tenant route names
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('tenant.dashboard'));
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('tenant.projects.index'));
        
        // Test admin route names
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('admin.dashboard'));
    }
}