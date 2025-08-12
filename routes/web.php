<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectInvitationController;

// Main application routes (no tenant context)
Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

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
    // Global dashboard for contributors
    Route::get('dashboard', function () {
        return Inertia::render('dashboard/global');
    })->name('global.dashboard');
    
    // Tenant selection for admin users
    Route::get('select-tenant', [App\Http\Controllers\TenantSelectionController::class, 'show'])
        ->name('tenant.select');
    Route::post('select-tenant', [App\Http\Controllers\TenantSelectionController::class, 'store'])
        ->name('tenant.select.store');
    
    // System admin routes
    Route::middleware('can:manage-platform')->prefix('admin')->name('admin.')->group(function () {
        Route::get('dashboard', function () {
            return Inertia::render('admin/system-dashboard');
        })->name('dashboard');
        Route::get('tenants', function () {
            return Inertia::render('admin/tenants');
        })->name('tenants');
        Route::get('users', function () {
            return Inertia::render('admin/users');
        })->name('users');
    });
    
    Route::post('logout', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});

// Handle tenant not found error from subdomain redirects
Route::get('/tenant-not-found', function () {
    $slug = request('slug');
    return Inertia::render('errors/tenant-not-found', [
        'slug' => $slug,
        'message' => 'The organization "' . $slug . '" could not be found.'
    ]);
})->name('tenant.not-found');

// Subdomain-based tenant routes
Route::domain('{tenant:slug}.' . parse_url(config('app.url'), PHP_URL_HOST))
    ->middleware(['web'])
    ->group(function () {
        Route::middleware(['auth', 'verified'])->group(function () {
            Route::get('dashboard', function () {
                return Inertia::render('dashboard');
            })->name('dashboard');
        });

        Route::resource('projects.invitations', ProjectInvitationController::class)->shallow()->only(['store']);
        Route::resource('projects', ProjectController::class);

        require __DIR__.'/settings.php';
        require __DIR__.'/auth.php';
    });

// Path-based tenant routes (fallback for development or custom domains)
Route::prefix('{tenant:slug}')
    ->middleware(['web'])
    ->group(function () {
        Route::middleware(['auth', 'verified'])->group(function () {
            Route::get('dashboard', function () {
                return Inertia::render('dashboard');
            })->name('tenant.dashboard');
        });

        Route::resource('projects.invitations', ProjectInvitationController::class)->shallow()->only(['store']);
        Route::resource('projects', ProjectController::class);

        require __DIR__.'/settings.php';
        require __DIR__.'/auth.php';
    });
