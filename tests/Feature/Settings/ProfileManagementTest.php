<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class ProfileManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_profile_settings_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/settings/profile');

        $response->assertOk();
        $response->assertInertia(fn ($page) => 
            $page->component('settings/profile')
                ->has('mustVerifyEmail')
                ->where('mustVerifyEmail', true) // User model implements MustVerifyEmail
        );
    }

    public function test_user_can_update_profile_information(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
        ]);

        $response = $this->actingAs($user)->patch('/settings/profile', [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        $response->assertRedirect('/settings/profile');
        $response->assertSessionHas('status');

        $user->refresh();
        $this->assertEquals('Updated Name', $user->name);
        $this->assertEquals('updated@example.com', $user->email);
        $this->assertNull($user->email_verified_at); // Email verification should be reset
    }

    public function test_email_verification_is_reset_when_email_is_changed(): void
    {
        $user = User::factory()->create([
            'email' => 'original@example.com',
            'email_verified_at' => now(),
        ]);

        Log::shouldReceive('info')->twice(); // Profile update and email change logs

        $response = $this->actingAs($user)->patch('/settings/profile', [
            'name' => $user->name,
            'email' => 'new@example.com',
        ]);

        $response->assertRedirect('/settings/profile');
        
        $user->refresh();
        $this->assertEquals('new@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_is_not_reset_when_email_is_unchanged(): void
    {
        $verifiedAt = now();
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'email_verified_at' => $verifiedAt,
        ]);

        Log::shouldReceive('info')
            ->once()
            ->with('Profile: User profile updated', \Mockery::type('array'));

        $response = $this->actingAs($user)->patch('/settings/profile', [
            'name' => 'Updated Name',
            'email' => 'test@example.com', // Same email
        ]);

        $response->assertRedirect('/settings/profile');
        
        $user->refresh();
        $this->assertEquals('Updated Name', $user->name);
        $this->assertEquals($verifiedAt->timestamp, $user->email_verified_at->timestamp);
    }

    public function test_profile_update_requires_valid_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch('/settings/profile', [
            'name' => '', // Required field
            'email' => 'invalid-email', // Invalid email format
        ]);

        $response->assertSessionHasErrors(['name', 'email']);
    }

    public function test_email_must_be_unique(): void
    {
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);
        $user = User::factory()->create(['email' => 'user@example.com']);

        $response = $this->actingAs($user)->patch('/settings/profile', [
            'name' => $user->name,
            'email' => 'existing@example.com',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        Log::shouldReceive('warning')
            ->once()
            ->with('Profile: User account deleted', \Mockery::type('array'));

        $response = $this->actingAs($user)->delete('/settings/profile', [
            'password' => 'password',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('status');
        
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_account_deletion_requires_correct_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $response = $this->actingAs($user)->delete('/settings/profile', [
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_profile_updates_are_logged_for_audit(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
        ]);

        Log::shouldReceive('info')
            ->once()
            ->with('Profile: User profile updated', \Mockery::on(function ($data) use ($user) {
                return $data['event'] === 'profile_updated' &&
                       $data['user_id'] === $user->id &&
                       $data['original_data']['name'] === 'Original Name' &&
                       $data['changes']['name'] === 'Updated Name';
            }));

        $this->actingAs($user)->patch('/settings/profile', [
            'name' => 'Updated Name',
            'email' => 'original@example.com',
        ]);
    }

    public function test_email_changes_are_specifically_logged(): void
    {
        $user = User::factory()->create([
            'email' => 'original@example.com',
        ]);

        Log::shouldReceive('info')->twice(); // Profile update and email change

        $this->actingAs($user)->patch('/settings/profile', [
            'name' => $user->name,
            'email' => 'new@example.com',
        ]);
    }

    public function test_account_deletion_is_logged(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        Log::shouldReceive('warning')
            ->once()
            ->with('Profile: User account deleted', \Mockery::on(function ($data) use ($user) {
                return $data['event'] === 'account_deleted' &&
                       $data['user_id'] === $user->id &&
                       $data['reason'] === 'User requested account deletion';
            }));

        $this->actingAs($user)->delete('/settings/profile', [
            'password' => 'password',
        ]);
    }
}