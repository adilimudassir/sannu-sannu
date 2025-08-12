<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectInvitationController;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| These routes are available within tenant context, both for subdomain-based
| and path-based tenant routing. They handle tenant-specific functionality.
|
*/

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', fn() => Inertia::render('dashboard'))
        ->name('dashboard');
});

// Project management
Route::resource('projects.invitations', ProjectInvitationController::class)
    ->shallow()
    ->only(['store']);

Route::resource('projects', ProjectController::class);

// Include tenant-specific settings and auth routes
require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
