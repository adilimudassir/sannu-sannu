<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SystemAdminPolicy
{
    /**
     * Determine whether the user can access the system admin panel.
     */
    public function accessAdminPanel(User $user): bool
    {
        return $user->isSystemAdmin();
    }

    /**
     * Determine whether the user can manage all tenants.
     */
    public function manageTenants(User $user): bool
    {
        return $user->isSystemAdmin();
    }

    /**
     * Determine whether the user can view platform analytics.
     */
    public function viewPlatformAnalytics(User $user): bool
    {
        return $user->isSystemAdmin();
    }

    /**
     * Determine whether the user can manage platform fees.
     */
    public function managePlatformFees(User $user): bool
    {
        return $user->isSystemAdmin();
    }

    /**
     * Determine whether the user can manage system settings.
     */
    public function manageSystemSettings(User $user): bool
    {
        return $user->isSystemAdmin();
    }

    /**
     * Determine whether the user can view all users across tenants.
     */
    public function viewAllUsers(User $user): bool
    {
        return $user->isSystemAdmin();
    }

    /**
     * Determine whether the user can manage user roles across tenants.
     */
    public function manageUserRoles(User $user): bool
    {
        return $user->isSystemAdmin();
    }

    /**
     * Determine whether the user can access system logs.
     */
    public function viewSystemLogs(User $user): bool
    {
        return $user->isSystemAdmin();
    }

    /**
     * Determine whether the user can manage platform integrations.
     */
    public function manageIntegrations(User $user): bool
    {
        return $user->isSystemAdmin();
    }

    /**
     * Determine whether the user can perform system maintenance.
     */
    public function performMaintenance(User $user): bool
    {
        return $user->isSystemAdmin();
    }

    /**
     * Determine whether the user can export platform data.
     */
    public function exportPlatformData(User $user): bool
    {
        return $user->isSystemAdmin();
    }

    /**
     * Determine whether the user can manage payment providers.
     */
    public function managePaymentProviders(User $user): bool
    {
        return $user->isSystemAdmin();
    }
}