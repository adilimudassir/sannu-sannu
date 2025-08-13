<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_screen_can_be_rendered(): void
    {
        $user = User::factory()->unverified()->create([
            'role' => Role::CONTRIBUTOR,
        ]);

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('auth/verify-email')
                ->has('user')
                ->where('user.email', $user->email)
        );
    }

    public function test_email_can_be_verified(): void
    {
        $user = User::factory()->unverified()->create([
            'role' => Role::CONTRIBUTOR,
        ]);

        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect(route('dashboard') . '?verified=1');
    }

    public function test_email_verification_redirects_based_on_user_role(): void
    {
        // Test contributor redirect
        $contributor = User::factory()->unverified()->create([
            'role' => Role::CONTRIBUTOR,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $contributor->id, 'hash' => sha1($contributor->email)]
        );

        $response = $this->actingAs($contributor)->get($verificationUrl);
        $response->assertRedirect(route('dashboard') . '?verified=1');

        // Test system admin redirect
        $systemAdmin = User::factory()->unverified()->create([
            'role' => Role::SYSTEM_ADMIN,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $systemAdmin->id, 'hash' => sha1($systemAdmin->email)]
        );

        $response = $this->actingAs($systemAdmin)->get($verificationUrl);
        $response->assertRedirect(route('admin.dashboard') . '?verified=1');
    }

    public function test_email_is_not_verified_with_invalid_hash(): void
    {
        $user = User::factory()->unverified()->create([
            'role' => Role::CONTRIBUTOR,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong-email')]
        );

        $this->actingAs($user)->get($verificationUrl);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_verified_user_is_redirected_to_appropriate_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => Role::CONTRIBUTOR,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertRedirect(route('dashboard'));
    }

    public function test_verification_email_can_be_resent(): void
    {
        $user = User::factory()->unverified()->create([
            'role' => Role::CONTRIBUTOR,
        ]);

        $response = $this->actingAs($user)
            ->from('/verify-email')
            ->post('/email/verification-notification');

        $response->assertRedirect('/verify-email');
        $response->assertSessionHas('status', 'verification-link-sent');
    }

    public function test_verification_email_resend_is_throttled(): void
    {
        $user = User::factory()->unverified()->create([
            'role' => Role::CONTRIBUTOR,
        ]);

        // First request should succeed
        $response = $this->actingAs($user)
            ->from('/verify-email')
            ->post('/email/verification-notification');

        $response->assertRedirect('/verify-email');
        $response->assertSessionHas('status', 'verification-link-sent');

        // Immediate second request should be throttled
        $response = $this->actingAs($user)
            ->from('/verify-email')
            ->post('/email/verification-notification');

        $response->assertRedirect('/verify-email');
        $response->assertSessionHas('status', 'verification-throttled');
    }

    public function test_unverified_user_cannot_access_protected_routes(): void
    {
        $user = User::factory()->unverified()->create([
            'role' => Role::CONTRIBUTOR,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect('/verify-email');
    }

    public function test_verified_user_can_access_protected_routes(): void
    {
        $user = User::factory()->create([
            'role' => Role::CONTRIBUTOR,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
    }

    public function test_tenant_admin_with_verified_email_redirects_to_tenant_selection(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'role' => Role::CONTRIBUTOR,
            'email_verified_at' => now(),
        ]);

        // Give user tenant admin role
        $user->tenantRoles()->create([
            'tenant_id' => $tenant->id,
            'role' => Role::TENANT_ADMIN,
            'is_active' => true,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);
        $response->assertRedirect(route('tenant.select') . '?verified=1');
    }

    public function test_email_verification_logs_audit_events(): void
    {
        $user = User::factory()->unverified()->create([
            'role' => Role::CONTRIBUTOR,
        ]);

        // Mock the audit log service for both events
        $auditMock = $this->mock(\App\Services\AuditLogService::class);
        
        $auditMock->shouldReceive('logAuthEvent')
            ->once()
            ->with('email_verification_requested', \Mockery::type(\App\Models\User::class), \Mockery::type(\Illuminate\Http\Request::class));

        $auditMock->shouldReceive('logAuthEvent')
            ->once()
            ->with('email_verified', \Mockery::type(\App\Models\User::class), \Mockery::type(\Illuminate\Http\Request::class));

        $this->actingAs($user)
            ->from('/verify-email')
            ->post('/email/verification-notification');

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->actingAs($user)->get($verificationUrl);
    }
}