<?php

namespace App\Policies;

use App\Models\User;
use App\Enums\Role;

class PlatformPolicy
{
    /**
     * Determine if the user can manage the platform (system admin only)
     */
    public function managePlatform(User $user): bool
    {
        return $user->hasRole(Role::SYSTEM_ADMIN);
    }

    /**
     * Determine if the user can view all tenants
     */
    public function viewAllTenants(User $user): bool
    {
        return $user->hasRole(Role::SYSTEM_ADMIN);
    }

    /**
     * Determine if the user can view all users
     */
    public function viewAllUsers(User $user): bool
    {
        return $user->hasRole(Role::SYSTEM_ADMIN);
    }

    /**
     * Determine if the user can view system analytics
     */
    public function viewSystemAnalytics(User $user): bool
    {
        return $user->hasRole(Role::SYSTEM_ADMIN);
    }
}