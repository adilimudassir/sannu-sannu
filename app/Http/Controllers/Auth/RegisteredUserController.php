<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Role;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Auth\Events\Registered;

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

        // Redirect to global dashboard for contributors
        return redirect()->route('global.dashboard');
    }
}
