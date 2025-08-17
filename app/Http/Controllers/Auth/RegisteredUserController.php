<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Check if this is an organization registration redirect
        if ($request->has('registration_type') && $request->input('registration_type') === 'organization') {
            return redirect()->route('tenant-application.create');
        }

        // Create global user with contributor role by default
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => Role::CONTRIBUTOR, // All new users are contributors by default
        ]);

        event(new Registered($user));

        // Log successful registration for audit purposes
        AuditLogService::logAuthEvent('registration_success', $user, $request);

        Auth::login($user);

        // Redirect to email verification page since new users need to verify their email
        return redirect()->route('verification.notice');
    }
}
