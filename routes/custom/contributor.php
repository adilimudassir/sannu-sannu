<?php

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

    // Include user settings routes
    Route::group([
        'prefix' => 'settings',
    ], function () {
        Route::redirect('/', '/settings/profile');
        Route::get('/profile', [App\Http\Controllers\Settings\ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [App\Http\Controllers\Settings\ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [App\Http\Controllers\Settings\ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::get('/password', [App\Http\Controllers\Settings\PasswordController::class, 'edit'])->name('password.edit');
        Route::put('/password', [App\Http\Controllers\Settings\PasswordController::class, 'update'])
            ->middleware('throttle:6,1')
            ->name('password.update');
        Route::get('/appearance', fn() => Inertia::render('settings/appearance'))->name('appearance');
        Route::get('/sessions', [App\Http\Controllers\SessionManagementController::class, 'index'])
            ->name('sessions.index');
        Route::delete('/sessions/{sessionId}', [App\Http\Controllers\SessionManagementController::class, 'destroy'])
            ->name('sessions.destroy');
        Route::post('/sessions/destroy-others', [App\Http\Controllers\SessionManagementController::class, 'destroyOthers'])
            ->name('sessions.destroy-others');
        Route::post('/clear-tenant-context', [App\Http\Controllers\SessionManagementController::class, 'clearTenantContext'])
            ->name('sessions.clear-tenant-context');
    });
});