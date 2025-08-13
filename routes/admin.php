<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ProjectController;

/*
|--------------------------------------------------------------------------
| System Admin Routes
|--------------------------------------------------------------------------
|
| These routes are only accessible to system administrators and handle
| platform-wide management functionality.
|
*/

Route::middleware('can:manage-platform')
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', fn() => Inertia::render('admin/dashboard'))
            ->name('dashboard');
        
        Route::get('tenants', fn() => Inertia::render('admin/tenants'))
            ->name('tenants');
        
        Route::get('users', fn() => Inertia::render('admin/users'))
            ->name('users');
        
        // Project management routes for system admin
        Route::resource('projects', ProjectController::class)->except(['show']);
        Route::get('projects/{project}', [ProjectController::class, 'show'])
            ->name('projects.show');
        
        // Project lifecycle management routes
        Route::patch('projects/{project}/activate', [ProjectController::class, 'activate'])
            ->name('projects.activate');
        Route::patch('projects/{project}/pause', [ProjectController::class, 'pause'])
            ->name('projects.pause');
        Route::patch('projects/{project}/complete', [ProjectController::class, 'complete'])
            ->name('projects.complete');
    });