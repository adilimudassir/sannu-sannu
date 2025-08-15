<?php

use Inertia\Inertia;
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
// Main application routes (no tenant context)
Route::get('/', function () {
    // if (auth()->check()) {
    //     return redirect()->route('dashboard');
    // }
    return Inertia::render('welcome');
})->name('home');

// Public project discovery routes
Route::prefix('public/projects')->name('public.projects.')->group(function () {
    Route::get('/', [App\Http\Controllers\PublicProjectController::class, 'index'])->name('index');
    Route::get('/search', [App\Http\Controllers\PublicProjectController::class, 'search'])->name('search');
    Route::get('/{project:slug}', [App\Http\Controllers\PublicProjectController::class, 'show'])->name('show');
});

require __DIR__ . '/custom/auth.php';
require __DIR__ . '/custom/contributor.php';
require __DIR__ . '/custom/admin.php';
require __DIR__ . '/custom/tenant.php';
