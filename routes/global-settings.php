<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Global Settings Routes
|--------------------------------------------------------------------------
|
| These routes handle global user settings like profile, password, and
| session management that are available outside of tenant context.
|
*/

// Global settings routes
Route::redirect('settings', '/settings/profile');
Route::get('settings/profile', [App\Http\Controllers\Settings\ProfileController::class, 'edit'])->name('profile.edit');
Route::patch('settings/profile', [App\Http\Controllers\Settings\ProfileController::class, 'update'])->name('profile.update');
Route::delete('settings/profile', [App\Http\Controllers\Settings\ProfileController::class, 'destroy'])->name('profile.destroy');
Route::get('settings/password', [App\Http\Controllers\Settings\PasswordController::class, 'edit'])->name('password.edit');
Route::put('settings/password', [App\Http\Controllers\Settings\PasswordController::class, 'update'])
    ->middleware('throttle:6,1')
    ->name('password.update');
Route::get('settings/appearance', fn() => Inertia::render('settings/appearance'))->name('appearance');
Route::get('settings/sessions', [App\Http\Controllers\SessionManagementController::class, 'index'])
    ->name('sessions.index');
Route::delete('settings/sessions/{sessionId}', [App\Http\Controllers\SessionManagementController::class, 'destroy'])
    ->name('sessions.destroy');
Route::post('settings/sessions/destroy-others', [App\Http\Controllers\SessionManagementController::class, 'destroyOthers'])
    ->name('sessions.destroy-others');
Route::post('settings/clear-tenant-context', [App\Http\Controllers\SessionManagementController::class, 'clearTenantContext'])
    ->name('sessions.clear-tenant-context');