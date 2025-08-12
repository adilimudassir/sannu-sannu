<?php

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

Route::middleware('can:manage-platform')
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('dashboard', fn() => Inertia::render('admin/system-dashboard'))
            ->name('dashboard');
        
        Route::get('tenants', fn() => Inertia::render('admin/tenants'))
            ->name('tenants');
        
        Route::get('users', fn() => Inertia::render('admin/users'))
            ->name('users');
    });