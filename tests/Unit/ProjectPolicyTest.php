<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Project;
use App\Models\Contribution;
use App\Models\ProjectInvitation;
use App\Models\UserTenantRole;
use App\Enums\Role;
use App\Enums\ProjectStatus;
use App\Enums\ProjectVisibility;
use App\Policies\ProjectPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProjectPolicyTest extends TestCase
{
    use RefreshDatabase;

    private ProjectPolicy $policy;
    private User $systemAdmin;
    private User $tenantAdmin;
    private User $tenantMember;
    private User $regularUser;
    private Tenant $tenant;
    private Project $publicProject;
    private Project $privateProject;
    private Project $inviteOnlyProject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new ProjectPolicy();

        // Create test tenant
        $this->tenant = Tenant::factory()->create();

        // Create test users
        $this->systemAdmin = User::factory()->create(['role' => Role::SYSTEM_ADMIN]);
        
        $this->tenantAdmin = User::factory()->create(['role' => Role::CONTRIBUTOR]);
        UserTenantRole::create([
            'user_id' => $this->tenantAdmin->id,
            'tenant_id' => $this->tenant->id,
            'role' => Role::TENANT_ADMIN,
            'is_active' => true,
        ]);
        
        $this->tenantMember = User::factory()->create(['role' => Role::CONTRIBUTOR]);
        UserTenantRole::create([
            'user_id' => $this->tenantMember->id,
            'tenant_id' => $this->tenant->id,
            'role' => Role::PROJECT_MANAGER,
            'is_active' => true,
        ]);
        
        $this->regularUser = User::factory()->create(['role' => Role::CONTRIBUTOR]);

        // Create test projects
        $this->publicProject = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->tenantAdmin->id,
            'visibility' => ProjectVisibility::PUBLIC,
            'status' => ProjectStatus::ACTIVE,
        ]);

        $this->privateProject = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->tenantAdmin->id,
            'visibility' => ProjectVisibility::PRIVATE,
            'status' => ProjectStatus::ACTIVE,
        ]);

        $this->inviteOnlyProject = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->tenantAdmin->id,
            'visibility' => ProjectVisibility::INVITE_ONLY,
            'status' => ProjectStatus::ACTIVE,
        ]);
    }

    public function test_view_any_allows_all_authenticated_users()
    {
        $this->assertTrue($this->policy->viewAny($this->systemAdmin));
        $this->assertTrue($this->policy->viewAny($this->tenantAdmin));
        $this->assertTrue($this->policy->viewAny($this->tenantMember));
        $this->assertTrue($this->policy->viewAny($this->regularUser));
    }

    public function test_system_admin_can_view_any_project()
    {
        $this->assertTrue($this->policy->view($this->systemAdmin, $this->publicProject));
        $this->assertTrue($this->policy->view($this->systemAdmin, $this->privateProject));
        $this->assertTrue($this->policy->view($this->systemAdmin, $this->inviteOnlyProject));
    }

    public function test_project_creator_can_view_their_projects()
    {
        $this->assertTrue($this->policy->view($this->tenantAdmin, $this->publicProject));
        $this->assertTrue($this->policy->view($this->tenantAdmin, $this->privateProject));
        $this->assertTrue($this->policy->view($this->tenantAdmin, $this->inviteOnlyProject));
    }

    public function test_public_projects_can_be_viewed_by_anyone()
    {
        $this->assertTrue($this->policy->view($this->regularUser, $this->publicProject));
        $this->assertTrue($this->policy->view($this->tenantMember, $this->publicProject));
    }

    public function test_private_projects_can_only_be_viewed_by_tenant_members()
    {
        $this->assertTrue($this->policy->view($this->tenantMember, $this->privateProject));
        $this->assertFalse($this->policy->view($this->regularUser, $this->privateProject));
    }

    public function test_invite_only_projects_require_invitation_or_tenant_admin()
    {
        // Regular user without invitation cannot view
        $this->assertFalse($this->policy->view($this->regularUser, $this->inviteOnlyProject));
        
        // Tenant admin can view invite-only projects in their tenant
        $this->assertTrue($this->policy->view($this->tenantMember, $this->inviteOnlyProject));
        
        // User with accepted invitation can view
        ProjectInvitation::factory()->create([
            'project_id' => $this->inviteOnlyProject->id,
            'email' => $this->regularUser->email,
            'status' => 'accepted',
        ]);
        $this->assertTrue($this->policy->view($this->regularUser, $this->inviteOnlyProject));
    }

    public function test_create_permissions()
    {
        $this->assertTrue($this->policy->create($this->systemAdmin));
        $this->assertTrue($this->policy->create($this->tenantAdmin));
        $this->assertFalse($this->policy->create($this->tenantMember));
        $this->assertFalse($this->policy->create($this->regularUser));
    }

    public function test_update_permissions()
    {
        // System admin can update any project
        $this->assertTrue($this->policy->update($this->systemAdmin, $this->publicProject));
        
        // Project creator can update their project
        $this->assertTrue($this->policy->update($this->tenantAdmin, $this->publicProject));
        
        // Tenant admin can also update projects in their tenant
        $this->assertTrue($this->policy->update($this->tenantMember, $this->publicProject));
        
        // Regular users cannot update
        $this->assertFalse($this->policy->update($this->regularUser, $this->publicProject));
    }

    public function test_delete_permissions_without_contributions()
    {
        // System admin can delete any project
        $this->assertTrue($this->policy->delete($this->systemAdmin, $this->publicProject));
        
        // Project creator can delete their project without contributions
        $this->assertTrue($this->policy->delete($this->tenantAdmin, $this->publicProject));
        
        // Project managers can also delete projects in their tenant
        $this->assertTrue($this->policy->delete($this->tenantMember, $this->publicProject));
        
        // Regular users cannot delete
        $this->assertFalse($this->policy->delete($this->regularUser, $this->publicProject));
    }

    public function test_delete_permissions_with_active_contributions()
    {
        // Create an active contribution
        Contribution::factory()->create([
            'tenant_id' => $this->tenant->id,
            'project_id' => $this->publicProject->id,
            'user_id' => $this->regularUser->id,
            'status' => 'active',
        ]);

        // System admin can still delete
        $this->assertTrue($this->policy->delete($this->systemAdmin, $this->publicProject));
        
        // Project creator cannot delete with active contributions
        $this->assertFalse($this->policy->delete($this->tenantAdmin, $this->publicProject));
    }

    public function test_activate_permissions()
    {
        $draftProject = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->tenantAdmin->id,
            'status' => ProjectStatus::DRAFT,
        ]);

        // System admin can activate any project
        $this->assertTrue($this->policy->activate($this->systemAdmin, $draftProject));
        
        // Project creator can activate their draft project
        $this->assertTrue($this->policy->activate($this->tenantAdmin, $draftProject));
        
        // Cannot activate non-draft projects
        $this->assertFalse($this->policy->activate($this->tenantAdmin, $this->publicProject));
        
        // Project managers can also activate projects in their tenant
        $this->assertTrue($this->policy->activate($this->tenantMember, $draftProject));
    }

    public function test_pause_permissions()
    {
        // System admin can pause any active project
        $this->assertTrue($this->policy->pause($this->systemAdmin, $this->publicProject));
        
        // Project creator can pause their active project
        $this->assertTrue($this->policy->pause($this->tenantAdmin, $this->publicProject));
        
        // Cannot pause non-active projects
        $draftProject = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->tenantAdmin->id,
            'status' => ProjectStatus::DRAFT,
        ]);
        $this->assertFalse($this->policy->pause($this->tenantAdmin, $draftProject));
        
        // Project managers can also pause projects in their tenant
        $this->assertTrue($this->policy->pause($this->tenantMember, $this->publicProject));
    }

    public function test_resume_permissions()
    {
        $pausedProject = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->tenantAdmin->id,
            'status' => ProjectStatus::PAUSED,
        ]);

        // System admin can resume any paused project
        $this->assertTrue($this->policy->resume($this->systemAdmin, $pausedProject));
        
        // Project creator can resume their paused project
        $this->assertTrue($this->policy->resume($this->tenantAdmin, $pausedProject));
        
        // Cannot resume non-paused projects
        $this->assertFalse($this->policy->resume($this->tenantAdmin, $this->publicProject));
        
        // Project managers can also resume projects in their tenant
        $this->assertTrue($this->policy->resume($this->tenantMember, $pausedProject));
    }

    public function test_complete_permissions()
    {
        // System admin can complete any active/paused project
        $this->assertTrue($this->policy->complete($this->systemAdmin, $this->publicProject));
        
        // Project creator can complete their active project
        $this->assertTrue($this->policy->complete($this->tenantAdmin, $this->publicProject));
        
        // Cannot complete draft projects
        $draftProject = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->tenantAdmin->id,
            'status' => ProjectStatus::DRAFT,
        ]);
        $this->assertFalse($this->policy->complete($this->tenantAdmin, $draftProject));
        
        // Project managers can also complete projects in their tenant
        $this->assertTrue($this->policy->complete($this->tenantMember, $this->publicProject));
    }

    public function test_cancel_permissions()
    {
        // System admin can cancel any non-completed/cancelled project
        $this->assertTrue($this->policy->cancel($this->systemAdmin, $this->publicProject));
        
        // Project creator can cancel their project
        $this->assertTrue($this->policy->cancel($this->tenantAdmin, $this->publicProject));
        
        // Cannot cancel completed projects
        $completedProject = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->tenantAdmin->id,
            'status' => ProjectStatus::COMPLETED,
        ]);
        $this->assertFalse($this->policy->cancel($this->tenantAdmin, $completedProject));
        
        // Project managers can also cancel projects in their tenant
        $this->assertTrue($this->policy->cancel($this->tenantMember, $this->publicProject));
    }

    public function test_manage_products_permissions()
    {
        $draftProject = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->tenantAdmin->id,
            'status' => ProjectStatus::DRAFT,
        ]);

        // System admin can manage products for any project
        $this->assertTrue($this->policy->manageProducts($this->systemAdmin, $draftProject));
        
        // Project creator can manage products for draft projects
        $this->assertTrue($this->policy->manageProducts($this->tenantAdmin, $draftProject));
        
        // Cannot manage products for active projects with contributions
        Contribution::factory()->create([
            'tenant_id' => $this->tenant->id,
            'project_id' => $this->publicProject->id,
            'user_id' => $this->regularUser->id,
        ]);
        $this->assertFalse($this->policy->manageProducts($this->tenantAdmin, $this->publicProject));
        
        // Project managers can also manage products in their tenant
        $this->assertTrue($this->policy->manageProducts($this->tenantMember, $draftProject));
    }

    public function test_view_statistics_permissions()
    {
        // System admin can view statistics for any project
        $this->assertTrue($this->policy->viewStatistics($this->systemAdmin, $this->publicProject));
        
        // Project creator can view statistics
        $this->assertTrue($this->policy->viewStatistics($this->tenantAdmin, $this->publicProject));
        
        // Contributors can view basic statistics
        Contribution::factory()->create([
            'tenant_id' => $this->tenant->id,
            'project_id' => $this->publicProject->id,
            'user_id' => $this->regularUser->id,
        ]);
        $this->assertTrue($this->policy->viewStatistics($this->regularUser, $this->publicProject));
        
        // Project managers can view statistics for projects in their tenant
        $this->assertTrue($this->policy->viewStatistics($this->tenantMember, $this->publicProject));
    }

    public function test_invite_users_permissions()
    {
        // System admin can invite users to any project
        $this->assertTrue($this->policy->inviteUsers($this->systemAdmin, $this->inviteOnlyProject));
        
        // Project creator can invite users to invite-only projects
        $this->assertTrue($this->policy->inviteUsers($this->tenantAdmin, $this->inviteOnlyProject));
        $this->assertTrue($this->policy->inviteUsers($this->tenantAdmin, $this->privateProject));
        
        // Cannot invite users to public projects
        $this->assertFalse($this->policy->inviteUsers($this->tenantAdmin, $this->publicProject));
        
        // Project managers can also invite users to projects in their tenant
        $this->assertTrue($this->policy->inviteUsers($this->tenantMember, $this->inviteOnlyProject));
    }

    public function test_contribute_permissions()
    {
        // Cannot contribute to own project
        $this->assertFalse($this->policy->contribute($this->tenantAdmin, $this->publicProject));
        
        // Can contribute to active public projects
        $this->assertTrue($this->policy->contribute($this->regularUser, $this->publicProject));
        
        // Cannot contribute to non-active projects
        $draftProject = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->tenantAdmin->id,
            'status' => ProjectStatus::DRAFT,
        ]);
        $this->assertFalse($this->policy->contribute($this->regularUser, $draftProject));
        
        // Cannot contribute if max contributors reached
        $limitedProject = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->tenantAdmin->id,
            'status' => ProjectStatus::ACTIVE,
            'max_contributors' => 1,
        ]);
        
        // Create a contribution to reach the limit
        Contribution::factory()->create([
            'tenant_id' => $this->tenant->id,
            'project_id' => $limitedProject->id,
            'user_id' => $this->tenantMember->id,
        ]);
        
        $this->assertFalse($this->policy->contribute($this->regularUser, $limitedProject));
    }

    public function test_cross_tenant_permissions()
    {
        // Only system admin can view cross-tenant projects
        $this->assertTrue($this->policy->viewCrossTenant($this->systemAdmin));
        $this->assertFalse($this->policy->viewCrossTenant($this->tenantAdmin));
        $this->assertFalse($this->policy->viewCrossTenant($this->tenantMember));
        $this->assertFalse($this->policy->viewCrossTenant($this->regularUser));
    }

    public function test_create_for_tenant_permissions()
    {
        $otherTenant = Tenant::factory()->create();

        // System admin can create projects for any tenant
        $this->assertTrue($this->policy->createForTenant($this->systemAdmin, $otherTenant->id));
        
        // Tenant admin can only create for their own tenant
        $this->assertTrue($this->policy->createForTenant($this->tenantAdmin, $this->tenant->id));
        $this->assertFalse($this->policy->createForTenant($this->tenantAdmin, $otherTenant->id));
        
        // Other users cannot create for any tenant
        $this->assertFalse($this->policy->createForTenant($this->tenantMember, $this->tenant->id));
        $this->assertFalse($this->policy->createForTenant($this->regularUser, $this->tenant->id));
    }

    public function test_override_restrictions_permissions()
    {
        // Only system admin can override restrictions
        $this->assertTrue($this->policy->overrideRestrictions($this->systemAdmin));
        $this->assertFalse($this->policy->overrideRestrictions($this->tenantAdmin));
        $this->assertFalse($this->policy->overrideRestrictions($this->tenantMember));
        $this->assertFalse($this->policy->overrideRestrictions($this->regularUser));
    }

    public function test_view_audit_logs_permissions()
    {
        // System admin can view audit logs for any project
        $this->assertTrue($this->policy->viewAuditLogs($this->systemAdmin, $this->publicProject));
        
        // Project creator can view audit logs
        $this->assertTrue($this->policy->viewAuditLogs($this->tenantAdmin, $this->publicProject));
        
        // Project managers can view audit logs for projects in their tenant
        $this->assertTrue($this->policy->viewAuditLogs($this->tenantMember, $this->publicProject));
        
        // Regular users cannot view audit logs
        $this->assertFalse($this->policy->viewAuditLogs($this->regularUser, $this->publicProject));
    }

    public function test_force_delete_permissions()
    {
        // Only system admin can force delete
        $this->assertTrue($this->policy->forceDelete($this->systemAdmin, $this->publicProject));
        $this->assertFalse($this->policy->forceDelete($this->tenantAdmin, $this->publicProject));
        $this->assertFalse($this->policy->forceDelete($this->tenantMember, $this->publicProject));
        $this->assertFalse($this->policy->forceDelete($this->regularUser, $this->publicProject));
    }

    public function test_managed_by_array_permissions()
    {
        $managedUser = User::factory()->create(['role' => Role::CONTRIBUTOR]);
        UserTenantRole::create([
            'user_id' => $managedUser->id,
            'tenant_id' => $this->tenant->id,
            'role' => Role::PROJECT_MANAGER,
            'is_active' => true,
        ]);

        $projectWithManagers = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->tenantAdmin->id,
            'managed_by' => [$managedUser->id],
        ]);

        // User in managed_by array can manage the project
        $this->assertTrue($this->policy->update($managedUser, $projectWithManagers));
        $this->assertTrue($this->policy->delete($managedUser, $projectWithManagers));
        $this->assertTrue($this->policy->viewStatistics($managedUser, $projectWithManagers));
    }
}