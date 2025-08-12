<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Enums\Role;
use App\Services\AuditLogService;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    public function __construct(
        private AuditLogService $auditLogService
    ) {}

    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        $user = $request->user();
        
        if ($user->hasVerifiedEmail()) {
            return $this->redirectBasedOnRole($user, true);
        }

        $request->fulfill();

        // Log successful email verification
        $this->auditLogService->logAuthEvent(
            'email_verified',
            $user,
            $request
        );

        return $this->redirectBasedOnRole($user, true);
    }

    /**
     * Redirect user based on their role after email verification.
     */
    private function redirectBasedOnRole($user, bool $verified = false): RedirectResponse
    {
        $verifiedParam = $verified ? '?verified=1' : '';
        
        // System admins go to system dashboard
        if ($user->role === Role::SYSTEM_ADMIN) {
            return redirect()->intended(route('admin.dashboard') . $verifiedParam);
        }

        // Users with tenant admin roles go to tenant selection
        if ($user->needsTenantSelection()) {
            return redirect()->intended(route('tenant.select') . $verifiedParam);
        }

        // Contributors go to global dashboard
        return redirect()->intended(route('global.dashboard') . $verifiedParam);
    }
}
