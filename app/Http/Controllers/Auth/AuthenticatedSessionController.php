<?php

namespace App\Http\Controllers\Auth;

use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use App\Services\SessionService;
use App\Services\AuditLogService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
use App\Http\Requests\Auth\LoginRequest;

class AuthenticatedSessionController extends Controller
{
    public function __construct(
        private SessionService $sessionService
    ) {}

    /**
     * Show the login page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = $request->user();

        // Create secure global session
        $this->sessionService->createGlobalSession($user, $request);

        // Log successful login
        AuditLogService::logAuthEvent('login_success', $user, $request);

        // Role-based redirect logic
        if ($user->isSystemAdmin()) {
            // System admins go to system dashboard
            return redirect()->intended(route('admin.dashboard'));
        } elseif ($user->needsTenantSelection()) {
            // Users with tenant admin/project manager roles need to select tenant
            return redirect()->intended(route('tenant.select'));
        } else {
            // Contributors go to global dashboard
            return redirect()->intended(route('dashboard'));
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Use SessionService to properly destroy session
        $this->sessionService->destroySession($request);

        return redirect('/')->with('status', 'You have been logged out successfully.');
    }
}
