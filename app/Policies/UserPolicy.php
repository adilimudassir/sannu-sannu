<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // System admins can view all users, tenant admins can view users in their tenant
        return $user->isSystemAdmin() || $user->isTenantAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // Users can view their own profile, system admins can view any user
        if ($user->id === $model->id || $user->isSystemAdmin()) {
            return true;
        }
        
        // Tenant admins can view users in their shared tenants
        $userTenants = $user->tenants()->pluck('id');
        $modelTenants = $model->tenants()->pluck('id');
        
        return $user->isTenantAdmin() && $userTenants->intersect($modelTenants)->isNotEmpty();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // System admins and tenant admins can create users (invite users)
        return $user->isSystemAdmin() || $user->isTenantAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Users can update their own profile, system admins can update any user
        if ($user->id === $model->id || $user->isSystemAdmin()) {
            return true;
        }
        
        // Tenant admins can update users in their shared tenants
        $userTenants = $user->tenants()->pluck('id');
        $modelTenants = $model->tenants()->pluck('id');
        
        return $user->isTenantAdmin() && $userTenants->intersect($modelTenants)->isNotEmpty();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Cannot delete themselves
        if ($user->id === $model->id) {
            return false;
        }
        
        // System admins can delete any user
        if ($user->isSystemAdmin()) {
            return true;
        }
        
        // Tenant admins can delete users in their shared tenants
        $userTenants = $user->tenants()->pluck('id');
        $modelTenants = $model->tenants()->pluck('id');
        
        return $user->isTenantAdmin() && $userTenants->intersect($modelTenants)->isNotEmpty();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        if ($user->isSystemAdmin()) {
            return true;
        }
        
        // Tenant admins can restore users in their shared tenants
        $userTenants = $user->tenants()->pluck('id');
        $modelTenants = $model->tenants()->pluck('id');
        
        return $user->isTenantAdmin() && $userTenants->intersect($modelTenants)->isNotEmpty();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        if ($user->isSystemAdmin()) {
            return true;
        }
        
        // Tenant admins can force delete users in their shared tenants
        $userTenants = $user->tenants()->pluck('id');
        $modelTenants = $model->tenants()->pluck('id');
        
        return $user->isTenantAdmin() && $userTenants->intersect($modelTenants)->isNotEmpty();
    }

    /**
     * Determine whether the user can change global roles.
     */
    public function changeGlobalRole(User $user, User $model): bool
    {
        // Cannot change their own role
        if ($user->id === $model->id) {
            return false;
        }
        
        // Only system admins can change global roles
        return $user->isSystemAdmin();
    }

    /**
     * Determine whether the user can manage tenant roles.
     */
    public function manageTenantRoles(User $user, User $model, int $tenantId): bool
    {
        // Cannot change their own roles
        if ($user->id === $model->id) {
            return false;
        }
        
        // System admins can manage any tenant roles
        if ($user->isSystemAdmin()) {
            return true;
        }
        
        // Tenant admins can manage roles within their tenant
        return $user->hasRoleInTenant(Role::TENANT_ADMIN, $tenantId);
    }

    /**
     * Determine whether the user can assign system admin role.
     */
    public function assignSystemAdminRole(User $user, User $model): bool
    {
        // Only system admins can assign system admin role, and not to themselves
        return $user->isSystemAdmin() && $user->id !== $model->id;
    }
}