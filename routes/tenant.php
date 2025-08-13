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
| All routes here have tenant context available via the IdentifyTenant middleware.
|
*/

// Tenant-specific authenticated routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Tenant dashboard
    Route::get('dashboard', fn() => Inertia::render('dashboard'))
        ->name('dashboard');
    
    // Project management routes
    Route::resource('projects', ProjectController::class);
    
    // Project invitation routes (nested under projects)
    Route::resource('projects.invitations', ProjectInvitationController::class)
        ->shallow()
        ->only(['store']);
});

// Include tenant-specific settings routes
require __DIR__ . '/settings.php';
