<?php

namespace Tests\Unit;

use App\Models\Project;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use App\Enums\ProjectStatus;
use App\Enums\ProjectVisibility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_has_correct_fillable_attributes()
    {
        $project = new Project();
        
        $expectedFillable = [
            'tenant_id',
            'name',
            'slug',
            'description',
            'visibility',
            'requires_approval',
            'max_contributors',
            'total_amount',
            'minimum_contribution',
            'payment_options',
            'installment_frequency',
            'custom_installment_months',
            'start_date',
            'end_date',
            'registration_deadline',
            'created_by',
            'managed_by',
            'status',
            'settings',
        ];

        $this->assertEquals($expectedFillable, $project->getFillable());
    }

    public function test_project_casts_attributes_correctly()
    {
        $project = Project::factory()->create([
            'status' => ProjectStatus::ACTIVE,
            'visibility' => ProjectVisibility::PUBLIC,
            'requires_approval' => true,
            'total_amount' => 1000.50,
            'payment_options' => ['full', 'installments'],
            'managed_by' => [1, 2, 3],
            'settings' => ['key' => 'value'],
        ]);

        $this->assertInstanceOf(ProjectStatus::class, $project->status);
        $this->assertInstanceOf(ProjectVisibility::class, $project->visibility);
        $this->assertTrue($project->requires_approval);
        $this->assertEquals(1000.50, $project->total_amount);
        $this->assertIsArray($project->payment_options);
        $this->assertIsArray($project->managed_by);
        $this->assertIsArray($project->settings);
    }

    public function test_project_belongs_to_tenant()
    {
        $tenant = Tenant::factory()->create();
        $project = Project::factory()->create(['tenant_id' => $tenant->id]);

        $this->assertInstanceOf(Tenant::class, $project->tenant);
        $this->assertEquals($tenant->id, $project->tenant->id);
    }

    public function test_project_belongs_to_creator()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['created_by' => $user->id]);

        $this->assertInstanceOf(User::class, $project->creator);
        $this->assertEquals($user->id, $project->creator->id);
    }

    public function test_project_has_many_products()
    {
        $project = Project::factory()->create();
        $products = Product::factory()->count(3)->create(['project_id' => $project->id]);

        $this->assertCount(3, $project->products);
        $this->assertInstanceOf(Product::class, $project->products->first());
    }

    public function test_project_status_methods()
    {
        $draftProject = Project::factory()->draft()->create();
        $activeProject = Project::factory()->active()->create();
        $pausedProject = Project::factory()->paused()->create();
        $completedProject = Project::factory()->completed()->create();
        $cancelledProject = Project::factory()->cancelled()->create();

        $this->assertTrue($draftProject->isDraft());
        $this->assertTrue($activeProject->isActive());
        $this->assertTrue($pausedProject->isPaused());
        $this->assertTrue($completedProject->isCompleted());
        $this->assertTrue($cancelledProject->isCancelled());

        $this->assertTrue($activeProject->acceptsContributions());
        $this->assertFalse($draftProject->acceptsContributions());
    }

    public function test_project_visibility_methods()
    {
        $publicProject = Project::factory()->public()->create();
        $privateProject = Project::factory()->private()->create();
        $inviteOnlyProject = Project::factory()->inviteOnly()->create();

        $this->assertTrue($publicProject->isPubliclyDiscoverable());
        $this->assertFalse($privateProject->isPubliclyDiscoverable());
        $this->assertFalse($inviteOnlyProject->isPubliclyDiscoverable());

        $this->assertFalse($publicProject->hasRestrictedAccess());
        $this->assertTrue($privateProject->hasRestrictedAccess());
        $this->assertTrue($inviteOnlyProject->hasRestrictedAccess());
    }

    public function test_project_calculate_total_amount()
    {
        $project = Project::factory()->create();
        
        Product::factory()->create(['project_id' => $project->id, 'price' => 100.00]);
        Product::factory()->create(['project_id' => $project->id, 'price' => 200.00]);
        Product::factory()->create(['project_id' => $project->id, 'price' => 150.00]);

        $this->assertEquals(450.00, $project->calculateTotalAmount());
    }

    public function test_project_scopes()
    {
        // Clear any existing projects
        Project::query()->delete();
        
        Project::factory()->active()->count(2)->create();
        Project::factory()->draft()->count(1)->create();
        Project::factory()->public()->active()->count(3)->create();

        $this->assertCount(5, Project::active()->get());
        $this->assertCount(3, Project::publiclyDiscoverable()->get());
    }

    public function test_project_search_scope()
    {
        // Clear any existing projects
        Project::query()->delete();
        
        Project::factory()->create(['name' => 'Laravel Project', 'description' => 'A project about Laravel']);
        Project::factory()->create(['name' => 'Vue Project', 'description' => 'A project about Vue.js']);
        Project::factory()->create(['name' => 'React Project', 'description' => 'A project about React']);

        $results = Project::search('Laravel')->get();
        $this->assertCount(1, $results);
        $this->assertEquals('Laravel Project', $results->first()->name);

        $results = Project::search('project')->get();
        $this->assertCount(3, $results);
    }
}