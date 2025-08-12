<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Services\AuditLogService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function __construct(
        private AuditLogService $auditLogService
    ) {}

    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Update the user's profile settings.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $originalData = $user->only(['name', 'email']);
        
        $user->fill($request->validated());

        $emailChanged = false;
        if ($user->isDirty('email')) {
            $emailChanged = true;
            $user->email_verified_at = null;
        }

        $changes = $user->getDirty();
        $user->save();

        // Log profile changes for audit purposes
        if (!empty($changes)) {
            $this->auditLogService->logProfileUpdate($user, $originalData, $changes, $request->ip());
            
            if ($emailChanged) {
                $this->auditLogService->logEmailChange($user, $originalData['email'], $changes['email'], $request->ip());
            }
        }

        $message = $emailChanged 
            ? 'Profile updated successfully. Please verify your new email address.'
            : 'Profile updated successfully.';

        return redirect('/settings/profile')->with('status', $message);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Log account deletion for audit purposes
        $this->auditLogService->logAccountDeletion($user, $request->ip(), 'User requested account deletion');

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('status', 'Your account has been successfully deleted.');
    }
}
