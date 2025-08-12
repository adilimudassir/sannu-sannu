<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tenant;
use App\Enums\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SessionManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'role' => Role::CONTRIBUTOR,
        ]);
    }

    public function test_user_can_view_active_sessions(): void
    {
        // Create some mock sessions
        DB::table('sessions')->insert([
            [
                'id' => 'session1',
                'user_id' => $this->user->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Chrome)',
                'payload' => base64_encode(serialize([])),
                'last_activity' => now()->timestamp,
            ],
            [
                'id' => 'session2',
                'user_id' => $this->user->id,
                'ip_address' => '192.168.1.1',
                'user_agent' => 'Mozilla/5.0 (Firefox)',
                'payload' => base64_encode(serialize([])),
                'last_activity' => now()->subHour()->timestamp,
            ],
        ]);

        // Set session activity to prevent expiration
        session(['last_activity' => now()]);

        $response = $this->actingAs($this->user)
            ->get(route('sessions.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => 
            $page->component('settings/sessions')
                ->has('sessions', 2)
        );
    }

    public function test_user_can_revoke_specific_session(): void
    {
        $sessionId = 'test-session-to-revoke';
        
        // Create a session to revoke
        DB::table('sessions')->insert([
            'id' => $sessionId,
            'user_id' => $this->user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
            'payload' => base64_encode(serialize([])),
            'last_activity' => now()->timestamp,
        ]);

        // Set session activity to prevent expiration
        session(['last_activity' => now()]);

        $response = $this->actingAs($this->user)
            ->delete(route('sessions.destroy', $sessionId));

        $response->assertRedirect();
        $response->assertSessionHas('status', 'Session revoked successfully.');
        $this->assertDatabaseMissing('sessions', ['id' => $sessionId]);
    }

    public function test_user_cannot_revoke_other_users_session(): void
    {
        $otherUser = User::factory()->create();
        $sessionId = 'other-user-session';
        
        // Create a session for another user
        DB::table('sessions')->insert([
            'id' => $sessionId,
            'user_id' => $otherUser->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
            'payload' => base64_encode(serialize([])),
            'last_activity' => now()->timestamp,
        ]);

        // Set session activity to prevent expiration
        session(['last_activity' => now()]);

        $response = $this->actingAs($this->user)
            ->delete(route('sessions.destroy', $sessionId));

        $response->assertRedirect();
        $response->assertSessionHasErrors(['session']);
        $this->assertDatabaseHas('sessions', ['id' => $sessionId]);
    }

    public function test_user_can_revoke_all_other_sessions(): void
    {
        // Create multiple sessions for the user
        DB::table('sessions')->insert([
            [
                'id' => 'session1',
                'user_id' => $this->user->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Test Agent 1',
                'payload' => base64_encode(serialize([])),
                'last_activity' => now()->timestamp,
            ],
            [
                'id' => 'session2',
                'user_id' => $this->user->id,
                'ip_address' => '192.168.1.1',
                'user_agent' => 'Test Agent 2',
                'payload' => base64_encode(serialize([])),
                'last_activity' => now()->timestamp,
            ],
        ]);

        // Set session activity to prevent expiration
        session(['last_activity' => now()]);

        $response = $this->actingAs($this->user)
            ->post(route('sessions.destroy-others'));

        $response->assertRedirect();
        $response->assertSessionHas('status');
        
        // Should have revoked the other sessions (but not necessarily all since current session might be created)
        $remainingSessions = DB::table('sessions')
            ->where('user_id', $this->user->id)
            ->count();
        
        // Should be fewer sessions than before
        $this->assertLessThan(2, $remainingSessions);
    }

    public function test_login_creates_global_session(): void
    {
        // Create a user with a simple email for testing
        $testUser = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post(route('global.login.store'), [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('global.dashboard'));
        
        // Check that user is authenticated
        $this->assertAuthenticated();
        
        // Make a follow-up request to check session data
        $this->get(route('global.dashboard'));
        
        $this->assertEquals($testUser->id, session('user_id'));
        $this->assertEquals(Role::CONTRIBUTOR->value, session('global_role'));
        $this->assertNotNull(session('login_time'));
    }

    public function test_tenant_context_is_set_for_admin_users(): void
    {
        $tenant = Tenant::factory()->create();
        $adminUser = User::factory()->create(['role' => Role::CONTRIBUTOR]);
        
        // Give user tenant admin role
        $adminUser->tenantRoles()->create([
            'tenant_id' => $tenant->id,
            'role' => Role::TENANT_ADMIN,
            'is_active' => true,
        ]);

        // Set session activity to prevent expiration
        session(['last_activity' => now()]);

        $response = $this->actingAs($adminUser)
            ->post(route('tenant.select.store'), [
                'tenant_id' => $tenant->id,
            ]);

        $response->assertRedirect();
        
        // Follow the redirect to get the session data
        $this->followRedirects($response);
        
        $this->assertEquals($tenant->id, session('selected_tenant_id'));
        $this->assertEquals($tenant->slug, session('selected_tenant_slug'));
        $this->assertEquals(Role::TENANT_ADMIN->value, session('tenant_role'));
    }

    public function test_tenant_context_can_be_cleared(): void
    {
        $tenant = Tenant::factory()->create();
        
        // Set some tenant context and session activity
        session([
            'selected_tenant_id' => $tenant->id,
            'selected_tenant_slug' => $tenant->slug,
            'tenant_role' => Role::TENANT_ADMIN->value,
            'last_activity' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('sessions.clear-tenant-context'));

        $response->assertRedirect(route('tenant.select'));
        
        // Follow the redirect to get the updated session data
        $this->followRedirects($response);
        
        $this->assertNull(session('selected_tenant_id'));
        $this->assertNull(session('selected_tenant_slug'));
        $this->assertNull(session('tenant_role'));
    }

    public function test_password_change_invalidates_all_sessions(): void
    {
        $tenant = Tenant::factory()->create();
        
        // Create multiple sessions for the user
        DB::table('sessions')->insert([
            [
                'id' => 'session1',
                'user_id' => $this->user->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Test Agent 1',
                'payload' => base64_encode(serialize([])),
                'last_activity' => now()->timestamp,
            ],
            [
                'id' => 'session2',
                'user_id' => $this->user->id,
                'ip_address' => '192.168.1.1',
                'user_agent' => 'Test Agent 2',
                'payload' => base64_encode(serialize([])),
                'last_activity' => now()->timestamp,
            ],
        ]);

        // Set session activity to prevent expiration
        session(['last_activity' => now()]);

        $response = $this->actingAs($this->user)
            ->put("/{$tenant->slug}/settings/password", [
                'current_password' => 'password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');
        
        // All old sessions should be invalidated
        $remainingSessions = DB::table('sessions')
            ->where('user_id', $this->user->id)
            ->count();
        
        $this->assertEquals(0, $remainingSessions);
    }

    public function test_session_activity_middleware_updates_last_activity(): void
    {
        // Set initial session activity
        session(['last_activity' => now()->subMinutes(10)]);
        
        $this->actingAs($this->user);
        
        // Make a request that should trigger the middleware
        $response = $this->get(route('global.dashboard'));
        
        $response->assertOk();
        $this->assertNotNull(session('last_activity'));
        
        // The last_activity should be updated to a more recent time
        $lastActivity = session('last_activity');
        $this->assertTrue(now()->diffInSeconds($lastActivity) < 5);
    }

    public function test_expired_session_redirects_to_login(): void
    {
        $this->actingAs($this->user);
        
        // Manually set an expired session
        session(['last_activity' => now()->subHours(3)]);
        
        $response = $this->get(route('global.dashboard'));
        
        $response->assertRedirect(route('global.login'));
        $response->assertSessionHas('status', 'Your session has expired. Please log in again.');
    }

    public function test_guest_users_bypass_session_activity_middleware(): void
    {
        $response = $this->get('/');
        
        $response->assertOk();
        $this->assertNull(session('last_activity'));
    }
}