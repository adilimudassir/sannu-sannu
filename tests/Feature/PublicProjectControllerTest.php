<?php

namespace Tests\Feature;

use App\Enums\ProjectStatus;
use App\Enums\ProjectVisibility;
use App\Models\Product;
use App\Models\Project;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->tenant = Tenant::factory()->create();
    }

    public function test_index_displays_public_projects()
    {
        // Create public active project
        $publicProject = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
            'visibility' => ProjectVisibility::PUBLIC,
            'status' => ProjectStatus::ACTIVE,
            'name' => 'Public Test Project',
            'description' => 'This is a public project for testing',
        ]);

        // Create private project (should not appear)
        Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
            'visibility' => ProjectVisibility::PRIVATE,
            'status' => ProjectStatus::ACTIVE,
        ]);

        // Create draft project (should not appear)
        Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
            'visibility' => ProjectVisibility::PUBLIC,
            'status' => ProjectStatus::DRAFT,
        ]);

        $response = $this->get(route('public.projects.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('public/projects/index')
            ->has('projects.data', 1)
            ->where('projects.data.0.name', 'Public Test Project')
            ->has('meta')
            ->where('meta.title', 'Discover Projects')
        );
    }

    public function test_index_applies_search_filter()
    {
        Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
            'visibility' => ProjectVisibility::PUBLIC,
            'status' => ProjectStatus::ACTIVE,
            'name' => 'Searchable Project',
        ]);

        Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
            'visibility' => ProjectVisibility::PUBLIC,
            'status' => ProjectStatus::ACTIVE,
            'name' => 'Other Project',
        ]);

        $response = $this->get(route('public.projects.index', ['search' => 'Searchable']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->has('projects.data', 1)
            ->where('projects.data.0.name', 'Searchable Project')
        );
    }

    public function test_index_applies_amount_filters()
    {
        Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
            'visibility' => ProjectVisibility::PUBLIC,
            'status' => ProjectStatus::ACTIVE,
            'total_amount' => 1000,
        ]);

        Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
            'visibility' => ProjectVisibility::PUBLIC,
            'status' => ProjectStatus::ACTIVE,
            'total_amount' => 5000,
        ]);

        $response = $this->get(route('public.projects.index', [
            'min_amount' => 2000,
            'max_amount' => 6000,
        ]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->has('projects.data', 1)
            ->where('projects.data.0.total_amount', '5000.00')
        );
    }

    public function test_show_displays_public_project()
    {
        $project = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
            'visibility' => ProjectVisibility::PUBLIC,
            'status' => ProjectStatus::ACTIVE,
            'name' => 'Test Project',
            'description' => 'Test project description',
            'slug' => 'test-project',
        ]);

        // Add products to the project
        Product::factory()->create([
            'project_id' => $project->id,
            'tenant_id' => $this->tenant->id,
            'name' => 'Test Product',
            'price' => 100,
        ]);

        $response = $this->get(route('public.projects.show', $project->slug));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('public/projects/show')
            ->where('project.name', 'Test Project')
            ->where('project.description', 'Test project description')
            ->has('project.tenant')
            ->has('project.creator')
            ->has('project.products', 1)
            ->has('statistics')
            ->has('meta')
            ->where('meta.title', 'Test Project - '.$this->tenant->name)
        );
    }

    public function test_show_returns_404_for_private_project()
    {
        $project = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
            'visibility' => ProjectVisibility::PRIVATE,
            'status' => ProjectStatus::ACTIVE,
            'slug' => 'private-project',
        ]);

        $response = $this->get(route('public.projects.show', $project->slug));

        $response->assertStatus(404);
    }

    public function test_show_returns_404_for_draft_project()
    {
        $project = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
            'visibility' => ProjectVisibility::PUBLIC,
            'status' => ProjectStatus::DRAFT,
            'slug' => 'draft-project',
        ]);

        $response = $this->get(route('public.projects.show', $project->slug));

        $response->assertStatus(404);
    }

    public function test_show_includes_seo_meta_data()
    {
        $project = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
            'visibility' => ProjectVisibility::PUBLIC,
            'status' => ProjectStatus::ACTIVE,
            'name' => 'SEO Test Project',
            'description' => 'This is a test project for SEO meta data validation',
            'slug' => 'seo-test-project',
        ]);

        $response = $this->get(route('public.projects.show', $project->slug));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->has('meta')
            ->where('meta.title', 'SEO Test Project - '.$this->tenant->name)
            ->where('meta.description', 'This is a test project for SEO meta data validation')
            ->has('meta.keywords')
            ->has('meta.og:title')
            ->has('meta.og:description')
            ->has('meta.canonical')
        );
    }

    public function test_search_returns_filtered_results()
    {
        Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
            'visibility' => ProjectVisibility::PUBLIC,
            'status' => ProjectStatus::ACTIVE,
            'name' => 'Laravel Project',
            'description' => 'A project about Laravel development',
        ]);

        Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
            'visibility' => ProjectVisibility::PUBLIC,
            'status' => ProjectStatus::ACTIVE,
            'name' => 'React Project',
            'description' => 'A project about React development',
        ]);

        $response = $this->get(route('public.projects.search', ['search' => 'Laravel']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('public/projects/search')
            ->has('projects.data', 1)
            ->where('projects.data.0.name', 'Laravel Project')
            ->where('searchTerm', 'Laravel')
        );
    }

    public function test_search_handles_empty_results()
    {
        $response = $this->get(route('public.projects.search', ['search' => 'nonexistent']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('public/projects/search')
            ->has('projects.data', 0)
            ->where('searchTerm', 'nonexistent')
        );
    }

    public function test_search_includes_noindex_meta()
    {
        $response = $this->get(route('public.projects.search', ['search' => 'test']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->has('meta')
            ->where('meta.noindex', true)
        );
    }

    public function test_projects_include_statistics()
    {
        $project = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
            'visibility' => ProjectVisibility::PUBLIC,
            'status' => ProjectStatus::ACTIVE,
            'total_amount' => 1000,
        ]);

        $response = $this->get(route('public.projects.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->has('projects.data.0.statistics')
            ->has('projects.data.0.statistics.total_contributors')
            ->has('projects.data.0.statistics.total_raised')
            ->has('projects.data.0.statistics.completion_percentage')
            ->has('projects.data.0.statistics.days_remaining')
        );
    }

    public function test_pagination_works_correctly()
    {
        // Create 20 projects
        Project::factory()->count(20)->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
            'visibility' => ProjectVisibility::PUBLIC,
            'status' => ProjectStatus::ACTIVE,
        ]);

        $response = $this->get(route('public.projects.index', ['per_page' => 10]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->has('projects.data', 10)
            ->where('projects.current_page', 1)
            ->where('projects.last_page', 2)
            ->where('projects.total', 20)
        );
    }
}
