<?php

use App\Http\Controllers\Contributor\ProjectController;
use App\Http\Controllers\SessionManagementController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Global Settings Routes
|--------------------------------------------------------------------------
|
| These routes handle global user settings like profile, password, and
| session management that are available outside of tenant context.
|
*/

Route::middleware('auth')->group(function () {
    // dashboard for contributors
    Route::get('dashboard', fn() => Inertia::render('dashboard'))
        ->name('dashboard');

    // Projects for contributors
    Route::prefix('projects')->name('contributor.projects.')->group(function () {
        Route::get('/', [ProjectController::class, 'index'])
        ->name('index');
    Route::get('/{project:slug}', [ProjectController::class, 'show'])
        ->name('show');
    });

    // Include user settings routes
    Route::group([
        'prefix' => 'settings',
    ], function () {
        Route::redirect('/', '/settings/profile');
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::get('/password', [PasswordController::class, 'edit'])->name('password.edit');
        Route::put('/password', [PasswordController::class, 'update'])
            ->middleware('throttle:6,1')
            ->name('password.update');
        Route::get('/appearance', fn() => Inertia::render('settings/appearance'))->name('appearance');
        Route::get('/sessions', [SessionManagementController::class, 'index'])
            ->name('sessions.index');
        Route::delete('/sessions/{sessionId}', [SessionManagementController::class, 'destroy'])
            ->name('sessions.destroy');
        Route::post('/sessions/destroy-others', [SessionManagementController::class, 'destroyOthers'])
            ->name('sessions.destroy-others');
        Route::post('/clear-tenant-context', [SessionManagementController::class, 'clearTenantContext'])
            ->name('sessions.clear-tenant-context');
    });
});
