<?php

namespace Tests\Unit;

use App\Enums\ProjectStatus;
use App\Enums\ProjectVisibility;
use App\Models\Contribution;
use App\Models\Product;
use App\Models\Project;
use App\Models\Tenant;
use App\Models\User;
use App\Services\ProjectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class ProjectServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProjectService $projectService;

    private Tenant $tenant;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->projectService = app(ProjectService::class);
        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create();
    }

    public function test_create_project_with_valid_data()
    {
        $data = [
            'name' => 'Test Project',
            'description' => 'A test project description',
            'visibility' => 'public',
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'total_amount' => 1000.00,
        ];

        $project = $this->projectService->createProject($data, $this->tenant, $this->user);

        $this->assertInstanceOf(Project::class, $project);
        $this->assertEquals('Test Project', $project->name);
        $this->assertEquals('test-project', $project->slug);
        $this->assertEquals(ProjectStatus::DRAFT, $project->status);
        $this->assertEquals($this->tenant->id, $project->tenant_id);
        $this->assertEquals($this->user->id, $project->created_by);
    }

    public function test_create_project_generates_unique_slug()
    {
        // Create first project
        $data = [
            'name' => 'Test Project',
            'description' => 'A test project description',
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'total_amount' => 1000.00,
        ];

        $project1 = $this->projectService->createProject($data, $this->tenant, $this->user);
        $project2 = $this->projectService->createProject($data, $this->tenant, $this->user);

        $this->assertEquals('test-project', $project1->slug);
        $this->assertEquals('test-project-1', $project2->slug);
    }

    public function test_create_project_validates_dates()
    {
        $data = [
            'name' => 'Test Project',
            'start_date' => now()->addMonth()->toDateString(),
            'end_date' => now()->addDay()->toDateString(), // End before start
            'total_amount' => 1000.00,
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('End date must be after start date');

        $this->projectService->createProject($data, $this->tenant, $this->user);
    }

    public function test_update_project_with_valid_data()
    {
        $project = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
        ]);

        $updateData = [
            'name' => 'Updated Project Name',
            'description' => 'Updated description',
        ];

        $updatedProject = $this->projectService->updateProject($project, $updateData, $this->user);

        $this->assertEquals('Updated Project Name', $updatedProject->name);
        $this->assertEquals('Updated description', $updatedProject->description);
    }

    public function test_update_project_prevents_critical_field_changes_with_contributions()
    {
        $project = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
            'total_amount' => 1000.00,
        ]);

        // Create a contribution
        Contribution::factory()->create([
            'project_id' => $project->id,
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
        ]);

        $updateData = [
            'total_amount' => 2000.00, // Try to change protected field
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot modify total_amount for projects with existing contributions');

        $this->projectService->updateProject($project, $updateData, $this->user);
    }

    public function test_delete_project_without_contributions()
    {
        $project = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
        ]);

        // Add some products
        Product::factory()->count(2)->create([
            'project_id' => $project->id,
            'tenant_id' => $this->tenant->id,
        ]);

        $result = $this->projectService->deleteProject($project, $this->user);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
        $this->assertDatabaseMissing('products', ['project_id' => $project->id]);
    }

    public function test_delete_project_with_contributions_fails()
    {
        $project = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
        ]);

        // Create a contribution
        Contribution::factory()->create([
            'project_id' => $project->id,
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot delete project with existing contributions');

        $this->projectService->deleteProject($project, $this->user);
    }

    public function test_activate_project_with_valid_conditions()
    {
        $project = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
            'status' => ProjectStatus::DRAFT,
            'name' => 'Test Project',
            'description' => 'Test description',
            'start_date' => now()->addDay(),
            'end_date' => now()->addMonth(),
            'total_amount' => 1000.00,
            'payment_options' => ['full'],
            'installment_frequency' => 'monthly',
        ]);

        // Add at least one product with matching price
        Product::factory()->create([
            'project_id' => $project->id,
            'tenant_id' => $this->tenant->id,
            'price' => 1000.00,
        ]);

        $activatedProject = $this->projectService->activateProject($project, $this->user);

        $this->assertEquals(ProjectStatus::ACTIVE, $activatedProject->status);
    }

    public function test_activate_project_fails_without_products()
    {
        $project = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
            'status' => ProjectStatus::DRAFT,
            'name' => 'Test Project',
            'description' => 'Test description',
            'start_date' => now()->addDay(),
            'end_date' => now()->addMonth(),
            'total_amount' => 1000.00,
        ]);

        // No products added

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one product is required for activation');

        $this->projectService->activateProject($project, $this->user);
    }

    public function test_pause_active_project()
    {
        $project = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
            'status' => ProjectStatus::ACTIVE,
        ]);

        $pausedProject = $this->projectService->pauseProject($project, $this->user);

        $this->assertEquals(ProjectStatus::PAUSED, $pausedProject->status);
    }

    public function test_pause_non_active_project_fails()
    {
        $project = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
            'status' => ProjectStatus::DRAFT,
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid transition from Draft to Paused');

        $this->projectService->pauseProject($project, $this->user);
    }

    public function test_complete_project()
    {
        $project = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
            'status' => ProjectStatus::ACTIVE,
        ]);

        $completedProject = $this->projectService->completeProject($project, $this->user);

        $this->assertEquals(ProjectStatus::COMPLETED, $completedProject->status);
    }

    public function test_cancel_project_with_reason()
    {
        $project = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
            'status' => ProjectStatus::ACTIVE,
        ]);

        $reason = 'Project cancelled due to budget constraints';
        $cancelledProject = $this->projectService->cancelProject($project, $this->user, $reason);

        $this->assertEquals(ProjectStatus::CANCELLED, $cancelledProject->status);
        $this->assertEquals($reason, $cancelledProject->settings['cancellation_reason']);
        $this->assertArrayHasKey('cancelled_at', $cancelledProject->settings);
    }

    public function test_get_public_projects()
    {
        // Create public projects
        Project::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
            'visibility' => ProjectVisibility::PUBLIC,
            'status' => ProjectStatus::ACTIVE,
        ]);

        // Create private project (should not be included)
        Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'visibility' => ProjectVisibility::PRIVATE,
            'status' => ProjectStatus::ACTIVE,
        ]);

        $result = $this->projectService->getPublicProjects();

        $this->assertEquals(3, $result->total());
    }

    public function test_search_projects()
    {
        $project1 = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Laravel Project',
            'description' => 'A project about Laravel',
        ]);

        $project2 = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Vue Project',
            'description' => 'A project about Vue.js',
        ]);

        $result = $this->projectService->searchProjects('Laravel', [], $this->tenant);

        $this->assertEquals(1, $result->total());
        $this->assertEquals($project1->id, $result->items()[0]->id);
    }

    public function test_apply_status_filter()
    {
        Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => ProjectStatus::ACTIVE,
        ]);

        Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => ProjectStatus::DRAFT,
        ]);

        $filters = ['status' => [ProjectStatus::ACTIVE->value]];
        $result = $this->projectService->getTenantProjects($this->tenant, $filters);

        $this->assertEquals(1, $result->total());
        $this->assertEquals(ProjectStatus::ACTIVE, $result->items()[0]->status);
    }

    public function test_calculate_project_total()
    {
        $project = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        Product::factory()->create([
            'project_id' => $project->id,
            'tenant_id' => $this->tenant->id,
            'price' => 100.00,
        ]);
        Product::factory()->create([
            'project_id' => $project->id,
            'tenant_id' => $this->tenant->id,
            'price' => 200.00,
        ]);

        $total = $this->projectService->calculateProjectTotal($project);

        $this->assertEquals(300.00, $total);
    }

    public function test_get_project_statistics()
    {
        $project = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'total_amount' => 1000.00,
            'end_date' => now()->addDays(10),
        ]);

        // Create some contributions with different users
        for ($i = 0; $i < 3; $i++) {
            $user = User::factory()->create();
            Contribution::factory()->create([
                'project_id' => $project->id,
                'tenant_id' => $this->tenant->id,
                'user_id' => $user->id,
                'total_committed' => 100.00,
                'total_paid' => 100.00,
            ]);
        }

        $statistics = $this->projectService->getProjectStatistics($project);

        $this->assertEquals(3, $statistics['total_contributors']);
        $this->assertEquals(300.00, $statistics['total_raised']);
        $this->assertEquals(30.00, $statistics['completion_percentage']);
        $this->assertEquals(100.00, $statistics['average_contribution']);
        $this->assertGreaterThanOrEqual(9, $statistics['days_remaining']);
        $this->assertLessThanOrEqual(10, $statistics['days_remaining']);
    }

    public function test_update_project_status_by_date()
    {
        // Create an active project that has passed its end date
        $expiredProject = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => ProjectStatus::ACTIVE,
            'end_date' => now()->subDay(),
        ]);

        // Create an active project that hasn't expired
        $activeProject = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => ProjectStatus::ACTIVE,
            'end_date' => now()->addDay(),
        ]);

        $updatedCount = $this->projectService->updateProjectStatusByDate();

        $this->assertEquals(1, $updatedCount);
        $this->assertEquals(ProjectStatus::COMPLETED, $expiredProject->fresh()->status);
        $this->assertEquals(ProjectStatus::ACTIVE, $activeProject->fresh()->status);
    }
}
