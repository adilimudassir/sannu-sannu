<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Legacy Auth Routes
|--------------------------------------------------------------------------
|
| These routes are kept for backward compatibility but authentication
| is now handled globally through routes/global.php. These routes may
| be used for tenant-specific authentication flows if needed.
|
*/

// Authentication routes (no tenant context required)
Route::middleware('guest')->group(function () {
    Route::get('login', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'create'])
        ->name('login');
    Route::post('login', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('login.store');
    Route::get('register', [App\Http\Controllers\Auth\RegisteredUserController::class, 'create'])
        ->name('register');
    Route::post('register', [App\Http\Controllers\Auth\RegisteredUserController::class, 'store'])
        ->middleware('throttle:3,1')
        ->name('register.store');
    Route::get('forgot-password', [App\Http\Controllers\Auth\PasswordResetLinkController::class, 'create'])
        ->name('password.request');
    Route::post('forgot-password', [App\Http\Controllers\Auth\PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:3,1')
        ->name('password.email');
    Route::get('reset-password/{token}', [App\Http\Controllers\Auth\NewPasswordController::class, 'create'])
        ->name('password.reset');
    Route::post('reset-password', [App\Http\Controllers\Auth\NewPasswordController::class, 'store'])
        ->middleware('throttle:3,1')
        ->name('password.store');
});

// Redirect authenticated users away from auth pages
// Route::middleware('auth')->group(function () {
//     Route::get('login', fn () => redirect()->route('dashboard'));
//     Route::get('register', fn () => redirect()->route('dashboard'));
//     Route::get('forgot-password', fn () => redirect()->route('dashboard'));
// });

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Email verification routes (don't require verification)
    Route::get('verify-email', [App\Http\Controllers\Auth\EmailVerificationPromptController::class, '__invoke'])
        ->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', [App\Http\Controllers\Auth\VerifyEmailController::class, '__invoke'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('email/verification-notification', [App\Http\Controllers\Auth\EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // Routes that don't require email verification (logout should always work)
    Route::post('logout', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
