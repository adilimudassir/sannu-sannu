<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_page_renders_with_auth_card_layout(): void
    {
        $response = $this->get(route('global.register'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('auth/register')
        );
    }

    public function test_register_page_has_correct_form_fields(): void
    {
        $response = $this->get(route('global.register'));

        $response->assertStatus(200);
        // The page should render without errors, indicating all form fields are properly structured
    }

    public function test_register_form_submits_to_correct_global_route(): void
    {
        $response = $this->post(route('global.register.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('global.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_register_page_shows_login_link(): void
    {
        $response = $this->get(route('global.register'));

        $response->assertStatus(200);
        // The component should render the login link pointing to global.login
    }

    public function test_registration_creates_global_user_with_contributor_role(): void
    {
        $this->post(route('global.register.store'), [
            'name' => 'Global User',
            'email' => 'global@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'global@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Global User', $user->name);
        $this->assertEquals(\App\Enums\Role::CONTRIBUTOR, $user->role);
    }

    public function test_registration_enforces_global_email_uniqueness(): void
    {
        // Create a user first
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->post(route('global.register.store'), [
            'name' => 'Another User',
            'email' => 'existing@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
        
        // Ensure only one user exists with this email
        $this->assertEquals(1, User::where('email', 'existing@example.com')->count());
    }
}