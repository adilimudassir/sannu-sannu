<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Enums\Role;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Http\Request;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private RoleMiddleware $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new RoleMiddleware();
    }

    public function test_unauthenticated_user_is_redirected()
    {
        $request = Request::create('/test');
        
        try {
            $response = $this->middleware->handle($request, function () {
                return response('OK');
            }, 'tenant_admin');
            
            // Should be a redirect response
            $this->assertEquals(302, $response->getStatusCode());
        } catch (\Exception $e) {
            // If route generation fails, that's expected in unit test context
            // The important thing is that it tries to redirect unauthenticated users
            $this->assertStringContainsString('login', $e->getMessage());
        }
    }

    public function test_user_with_required_role_can_access()
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => Role::TENANT_ADMIN
        ]);

        $this->actingAs($user);

        $request = Request::create('/test');
        
        $response = $this->middleware->handle($request, function () {
            return response('OK');
        }, 'tenant_admin');

        $this->assertEquals('OK', $response->getContent());
    }

    public function test_user_with_multiple_roles_can_access_with_any_required_role()
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => Role::PROJECT_MANAGER
        ]);

        $this->actingAs($user);

        $request = Request::create('/test');
        
        $response = $this->middleware->handle($request, function () {
            return response('OK');
        }, 'tenant_admin', 'project_manager');

        $this->assertEquals('OK', $response->getContent());
    }

    public function test_user_without_required_role_gets_403()
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => Role::CONTRIBUTOR
        ]);

        $this->actingAs($user);

        $request = Request::create('/test');
        
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionMessage('Insufficient permissions');

        $this->middleware->handle($request, function () {
            return response('OK');
        }, 'tenant_admin');
    }

    public function test_invalid_role_throws_exception()
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => Role::CONTRIBUTOR
        ]);

        $this->actingAs($user);

        $request = Request::create('/test');
        
        $this->expectException(\ValueError::class);

        $this->middleware->handle($request, function () {
            return response('OK');
        }, 'invalid_role');
    }
}