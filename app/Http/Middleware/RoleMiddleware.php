<?php

namespace App\Http\Middleware;

use Closure;
use App\Enums\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            // Try to get tenant from the request for proper login redirect
            $tenant = $request->route('tenant');
            if ($tenant) {
                return redirect()->route('login', ['tenant' => $tenant]);
            }
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Convert string roles to Role enum instances
        $requiredRoles = array_map(function ($role) {
            return Role::from($role);
        }, $roles);

        // Check if user has any of the required roles
        if (!$user->hasAnyRole($requiredRoles)) {
            abort(403, 'Insufficient permissions. Required roles: ' . implode(', ', $roles));
        }

        return $next($request);
    }
}