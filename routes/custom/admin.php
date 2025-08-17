<?php

use App\Http\Controllers\Admin\ProjectController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| System Admin Routes
|--------------------------------------------------------------------------
|
| These routes are only accessible to system administrators and handle
| platform-wide management functionality.
|
*/

Route::middleware(['can:manage-platform', 'auth'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', fn () => Inertia::render('admin/dashboard'))
            ->name('dashboard');

        Route::get('tenant-applications', [\App\Http\Controllers\Admin\TenantApplicationController::class, 'index'])
            ->name('tenant-applications.index');

        Route::get('tenant-applications/{tenantApplication}', [\App\Http\Controllers\Admin\TenantApplicationController::class, 'show'])
            ->name('tenant-applications.show');

        Route::patch('tenant-applications/{tenantApplication}/approve', [\App\Http\Controllers\Admin\TenantApplicationController::class, 'approve'])
            ->name('tenant-applications.approve');
        Route::patch('tenant-applications/{tenantApplication}/reject', [\App\Http\Controllers\Admin\TenantApplicationController::class, 'reject'])
            ->name('tenant-applications.reject');

        Route::get('tenants', fn () => Inertia::render('admin/tenants'))
            ->name('tenants');

        Route::get('users', fn () => Inertia::render('admin/users'))
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
        Route::patch('projects/{project}/resume', [ProjectController::class, 'resume'])
            ->name('projects.resume');
        Route::patch('projects/{project}/cancel', [ProjectController::class, 'cancel'])
            ->name('projects.cancel');
    });
