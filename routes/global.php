<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Global Routes
|--------------------------------------------------------------------------
|
| These routes are available globally without tenant context.
| They include authentication, profile management, and system admin routes.
|
*/

// Main application routes (no tenant context)
Route::get('/', fn() => Inertia::render('welcome'))->name('home');

// Global authentication routes (no tenant context required)
Route::middleware('guest')->group(function () {
    Route::get('login', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'create'])
        ->name('global.login');
    Route::post('login', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('global.login.store');
    Route::get('register', [App\Http\Controllers\Auth\RegisteredUserController::class, 'create'])
        ->name('global.register');
    Route::post('register', [App\Http\Controllers\Auth\RegisteredUserController::class, 'store'])
        ->middleware('throttle:3,1')
        ->name('global.register.store');
    Route::get('forgot-password', [App\Http\Controllers\Auth\PasswordResetLinkController::class, 'create'])
        ->name('global.password.request');
    Route::post('forgot-password', [App\Http\Controllers\Auth\PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:3,1')
        ->name('global.password.email');
    Route::get('reset-password/{token}', [App\Http\Controllers\Auth\NewPasswordController::class, 'create'])
        ->name('global.password.reset');
    Route::post('reset-password', [App\Http\Controllers\Auth\NewPasswordController::class, 'store'])
        ->middleware('throttle:3,1')
        ->name('global.password.store');
});

// Global authenticated routes
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
    
    // Routes that require email verification
    Route::middleware('verified')->group(function () {
        // Global dashboard for contributors
        Route::get('dashboard', fn() => Inertia::render('dashboard/global'))
            ->name('global.dashboard');
        
        // Tenant selection for admin users
        Route::get('select-tenant', [App\Http\Controllers\TenantSelectionController::class, 'show'])
            ->name('tenant.select');
        Route::post('select-tenant', [App\Http\Controllers\TenantSelectionController::class, 'store'])
            ->name('tenant.select.store');
        
        // Include global settings routes
        require __DIR__.'/global-settings.php';
        
        // Include system admin routes
        require __DIR__.'/admin.php';
    });
    
    // Routes that don't require email verification (logout should always work)
    Route::post('logout', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});

// Handle tenant not found error from subdomain redirects
Route::get('/tenant-not-found', function () {
    $slug = request('slug');
    return Inertia::render('errors/tenant-not-found', [
        'slug' => $slug,
        'message' => "The organization \"{$slug}\" could not be found."
    ]);
})->name('tenant.not-found');