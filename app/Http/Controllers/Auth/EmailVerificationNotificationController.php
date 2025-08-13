<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Enums\Role;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    public function __construct(
        private AuditLogService $auditLogService
    ) {}

    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        
        if ($user->hasVerifiedEmail()) {
            return $this->redirectBasedOnRole($user);
        }

        // Check if user has requested too many verification emails recently
        $lastRequest = $request->session()->get('last_verification_request');
        if ($lastRequest && now()->diffInMinutes($lastRequest) < 1) {
            return back()->with('status', 'verification-throttled');
        }

        $user->sendEmailVerificationNotification();
        
        // Log the verification email request
        $this->auditLogService->logAuthEvent(
            'email_verification_requested',
            $user,
            $request
        );

        // Store timestamp to prevent spam
        $request->session()->put('last_verification_request', now());

        return back()->with('status', 'verification-link-sent');
    }

    /**
     * Redirect user based on their role after email verification.
     */
    private function redirectBasedOnRole($user): RedirectResponse
    {
        // System admins go to system dashboard
        if ($user->role === Role::SYSTEM_ADMIN) {
            return redirect()->intended(route('admin.dashboard'));
        }

        // Users with tenant admin roles go to tenant selection
        if ($user->needsTenantSelection()) {
            return redirect()->intended(route('tenant.select'));
        }

        // Contributors go to global dashboard
        return redirect()->intended(route('dashboard'));
    }
}
