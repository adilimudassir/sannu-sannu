<?php

use Illuminate\Support\Facades\Route;

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

// Global routes (no tenant context)
require __DIR__.'/global.php';

// Subdomain-based tenant routes
// Route::domain('{tenant:slug}.' . parse_url(config('app.url'), PHP_URL_HOST))
//     ->middleware(['web'])
//     ->group(function () {
//         require __DIR__.'/tenant.php';
//     });

// Path-based tenant routes (fallback for development or custom domains)
Route::prefix('{tenant:slug}')
    ->middleware(['web'])
    ->name('tenant.')
    ->group(function () {
        require __DIR__.'/tenant.php';
    });
