<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

test('global reset password link screen can be rendered', function () {
    $response = $this->get('/forgot-password');

    $response->assertStatus(200);
});

test('global reset password link can be requested', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class);
});

test('global reset password screen can be rendered', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
        $response = $this->get('/reset-password/'.$notification->token);

        $response->assertStatus(200);

        return true;
    });
});

test('global password can be reset with valid token', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $response = $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        return true;
    });
});

test('global password reset handles invalid email gracefully', function () {
    $response = $this->post('/forgot-password', ['email' => 'invalid@example.com']);

    $response->assertRedirect()
        ->assertSessionHas('status', 'A reset link will be sent if the account exists.');
});

test('global password reset requires matching password confirmation', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $response = $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'differentpassword',
        ]);

        $response->assertSessionHasErrors(['password']);

        return true;
    });
});

test('global password reset requires valid token', function () {
    $user = User::factory()->create();

    $response = $this->post('/reset-password', [
        'token' => 'invalid-token',
        'email' => $user->email,
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $response->assertSessionHasErrors(['email']);
});