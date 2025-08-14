<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
// Main application routes (no tenant context)
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return Inertia::render('welcome');
})->name('home');
// Fallback route for Laravel's default authentication redirect
Route::redirect('/login-redirect', '/login')->name('login');

// Public project discovery routes
Route::prefix('projects')->name('public.projects.')->group(function () {
    Route::get('/', [App\Http\Controllers\PublicProjectController::class, 'index'])->name('index');
    Route::get('/search', [App\Http\Controllers\PublicProjectController::class, 'search'])->name('search');
    Route::get('/{project:slug}', [App\Http\Controllers\PublicProjectController::class, 'show'])->name('show');
});

require __DIR__.'/auth.php';

Route::middleware('auth')->group(function () {
    // Tenant selection for admin users
    Route::get('select-tenant', [App\Http\Controllers\TenantSelectionController::class, 'show'])
        ->name('tenant.select');
    Route::post('select-tenant', [App\Http\Controllers\TenantSelectionController::class, 'store'])
        ->name('tenant.select.store');

    // dashboard for contributors
    Route::get('dashboard', fn () => Inertia::render('dashboard/global'))
        ->name('dashboard');

    // Include user settings routes
    require __DIR__.'/user-settings.php';

    // Include system admin routes
    require __DIR__.'/admin.php';
});

// Handle tenant not found error from subdomain redirects
Route::get('/tenant-not-found', function () {
    $slug = request('slug');

    return Inertia::render('errors/tenant-not-found', [
        'slug' => $slug,
        'message' => "The organization \"{$slug}\" could not be found.",
    ]);
})->name('tenant.not-found');

// Path-based tenant routes (for development and fallback)
Route::prefix('{tenant:slug}')
    ->middleware(['web', 'tenant'])
    ->name('tenant.')
    ->group(function () {
        require __DIR__.'/tenant.php';
    });
