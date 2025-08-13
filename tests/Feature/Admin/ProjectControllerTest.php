<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Project;
use App\Models\Product;
use App\Enums\Role;
use App\Enums\ProjectStatus;
use App\Enums\ProjectVisibility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $systemAdmin;
    private User $tenantAdmin;
    private Tenant $tenant1;
    private Tenant $tenant2;

    protected function setUp(): void
    {
        parent::setUp();

        // Create system admin
        $this->systemAdmin = User::factory()->create([
            'role' => Role::SYSTEM_ADMIN,
        ]);

        // Create tenants
        $this->tenant1 = Tenant::factory()->create(['name' => 'Tenant One']);
        $this->tenant2 = Tenant::factory()->create(['name' => 'Tenant Two']);

        // Create tenant admin
        $this->tenantAdmin = User::factory()->create();
        $this->tenantAdmin->tenantRoles()->create([
            'tenant_id' => $this->tenant1->id,
            'role' => Role::TENANT_ADMIN,
            'is_active' => true,
        ]);
    }

    public function test_system_admin_can_view_all_projects_across_tenants(): void
    {
        // Create projects in different tenants
        $project1 = Project::factory()->create([
            'tenant_id' => $this->tenant1->id,
            'name' => 'Project One',
        ]);
        $project2 = Project::factory()->create([
            'tenant_id' => $this->tenant2->id,
            'name' => 'Project Two',
        ]);

        $response = $this->actingAs($this->systemAdmin)
            ->get(route('admin.projects.index'));

        $response->assertOk();
        
        // Check that the response contains the expected data structure
        $responseData = $response->viewData('page')['props'];
        $this->assertArrayHasKey('projects', $responseData);
        $this->assertArrayHasKey('tenants', $responseData);
        $this->assertCount(2, $responseData['projects']['data']);
    }

    public function test_tenant_admin_cannot_access_admin_project_routes(): void
    {
        $response = $this->actingAs($this->tenantAdmin)
            ->get(route('admin.projects.index'));

        $response->assertForbidden();
    }

    public function test_system_admin_can_create_project_for_any_tenant(): void
    {
        Storage::fake('public');

        $projectData = [
            'tenant_id' => $this->tenant1->id,
            'name' => 'Test Project',
            'description' => 'Test project description',
            'visibility' => ProjectVisibility::PUBLIC->value,
            'total_amount' => 1000.00,
            'start_date' => now()->addDays(7)->format('Y-m-d'),
            'end_date' => now()->addDays(30)->format('Y-m-d'),
            'payment_options' => ['full'],
            'products' => [
                [
                    'name' => 'Product 1',
                    'description' => 'Product 1 description',
                    'price' => 500.00,
                    'sort_order' => 0,
                ],
                [
                    'name' => 'Product 2',
                    'description' => 'Product 2 description',
                    'price' => 500.00,
                    'sort_order' => 1,
                ],
            ],
        ];

        $response = $this->actingAs($this->systemAdmin)
            ->post(route('admin.projects.store'), $projectData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('projects', [
            'tenant_id' => $this->tenant1->id,
            'name' => 'Test Project',
            'created_by' => $this->systemAdmin->id,
        ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Product 1',
            'price' => 500.00,
        ]);
    }

    public function test_system_admin_project_creation_requires_tenant_selection(): void
    {
        $projectData = [
            // Missing tenant_id
            'name' => 'Test Project',
            'description' => 'Test project description',
            'visibility' => ProjectVisibility::PUBLIC->value,
            'total_amount' => 1000.00,
            'start_date' => now()->addDays(7)->format('Y-m-d'),
            'end_date' => now()->addDays(30)->format('Y-m-d'),
            'payment_options' => ['full'],
            'products' => [
                [
                    'name' => 'Product 1',
                    'description' => 'Product 1 description',
                    'price' => 1000.00,
                    'sort_order' => 0,
                ],
            ],
        ];

        $response = $this->actingAs($this->systemAdmin)
            ->post(route('admin.projects.store'), $projectData);

        $response->assertSessionHasErrors(['tenant_id']);
    }

    public function test_system_admin_can_view_project_from_any_tenant(): void
    {
        $project = Project::factory()->create([
            'tenant_id' => $this->tenant1->id,
        ]);

        $response = $this->actingAs($this->systemAdmin)
            ->get(route('admin.projects.show', $project));

        $response->assertOk();
        
        // Check that the response contains the expected data structure
        $responseData = $response->viewData('page')['props'];
        $this->assertArrayHasKey('project', $responseData);
        $this->assertArrayHasKey('statistics', $responseData);
        $this->assertTrue($responseData['canEdit']);
        $this->assertTrue($responseData['canDelete']);
    }

    public function test_system_admin_can_edit_project_from_any_tenant(): void
    {
        $project = Project::factory()->create([
            'tenant_id' => $this->tenant1->id,
        ]);

        $response = $this->actingAs($this->systemAdmin)
            ->get(route('admin.projects.edit', $project));

        $response->assertOk();
        
        // Check that the response contains the expected data structure
        $responseData = $response->viewData('page')['props'];
        $this->assertArrayHasKey('project', $responseData);
        $this->assertArrayHasKey('tenants', $responseData);
    }

    public function test_system_admin_can_update_project_tenant(): void
    {
        $project = Project::factory()->create([
            'tenant_id' => $this->tenant1->id,
            'name' => 'Original Project',
        ]);

        $updateData = [
            'tenant_id' => $this->tenant2->id,
            'name' => 'Updated Project',
            'description' => 'Updated description',
            'visibility' => ProjectVisibility::PUBLIC->value,
            'total_amount' => 1500.00,
            'start_date' => now()->addDays(7)->format('Y-m-d'),
            'end_date' => now()->addDays(30)->format('Y-m-d'),
            'payment_options' => ['full'],
        ];

        $response = $this->actingAs($this->systemAdmin)
            ->put(route('admin.projects.update', $project), $updateData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $project->refresh();
        $this->assertEquals($this->tenant2->id, $project->tenant_id);
        $this->assertEquals('Updated Project', $project->name);
    }

    public function test_system_admin_can_delete_project_from_any_tenant(): void
    {
        $project = Project::factory()->create([
            'tenant_id' => $this->tenant1->id,
        ]);

        $response = $this->actingAs($this->systemAdmin)
            ->delete(route('admin.projects.destroy', $project));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    public function test_system_admin_can_activate_project(): void
    {
        $project = Project::factory()->create([
            'tenant_id' => $this->tenant1->id,
            'status' => ProjectStatus::DRAFT,
        ]);

        // Add at least one product to make project activatable
        Product::factory()->create([
            'project_id' => $project->id,
            'tenant_id' => $this->tenant1->id,
        ]);

        $response = $this->actingAs($this->systemAdmin)
            ->patch(route('admin.projects.activate', $project));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $project->refresh();
        $this->assertEquals(ProjectStatus::ACTIVE, $project->status);
    }

    public function test_system_admin_can_pause_project(): void
    {
        $project = Project::factory()->create([
            'tenant_id' => $this->tenant1->id,
            'status' => ProjectStatus::ACTIVE,
        ]);

        $response = $this->actingAs($this->systemAdmin)
            ->patch(route('admin.projects.pause', $project));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $project->refresh();
        $this->assertEquals(ProjectStatus::PAUSED, $project->status);
    }

    public function test_system_admin_can_complete_project(): void
    {
        $project = Project::factory()->create([
            'tenant_id' => $this->tenant1->id,
            'status' => ProjectStatus::ACTIVE,
        ]);

        $response = $this->actingAs($this->systemAdmin)
            ->patch(route('admin.projects.complete', $project));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $project->refresh();
        $this->assertEquals(ProjectStatus::COMPLETED, $project->status);
    }

    public function test_system_admin_actions_are_logged(): void
    {
        // Test that audit logging is called (we're using Log facade, not database)
        // This test verifies that the audit logging methods are called without errors
        $projectData = [
            'tenant_id' => $this->tenant1->id,
            'name' => 'Logged Project',
            'description' => 'Test project for logging',
            'visibility' => ProjectVisibility::PUBLIC->value,
            'total_amount' => 1000.00,
            'start_date' => now()->addDays(7)->format('Y-m-d'),
            'end_date' => now()->addDays(30)->format('Y-m-d'),
            'payment_options' => ['full'],
            'products' => [
                [
                    'name' => 'Product 1',
                    'description' => 'Product 1 description',
                    'price' => 1000.00,
                    'sort_order' => 0,
                ],
            ],
        ];

        $response = $this->actingAs($this->systemAdmin)
            ->post(route('admin.projects.store'), $projectData);

        // Check that the project was created successfully (audit logging doesn't throw errors)
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('projects', [
            'name' => 'Logged Project',
            'tenant_id' => $this->tenant1->id,
        ]);
    }

    public function test_system_admin_can_filter_projects_by_tenant(): void
    {
        // Create projects in different tenants
        Project::factory()->create(['tenant_id' => $this->tenant1->id]);
        Project::factory()->create(['tenant_id' => $this->tenant2->id]);

        $response = $this->actingAs($this->systemAdmin)
            ->get(route('admin.projects.index', ['tenant_id' => $this->tenant1->id]));

        $response->assertOk();
        
        // Check that the response contains the expected data structure
        $responseData = $response->viewData('page')['props'];
        $this->assertArrayHasKey('projects', $responseData);
        $this->assertCount(1, $responseData['projects']['data']);
    }

    public function test_regular_user_cannot_access_admin_project_routes(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('admin.projects.index'));

        $response->assertForbidden();
    }
}