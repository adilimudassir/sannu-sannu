<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Auth\Access\Response;

class TenantPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Only system admins can view all tenants
        return $user->isSystemAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Tenant $tenant): bool
    {
        // System admins can view any tenant, users can view tenants they have roles in
        return $user->isSystemAdmin() || $user->tenants()->where('id', $tenant->id)->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only system admins can create tenants
        return $user->isSystemAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Tenant $tenant): bool
    {
        // System admins can update any tenant, tenant admins can update tenants they manage
        return $user->isSystemAdmin() || 
               $user->hasRoleInTenant(Role::TENANT_ADMIN, $tenant->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Tenant $tenant): bool
    {
        // System admins can delete any tenant, tenant admins can delete tenants they manage
        return $user->isSystemAdmin() || 
               $user->hasRoleInTenant(Role::TENANT_ADMIN, $tenant->id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Tenant $tenant): bool
    {
        return $user->isSystemAdmin() || 
               $user->hasRoleInTenant(Role::TENANT_ADMIN, $tenant->id);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Tenant $tenant): bool
    {
        return $user->isSystemAdmin() || 
               $user->hasRoleInTenant(Role::TENANT_ADMIN, $tenant->id);
    }

    /**
     * Determine whether the user can manage tenant settings.
     */
    public function manageSettings(User $user, Tenant $tenant): bool
    {
        return $user->isSystemAdmin() || 
               $user->hasRoleInTenant(Role::TENANT_ADMIN, $tenant->id);
    }

    /**
     * Determine whether the user can invite users to the tenant.
     */
    public function inviteUsers(User $user, Tenant $tenant): bool
    {
        return $user->isSystemAdmin() || 
               $user->hasRoleInTenant(Role::TENANT_ADMIN, $tenant->id);
    }

    /**
     * Determine whether the user can suspend/activate the tenant.
     */
    public function suspend(User $user, Tenant $tenant): bool
    {
        // Only system admins can suspend tenants
        return $user->isSystemAdmin();
    }

    /**
     * Determine whether the user can view platform analytics for the tenant.
     */
    public function viewPlatformAnalytics(User $user, Tenant $tenant): bool
    {
        // Only system admins can view platform-level analytics
        return $user->isSystemAdmin();
    }
}