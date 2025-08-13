<?php

namespace Tests\Feature;

use App\Enums\ProjectStatus;
use App\Enums\ProjectVisibility;
use App\Enums\Role;
use App\Models\Project;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserTenantRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $tenantAdmin;
    private User $systemAdmin;
    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        // Create tenant
        $this->tenant = Tenant::factory()->create();

        // Create tenant admin user
        $this->tenantAdmin = User::factory()->create();
        UserTenantRole::create([
            'user_id' => $this->tenantAdmin->id,
            'tenant_id' => $this->tenant->id,
            'role' => Role::TENANT_ADMIN,
        ]);

        // Create system admin user
        $this->systemAdmin = User::factory()->create([
            'role' => Role::SYSTEM_ADMIN,
        ]);

        Storage::fake('public');
    }

    public function test_tenant_admin_can_view_project_index()
    {
        // Create some projects for the tenant
        Project::factory()->count(3)->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($this->tenantAdmin)
            ->get("/{$this->tenant->slug}/projects");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('projects/index')
            ->has('projects')
            ->has('filters')
            ->has('tenant')
        );
    }

    public function test_tenant_admin_can_create_project_with_products()
    {
        $projectData = [
            'name' => 'Test Project',
            'description' => 'A test project description',
            'visibility' => ProjectVisibility::PUBLIC->value,
            'total_amount' => 1000.00,
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(30)->format('Y-m-d'),
            'payment_options' => ['full'],
            'products' => [
                [
                    'name' => 'Product 1',
                    'description' => 'First product',
                    'price' => 500.00,
                ],
                [
                    'name' => 'Product 2',
                    'description' => 'Second product',
                    'price' => 500.00,
                ],
            ],
        ];

        $response = $this->actingAs($this->tenantAdmin)
            ->post("/{$this->tenant->slug}/projects", $projectData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('projects', [
            'name' => 'Test Project',
            'tenant_id' => $this->tenant->id,
            'status' => ProjectStatus::DRAFT->value,
        ]);

        $project = Project::where('name', 'Test Project')->first();
        $this->assertCount(2, $project->products);
        $this->assertEquals(1000.00, $project->total_amount);
    }

    public function test_tenant_admin_can_view_project_with_statistics()
    {
        $project = Project::factory()->create(['tenant_id' => $this->tenant->id]);
        Product::factory()->count(2)->create(['project_id' => $project->id]);

        $response = $this->actingAs($this->tenantAdmin)
            ->get("/{$this->tenant->slug}/projects/{$project->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('projects/show')
            ->has('project')
            ->has('statistics')
            ->has('canEdit')
            ->has('canDelete')
        );
    }

    public function test_tenant_admin_can_update_project()
    {
        $project = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Original Name',
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'description' => 'Updated description',
        ];

        $response = $this->actingAs($this->tenantAdmin)
            ->put("/{$this->tenant->slug}/projects/{$project->id}", $updateData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_tenant_admin_can_activate_project()
    {
        $project = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => ProjectStatus::DRAFT,
            'description' => 'Complete description',
        ]);
        Product::factory()->create(['project_id' => $project->id]);

        $response = $this->actingAs($this->tenantAdmin)
            ->patch("/{$this->tenant->slug}/projects/{$project->id}/activate");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'status' => ProjectStatus::ACTIVE->value,
        ]);
    }

    public function test_tenant_admin_can_pause_active_project()
    {
        $project = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => ProjectStatus::ACTIVE,
        ]);

        $response = $this->actingAs($this->tenantAdmin)
            ->patch("/{$this->tenant->slug}/projects/{$project->id}/pause");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'status' => ProjectStatus::PAUSED->value,
        ]);
    }

    public function test_tenant_admin_can_delete_project_without_contributions()
    {
        $project = Project::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($this->tenantAdmin)
            ->delete("/{$this->tenant->slug}/projects/{$project->id}");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    public function test_unauthorized_user_cannot_access_projects()
    {
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)
            ->get("/{$this->tenant->slug}/projects");

        $response->assertStatus(403);
    }

    public function test_project_filtering_works()
    {
        // Create projects with different statuses
        Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => ProjectStatus::DRAFT,
        ]);
        Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => ProjectStatus::ACTIVE,
        ]);

        $response = $this->actingAs($this->tenantAdmin)
            ->get("/{$this->tenant->slug}/projects?status[]=" . ProjectStatus::ACTIVE->value);

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('projects/index')
            ->has('projects.data', 1)
        );
    }

    public function test_project_search_works()
    {
        Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Searchable Project',
        ]);
        Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Other Project',
        ]);

        $response = $this->actingAs($this->tenantAdmin)
            ->get("/{$this->tenant->slug}/projects?search=Searchable");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('projects/index')
            ->has('projects.data', 1)
        );
    }

    public function test_validation_prevents_invalid_project_creation()
    {
        $invalidData = [
            'name' => '', // Required field
            'description' => '',
            'total_amount' => -100, // Must be positive
            'products' => [], // Required
        ];

        $response = $this->actingAs($this->tenantAdmin)
            ->post("/{$this->tenant->slug}/projects", $invalidData);

        $response->assertSessionHasErrors(['name', 'description', 'total_amount', 'products']);
    }
}