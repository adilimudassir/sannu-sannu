<?php

namespace App\Policies;

use App\Enums\Role;
use App\Enums\ProjectStatus;
use App\Enums\ProjectVisibility;
use App\Models\User;
use App\Models\Project;
use Illuminate\Auth\Access\Response;

class ProjectPolicy
{
    /**
     * Determine whether the user can view any projects.
     */
    public function viewAny(User $user): bool
    {
        // System admins can view projects from any tenant
        if ($user->isSystemAdmin()) {
            return true;
        }

        // Check if user has access to the current tenant
        $tenant = app('tenant');
        if (!$tenant) {
            return false;
        }

        // Users with tenant roles can view projects in their tenant
        return $user->hasRoleInTenant(Role::TENANT_ADMIN, $tenant->id) ||
               $user->hasRoleInTenant(Role::PROJECT_MANAGER, $tenant->id) ||
               $user->hasRoleInTenant(Role::CONTRIBUTOR, $tenant->id);
    }

    /**
     * Determine whether the user can view the project.
     */
    public function view(User $user, Project $project): bool
    {
        // System admins can view any project
        if ($user->isSystemAdmin()) {
            return true;
        }

        // Project creators and managers can always view their projects
        if ($this->canManageProject($user, $project)) {
            return true;
        }

        // Public projects can be viewed by anyone
        if ($project->visibility === ProjectVisibility::PUBLIC) {
            return true;
        }

        // Private projects can only be viewed by tenant members
        if ($project->visibility === ProjectVisibility::PRIVATE) {
            return $user->hasRoleInTenant(Role::TENANT_ADMIN, $project->tenant_id) ||
                   $user->hasRoleInTenant(Role::PROJECT_MANAGER, $project->tenant_id);
        }

        // Invite-only projects require specific invitation or tenant admin access
        if ($project->visibility === ProjectVisibility::INVITE_ONLY) {
            // Check if user has been invited to this project
            $hasInvitation = $project->invitations()
                ->where('email', $user->email)
                ->where('status', 'accepted')
                ->exists();

            return $hasInvitation || 
                   $user->hasRoleInTenant(Role::TENANT_ADMIN, $project->tenant_id);
        }

        return false;
    }

    /**
     * Determine whether the user can create projects.
     */
    public function create(User $user): bool
    {
        // System admins and tenant admins can create projects
        return $user->isSystemAdmin() || $user->isTenantAdmin();
    }

    /**
     * Determine whether the user can update the project.
     */
    public function update(User $user, Project $project): bool
    {
        // System admins can update any project
        if ($user->isSystemAdmin()) {
            return true;
        }

        // Project creators and managers can update their projects
        return $this->canManageProject($user, $project);
    }

