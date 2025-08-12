<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Tenant;
use App\Services\SessionService;
use App\Enums\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Carbon\Carbon;

class SessionServiceTest extends TestCase
{
    use RefreshDatabase;

    private SessionService $sessionService;
    private User $user;
    private Request $request;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->sessionService = new SessionService();
        $this->user = User::factory()->create([
            'role' => Role::CONTRIBUTOR,
        ]);
        
        $this->request = Request::create('/test', 'GET');
        $this->request->setLaravelSession($this->app['session.store']);
    }

    public function test_creates_global_session(): void
    {
        $this->sessionService->createGlobalSession($this->user, $this->request);

        $this->assertEquals($this->user->id, $this->request->session()->get('user_id'));
        $this->assertEquals(Role::CONTRIBUTOR->value, $this->request->session()->get('global_role'));
        $this->assertNotNull($this->request->session()->get('login_time'));
        $this->assertNotNull($this->request->session()->get('last_activity'));
    }

    public function test_sets_tenant_context_for_system_admin(): void
    {
        $systemAdmin = User::factory()->create(['role' => Role::SYSTEM_ADMIN]);
        $tenant = Tenant::factory()->create();
        
        Auth::login($systemAdmin);

        $result = $this->sessionService->setTenantContext($tenant->id, $this->request);

        $this->assertTrue($result);
        $this->assertEquals($tenant->id, $this->request->session()->get('selected_tenant_id'));
        $this->assertEquals($tenant->slug, $this->request->session()->get('selected_tenant_slug'));
    }

    public function test_sets_tenant_context_for_tenant_admin(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['role' => Role::CONTRIBUTOR]);
        
        // Give user tenant admin role
        $user->tenantRoles()->create([
            'tenant_id' => $tenant->id,
            'role' => Role::TENANT_ADMIN,
            'is_active' => true,
        ]);
        
        Auth::login($user);

        $result = $this->sessionService->setTenantContext($tenant->id, $this->request);

        $this->assertTrue($result);
        $this->assertEquals($tenant->id, $this->request->session()->get('selected_tenant_id'));
        $this->assertEquals(Role::TENANT_ADMIN->value, $this->request->session()->get('tenant_role'));
    }

    public function test_fails_to_set_tenant_context_without_permission(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['role' => Role::CONTRIBUTOR]);
        
        Auth::login($user);

        $result = $this->sessionService->setTenantContext($tenant->id, $this->request);

        $this->assertFalse($result);
        $this->assertNull($this->request->session()->get('selected_tenant_id'));
    }

    public function test_clears_tenant_context(): void
    {
        // Set some tenant context first
        $this->request->session()->put([
            'selected_tenant_id' => 1,
            'selected_tenant_slug' => 'test-tenant',
            'tenant_role' => Role::TENANT_ADMIN->value,
        ]);

        Auth::login($this->user);
        $this->sessionService->clearTenantContext($this->request);

        $this->assertNull($this->request->session()->get('selected_tenant_id'));
        $this->assertNull($this->request->session()->get('selected_tenant_slug'));
        $this->assertNull($this->request->session()->get('tenant_role'));
    }

    public function test_gets_tenant_context(): void
    {
        $tenantId = 123;
        $tenantSlug = 'test-tenant';
        $tenantRole = Role::TENANT_ADMIN->value;
        
        $this->request->session()->put([
            'selected_tenant_id' => $tenantId,
            'selected_tenant_slug' => $tenantSlug,
            'tenant_role' => $tenantRole,
            'tenant_context_set_at' => now(),
        ]);

        $context = $this->sessionService->getTenantContext($this->request);

        $this->assertNotNull($context);
        $this->assertEquals($tenantId, $context['tenant_id']);
        $this->assertEquals($tenantSlug, $context['tenant_slug']);
        $this->assertEquals($tenantRole, $context['tenant_role']);
    }

    public function test_returns_null_when_no_tenant_context(): void
    {
        $context = $this->sessionService->getTenantContext($this->request);

        $this->assertNull($context);
    }

    public function test_detects_expired_session(): void
    {
        // Set last activity to 3 hours ago (default session lifetime is 120 minutes)
        $this->request->session()->put('last_activity', Carbon::now()->subHours(3));

        $isExpired = $this->sessionService->isSessionExpired($this->request);

        $this->assertTrue($isExpired);
    }

    public function test_detects_active_session(): void
    {
        // Set last activity to 30 minutes ago
        $this->request->session()->put('last_activity', Carbon::now()->subMinutes(30));

        $isExpired = $this->sessionService->isSessionExpired($this->request);

        $this->assertFalse($isExpired);
    }

    public function test_updates_activity(): void
    {
        $this->sessionService->updateActivity($this->request);

        $lastActivity = $this->request->session()->get('last_activity');
        $this->assertNotNull($lastActivity);
        $this->assertTrue(Carbon::parse($lastActivity)->diffInSeconds(now()) < 5);
    }

    public function test_invalidates_all_user_sessions(): void
    {
        // Create some mock sessions in database
        DB::table('sessions')->insert([
            [
                'id' => 'session1',
                'user_id' => $this->user->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Test Agent',
                'payload' => base64_encode(serialize([])),
                'last_activity' => now()->timestamp,
            ],
            [
                'id' => 'session2',
                'user_id' => $this->user->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Test Agent',
                'payload' => base64_encode(serialize([])),
                'last_activity' => now()->timestamp,
            ],
        ]);

        $this->sessionService->invalidateAllUserSessions($this->user);

        $remainingSessions = DB::table('sessions')
            ->where('user_id', $this->user->id)
            ->count();

        $this->assertEquals(0, $remainingSessions);
    }

    public function test_revokes_specific_session(): void
    {
        $sessionId = 'test-session-id';
        
        // Create a mock session
        DB::table('sessions')->insert([
            'id' => $sessionId,
            'user_id' => $this->user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
            'payload' => base64_encode(serialize([])),
            'last_activity' => now()->timestamp,
        ]);

        $result = $this->sessionService->revokeSession($sessionId, $this->user);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('sessions', ['id' => $sessionId]);
    }

    public function test_fails_to_revoke_nonexistent_session(): void
    {
        $result = $this->sessionService->revokeSession('nonexistent-session', $this->user);

        $this->assertFalse($result);
    }

    public function test_should_prompt_for_verification_on_ip_change(): void
    {
        $this->request->session()->put([
            'login_time' => now()->subHour(),
            'ip_address' => '192.168.1.1',
        ]);
        
        // Mock different IP
        $this->request = Request::create('/test', 'GET', [], [], [], ['REMOTE_ADDR' => '10.0.0.1']);
        $this->request->setLaravelSession($this->app['session.store']);
        $this->request->session()->put([
            'login_time' => now()->subHour(),
            'ip_address' => '192.168.1.1',
        ]);

        $shouldPrompt = $this->sessionService->shouldPromptForVerification($this->request);

        $this->assertTrue($shouldPrompt);
    }

    public function test_should_prompt_for_verification_on_old_session(): void
    {
        $this->request->session()->put([
            'login_time' => now()->subHours(25), // Older than 24 hours
            'ip_address' => '127.0.0.1',
        ]);

        $shouldPrompt = $this->sessionService->shouldPromptForVerification($this->request);

        $this->assertTrue($shouldPrompt);
    }

    public function test_cleans_up_expired_sessions(): void
    {
        $sessionLifetime = config('session.lifetime', 120);
        $expiredTime = now()->subMinutes($sessionLifetime + 10)->timestamp;
        $activeTime = now()->subMinutes(30)->timestamp;

        // Create expired and active sessions
        DB::table('sessions')->insert([
            [
                'id' => 'expired-session',
                'user_id' => $this->user->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Test Agent',
                'payload' => base64_encode(serialize([])),
                'last_activity' => $expiredTime,
            ],
            [
                'id' => 'active-session',
                'user_id' => $this->user->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Test Agent',
                'payload' => base64_encode(serialize([])),
                'last_activity' => $activeTime,
            ],
        ]);

        $deletedCount = $this->sessionService->cleanupExpiredSessions();

        $this->assertEquals(1, $deletedCount);
        $this->assertDatabaseMissing('sessions', ['id' => 'expired-session']);
        $this->assertDatabaseHas('sessions', ['id' => 'active-session']);
    }
}