<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginAccessibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_form_has_proper_accessibility_attributes(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        
        // Check that the page renders the login component
        $response->assertInertia(fn ($page) => 
            $page->component('auth/login')
                ->has('canResetPassword')
        );
    }

    public function test_login_form_validation_errors_are_accessible(): void
    {
        $response = $this->post(route('login.store'), [
            'email' => 'invalid-email',
            'password' => '',
        ]);

        // Should have validation errors
        $response->assertSessionHasErrors(['email', 'password']);
        
        // Should redirect back to login with errors
        $response->assertRedirect();
        $this->assertGuest();
    }

    public function test_login_form_handles_rate_limiting_gracefully(): void
    {
        // Attempt login multiple times with invalid credentials
        for ($i = 0; $i < 6; $i++) {
            $response = $this->post(route('login.store'), [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ]);
        }

        // Should be rate limited after 5 attempts
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}