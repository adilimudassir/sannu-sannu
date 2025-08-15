<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\ProjectInvitationController;
use App\Http\Controllers\Settings\PasswordController;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| These routes are available within tenant context, both for subdomain-based
| and path-based tenant routing. They handle tenant-specific functionality.
| All routes here have tenant context available via the IdentifyTenant middleware.
|
*/

// Handle tenant not found error from subdomain redirects
Route::get('/tenant-not-found', function () {
    $slug = request('slug');

    return Inertia::render('errors/tenant-not-found', [
        'slug' => $slug,
        'message' => "The organization \"{$slug}\" could not be found.",
    ]);
})->name('tenant.not-found');


Route::middleware('auth')->group(function () {
    // Tenant selection for admin users
    Route::get('select-tenant', [App\Http\Controllers\TenantSelectionController::class, 'show'])
        ->name('tenant.select');
    Route::post('select-tenant', [App\Http\Controllers\TenantSelectionController::class, 'store'])
        ->name('tenant.select.store');

    // Path-based tenant routes (for development and fallback)
    Route::prefix('{tenant:slug}')
        ->middleware(['tenant'])
        ->name('tenant.')
        ->group(function () {
            Route::middleware(['auth', 'verified'])->group(function () {
                // Tenant dashboard
                Route::get('dashboard', fn() => Inertia::render('dashboard'))
                    ->name('dashboard');

                // Project management routes
                Route::resource('projects', ProjectController::class);

                // Additional project lifecycle routes
                Route::patch('projects/{project}/activate', [ProjectController::class, 'activate'])
                    ->name('projects.activate');
                Route::patch('projects/{project}/pause', [ProjectController::class, 'pause'])
                    ->name('projects.pause');
                Route::patch('projects/{project}/complete', [ProjectController::class, 'complete'])
                    ->name('projects.complete');

                // Project invitation routes (nested under projects)
                Route::resource('projects.invitations', ProjectInvitationController::class)
                    ->shallow()
                    ->only(['store']);

                Route::middleware('auth')->group(function () {
                    Route::redirect('settings', '/settings/profile');

                    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
                    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
                    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

                    Route::get('settings/password', [PasswordController::class, 'edit'])->name('password.edit');

                    Route::put('settings/password', [PasswordController::class, 'update'])
                        ->middleware('throttle:6,1')
                        ->name('password.update');

                    Route::get('settings/appearance', function () {
                        return Inertia::render('settings/appearance');
                    })->name('appearance');
                });
            });
        });
});


