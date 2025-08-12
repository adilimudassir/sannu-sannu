<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Enums\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmailVerificationPromptController extends Controller
{
    /**
     * Show the email verification prompt page.
     */
    public function __invoke(Request $request): Response|RedirectResponse
    {
        $user = $request->user();
        
        if ($user->hasVerifiedEmail()) {
            // Redirect based on user role after verification
            return $this->redirectBasedOnRole($user);
        }

        return Inertia::render('auth/verify-email', [
            'status' => $request->session()->get('status'),
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
            ]
        ]);
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
        return redirect()->intended(route('global.dashboard'));
    }
}
