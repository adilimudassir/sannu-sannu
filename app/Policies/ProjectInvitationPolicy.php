<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;
use App\Models\ProjectInvitation;
use Illuminate\Auth\Access\Response;

class ProjectInvitationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Project managers and tenant admins can view invitations
        return $user->canManageProjects();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ProjectInvitation $invitation): bool
    {
        // Users can view invitations they sent or received, or admins/managers can view any
        return $user->id === $invitation->invited_by || 
               $user->email === $invitation->email ||
               ($user->canManageProjects() && $user->tenant_id === $invitation->tenant_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Project managers and tenant admins can create invitations
        return $user->canManageProjects();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ProjectInvitation $invitation): bool
    {
        // Only the person who sent the invitation or admins can update it
        return $user->id === $invitation->invited_by || 
               ($user->canManageProjects() && $user->tenant_id === $invitation->tenant_id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ProjectInvitation $invitation): bool
    {
        // Only the person who sent the invitation or admins can delete it
        return $user->id === $invitation->invited_by || 
               ($user->canManageProjects() && $user->tenant_id === $invitation->tenant_id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ProjectInvitation $invitation): bool
    {
        return $user->canManageProjects() && $user->tenant_id === $invitation->tenant_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ProjectInvitation $invitation): bool
    {
        return $user->role === Role::TENANT_ADMIN && $user->tenant_id === $invitation->tenant_id;
    }

    /**
     * Determine whether the user can accept the invitation.
     */
    public function accept(User $user, ProjectInvitation $invitation): bool
    {
        // Only the invited user can accept the invitation
        return $user->email === $invitation->email;
    }

    /**
     * Determine whether the user can decline the invitation.
     */
    public function decline(User $user, ProjectInvitation $invitation): bool
    {
        // Only the invited user can decline the invitation
        return $user->email === $invitation->email;
    }
}