    /**
     * Determine whether the user can delete the project.
     */
    public function delete(User $user, Project $project): bool
    {
        // System admins can delete any project
        if ($user->isSystemAdmin()) {
            return true;
        }

        // Project creators and managers can delete their projects
        // but only if there are no active contributions
        if ($this->canManageProject($user, $project)) {
            // Check if project has active contributions
            $hasActiveContributions = $project->contributions()
                ->whereIn('status', ['active', 'pending'])
                ->exists();

            return !$hasActiveContributions;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the project.
     */
    public function restore(User $user, Project $project): bool
    {
        // System admins can restore any project
        if ($user->isSystemAdmin()) {
            return true;
        }

        // Project creators and managers can restore their projects
        return $this->canManageProject($user, $project);
    }

    /**
     * Determine whether the user can permanently delete the project.
     */
    public function forceDelete(User $user, Project $project): bool
    {
        // Only system admins can force delete projects
        return $user->isSystemAdmin();
    }

    /**
     * Determine whether the user can activate the project.
     */
    public function activate(User $user, Project $project): bool
    {
        // System admins can activate any project
        if ($user->isSystemAdmin()) {
            return true;
        }

        // Project creators and managers can activate their projects
        if ($this->canManageProject($user, $project)) {
            // Can only activate draft projects
            return $project->status === ProjectStatus::DRAFT;
        }

        return false;
    }

    /**
     * Determine whether the user can pause the project.
     */
    public function pause(User $user, Project $project): bool
    {
        // System admins can pause any project
        if ($user->isSystemAdmin()) {
            return true;
        }

        // Project creators and managers can pause their projects
        if ($this->canManageProject($user, $project)) {
            // Can only pause active projects
            return $project->status === ProjectStatus::ACTIVE;
        }

        return false;
    }

    /**
     * Determine whether the user can resume the project.
     */
    public function resume(User $user, Project $project): bool
    {
        // System admins can resume any project
        if ($user->isSystemAdmin()) {
            return true;
        }

        // Project creators and managers can resume their projects
        if ($this->canManageProject($user, $project)) {
            // Can only resume paused projects
            return $project->status === ProjectStatus::PAUSED;
        }

        return false;
    }

    /**
     * Determine whether the user can complete the project.
     */
    public function complete(User $user, Project $project): bool
    {
        // System admins can complete any project
        if ($user->isSystemAdmin()) {
            return true;
        }

        // Project creators and managers can complete their projects
        if ($this->canManageProject($user, $project)) {
            // Can only complete active or paused projects
            return in_array($project->status, [ProjectStatus::ACTIVE, ProjectStatus::PAUSED]);
        }

        return false;
    }

    /**
     * Determine whether the user can cancel the project.
     */
    public function cancel(User $user, Project $project): bool
    {
        // System admins can cancel any project
        if ($user->isSystemAdmin()) {
            return true;
        }

        // Project creators and managers can cancel their projects
        if ($this->canManageProject($user, $project)) {
            // Can cancel projects that are not already completed or cancelled
            return !in_array($project->status, [ProjectStatus::COMPLETED, ProjectStatus::CANCELLED]);
        }

        return false;
    }

    /**
     * Determine whether the user can manage project products.
     */
    public function manageProducts(User $user, Project $project): bool
    {
        // System admins can manage products for any project
        if ($user->isSystemAdmin()) {
            return true;
        }

        // Project creators and managers can manage products
        if ($this->canManageProject($user, $project)) {
            // Can only manage products for draft projects or projects without contributions
            if ($project->status === ProjectStatus::DRAFT) {
                return true;
            }

            // Check if project has contributions
            $hasContributions = $project->contributions()->exists();
            return !$hasContributions;
        }

        return false;
    }

    /**
     * Determine whether the user can view project statistics.
     */
    public function viewStatistics(User $user, Project $project): bool
    {
        // System admins can view statistics for any project
        if ($user->isSystemAdmin()) {
            return true;
        }

        // Project creators and managers can view statistics
        if ($this->canManageProject($user, $project)) {
            return true;
        }

        // Contributors can view basic statistics for projects they've contributed to
        $hasContributed = $project->contributions()
            ->where('user_id', $user->id)
            ->exists();

        return $hasContributed;
    }

    /**
     * Determine whether the user can invite others to the project.
     */
    public function inviteUsers(User $user, Project $project): bool
    {
        // System admins can invite users to any project
        if ($user->isSystemAdmin()) {
            return true;
        }

        // Project creators and managers can invite users
        if ($this->canManageProject($user, $project)) {
            // Can only invite to invite-only projects or private projects
            return in_array($project->visibility, [ProjectVisibility::INVITE_ONLY, ProjectVisibility::PRIVATE]);
        }

        return false;
    }

    /**
     * Determine whether the user can contribute to the project.
     */
    public function contribute(User $user, Project $project): bool
    {
        // Cannot contribute to own project
        if ($project->created_by === $user->id) {
            return false;
        }

        // Project must be active to accept contributions
        if ($project->status !== ProjectStatus::ACTIVE) {
            return false;
        }

        // Check visibility restrictions
        if (!$this->view($user, $project)) {
            return false;
        }

        // Check if project has reached maximum contributors
        if ($project->max_contributors) {
            $currentContributors = $project->contributions()
                ->distinct('user_id')
                ->count();

            if ($currentContributors >= $project->max_contributors) {
                return false;
            }
        }

        // Check if registration deadline has passed
        if ($project->registration_deadline && now()->isAfter($project->registration_deadline)) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can view cross-tenant projects (system admin only).
     */
    public function viewCrossTenant(User $user): bool
    {
        return $user->isSystemAdmin();
    }

    /**
     * Determine whether the user can create projects for other tenants (system admin only).
     */
    public function createForTenant(User $user, int $tenantId): bool
    {
        // System admins can create projects for any tenant
        if ($user->isSystemAdmin()) {
            return true;
        }

        // Tenant admins can only create projects for their own tenant
        return $user->hasRoleInTenant(Role::TENANT_ADMIN, $tenantId);
    }

    /**
     * Determine whether the user can override project restrictions (system admin only).
     */
    public function overrideRestrictions(User $user): bool
    {
        return $user->isSystemAdmin();
    }

    /**
     * Determine whether the user can view audit logs for the project.
     */
    public function viewAuditLogs(User $user, Project $project): bool
    {
        // System admins can view audit logs for any project
        if ($user->isSystemAdmin()) {
            return true;
        }

        // Project creators and tenant admins can view audit logs
        return $this->canManageProject($user, $project);
    }

    /**
     * Helper method to determine if a user can manage a project.
     */
    private function canManageProject(User $user, Project $project): bool
    {
        // Project creator can always manage
        if ($project->created_by === $user->id) {
            return true;
        }

        // Check if user is in managed_by array
        if (is_array($project->managed_by) && in_array($user->id, $project->managed_by)) {
            return true;
        }

        // Tenant admins and project managers can manage projects in their tenant
        return $user->hasRoleInTenant(Role::TENANT_ADMIN, $project->tenant_id) ||
               $user->hasRoleInTenant(Role::PROJECT_MANAGER, $project->tenant_id);
    }
}