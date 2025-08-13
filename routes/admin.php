<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;

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
    });