<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TenantMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test tenant
        $this->tenant = Tenant::factory()->create([
            'slug' => 'test-tenant',
            'name' => 'Test Tenant',
            'is_active' => true,
        ]);
    }

    public function test_tenant_middleware_resolves_tenant_from_path()
    {
        $response = $this->get('/test-tenant/login');
        
        $response->assertStatus(200);
        $this->assertEquals($this->tenant->id, app('tenant')->id);
    }

    public function test_tenant_middleware_handles_invalid_tenant_slug()
    {
        $response = $this->get('/invalid-tenant/login');
        
        $response->assertStatus(404);
    }

    public function test_tenant_middleware_handles_inactive_tenant()
    {
        $this->tenant->update(['is_active' => false]);
        
        $response = $this->get('/test-tenant/login');
        
        $response->assertStatus(404);
    }

    public function test_tenant_context_is_shared_with_views()
    {
        $response = $this->get('/test-tenant/login');
        
        $response->assertStatus(200);
        $response->assertViewHas('tenant');
        $this->assertEquals($this->tenant->id, $response->viewData('tenant')->id);
    }

    public function test_tenant_service_returns_current_tenant()
    {
        $this->get('/test-tenant/login');
        
        $currentTenant = \App\Services\TenantService::current();
        $this->assertNotNull($currentTenant);
        $this->assertEquals($this->tenant->id, $currentTenant->id);
    }

    public function test_tenant_service_has_tenant_check()
    {
        $this->assertFalse(\App\Services\TenantService::hasTenant());
        
        $this->get('/test-tenant/login');
        
        $this->assertTrue(\App\Services\TenantService::hasTenant());
    }

    public function test_tenant_url_generation()
    {
        $url = \App\Services\TenantService::getUrl('test-tenant', 'dashboard');
        
        $this->assertStringContainsString('test-tenant', $url);
        $this->assertStringContainsString('dashboard', $url);
    }

    public function test_tenant_context_for_frontend()
    {
        $this->get('/test-tenant/login');
        
        $context = \App\Services\TenantService::getContextForFrontend();
        
        $this->assertArrayHasKey('id', $context);
        $this->assertArrayHasKey('slug', $context);
        $this->assertArrayHasKey('name', $context);
        $this->assertEquals($this->tenant->id, $context['id']);
        $this->assertEquals($this->tenant->slug, $context['slug']);
    }

    public function test_authenticated_user_can_access_tenant_dashboard()
    {
        $user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $response = $this->actingAs($user)->get('/test-tenant/dashboard');
        
        $response->assertStatus(200);
        $this->assertEquals($this->tenant->id, app('tenant')->id);
    }
}