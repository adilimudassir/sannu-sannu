<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_email_verification_flow_for_contributor(): void
    {
        Mail::fake();

        // 1. Register a new user
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'john@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNull($user->email_verified_at);

        // 2. User should be redirected to email verification page
        $response->assertRedirect('/verify-email');

        // 3. Access the verification page
        $response = $this->actingAs($user)->get('/verify-email');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('auth/verify-email')
                ->where('user.email', 'john@example.com')
        );

        // 4. Request a new verification email
        $response = $this->actingAs($user)
            ->from('/verify-email')
            ->post('/email/verification-notification');

        $response->assertRedirect('/verify-email');
        $response->assertSessionHas('status', 'verification-link-sent');

        // 5. Verify email using the verification link
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        // 6. User should be redirected to global dashboard after verification
        $response->assertRedirect(route('dashboard') . '?verified=1');

        // 7. Verify the user's email is now verified
        $this->assertTrue($user->fresh()->hasVerifiedEmail());

        // 8. User should now be able to access protected routes
        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_complete_email_verification_flow_for_system_admin(): void
    {
        Mail::fake();

        // Create system admin user
        $user = User::factory()->unverified()->create([
            'role' => Role::SYSTEM_ADMIN,
            'email' => 'admin@example.com',
        ]);

        // Verify email
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        // System admin should be redirected to admin dashboard
        $response->assertRedirect(route('admin.dashboard') . '?verified=1');

        // Verify the user's email is now verified
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_unverified_user_cannot_access_protected_features(): void
    {
        $user = User::factory()->unverified()->create([
            'role' => Role::CONTRIBUTOR,
        ]);

        // Should be redirected to verification page
        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertRedirect('/verify-email');

        $response = $this->actingAs($user)->get('/select-tenant');
        $response->assertRedirect('/verify-email');

        $response = $this->actingAs($user)->get('/settings/sessions');
        $response->assertRedirect('/verify-email');
    }

    public function test_verification_link_expiration_handling(): void
    {
        $user = User::factory()->unverified()->create([
            'role' => Role::CONTRIBUTOR,
        ]);

        // Create an expired verification URL (expired 1 minute ago)
        $expiredUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->subMinute(),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // Attempt to use expired link
        $response = $this->actingAs($user)->get($expiredUrl);

        // Should get a 403 response due to invalid signature
        $response->assertStatus(403);

        // User should still be unverified
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_verification_throttling_works(): void
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
}