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
            // Redirect to global login for unauthenticated users
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Convert string roles to Role enum instances
        $requiredRoles = array_map(function ($role) {
            return Role::from($role);
        }, $roles);

        // Get current tenant if available
        $tenant = app('tenant', null);
        
        // Check if user has any of the required roles (global or tenant-specific)
        $hasPermission = false;
        
        foreach ($requiredRoles as $role) {
            if ($this->userHasRole($user, $role, $tenant)) {
                $hasPermission = true;
                break;
            }
        }

        if (!$hasPermission) {
            abort(403, 'Insufficient permissions. Required roles: ' . implode(', ', $roles));
        }

        return $next($request);
    }

    /**
     * Check if user has the specified role
     */
    protected function userHasRole($user, Role $role, $tenant = null): bool
    {
        // Check global roles first
        if ($user->role === $role) {
            return true;
        }

        // Check tenant-specific roles if tenant context is available
        if ($tenant && method_exists($user, 'hasRoleInTenant')) {
            return $user->hasRoleInTenant($role, $tenant->id);
        }

        return false;
    }
}