<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;
use App\Models\Contribution;
use Illuminate\Auth\Access\Response;

class ContributionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view contributions (filtered by tenant)
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Contribution $contribution): bool
    {
        // Users can view contributions within their tenant
        return $user->tenant_id === $contribution->tenant_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // All authenticated users can create contributions
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Contribution $contribution): bool
    {
        // Users can update their own contributions, or admins/managers can update any
        return $user->id === $contribution->user_id || 
               ($user->canManageProjects() && $user->tenant_id === $contribution->tenant_id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Contribution $contribution): bool
    {
        // Users can delete their own contributions, or admins/managers can delete any
        return $user->id === $contribution->user_id || 
               ($user->canManageProjects() && $user->tenant_id === $contribution->tenant_id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Contribution $contribution): bool
    {
        return $user->canManageProjects() && $user->tenant_id === $contribution->tenant_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Contribution $contribution): bool
    {
        return $user->role === Role::TENANT_ADMIN && $user->tenant_id === $contribution->tenant_id;
    }

    /**
     * Determine whether the user can approve the contribution.
     */
    public function approve(User $user, Contribution $contribution): bool
    {
        // Only project managers and tenant admins can approve contributions
        return $user->canManageProjects() && $user->tenant_id === $contribution->tenant_id;
    }
}