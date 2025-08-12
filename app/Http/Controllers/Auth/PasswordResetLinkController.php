<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetLinkController extends Controller
{
    /**
     * Show the password reset link request page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/forgot-password', [
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(ForgotPasswordRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Log password reset request for audit purposes
        AuditLogService::logAuthEvent('password_reset_requested', null, $request, [
            'email' => $validated['email'],
        ]);

        Password::sendResetLink(
            $validated
        );

        return back()->with('status', __('A reset link will be sent if the account exists.'));
    }
}
