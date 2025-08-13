<?php

namespace Tests\Unit;

use App\Http\Requests\UpdateProjectRequest;
use App\Enums\ProjectStatus;
use App\Enums\ProjectVisibility;
use App\Models\Project;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateProjectRequestTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Tenant $tenant;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->tenant = Tenant::factory()->create();
        $this->project = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
            'status' => ProjectStatus::DRAFT,
        ]);
    }

    /**
     * Test valid project update data passes validation.
     */
    public function test_valid_project_update_data_passes_validation(): void
    {
        $data = [
            'name' => 'Updated Project Name',
            'description' => 'Updated project description',
            'visibility' => ProjectVisibility::PRIVATE->value,
            'max_contributors' => 200,
            'end_date' => now()->addDays(60)->format('Y-m-d'),
        ];

        $request = new UpdateProjectRequest();
        $request->setRouteResolver(function () {
            return new class {
                public function parameter($key) {
                    return $this->project ?? null;
                }
                private $project;
                public function __construct() {
                    $this->project = Project::factory()->make(['status' => ProjectStatus::DRAFT]);
                }
            };
        });

        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    /**
     * Test partial updates work with sometimes validation.
     */
    public function test_partial_updates_work(): void
    {
        $data = [
            'name' => 'Just updating the name',
        ];

        $request = new UpdateProjectRequest();
        $request->setRouteResolver(function () {
            return new class {
                public function parameter($key) {
                    return Project::factory()->make(['status' => ProjectStatus::DRAFT]);
                }
            };
        });

        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    /**
     * Test financial fields are prohibited when project has contributions.
     */
    public function test_financial_fields_prohibited_with_contributions(): void
    {
        // Create a project with contributions
        $projectWithContributions = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => ProjectStatus::ACTIVE,
        ]);
        
        // Create a contribution for this project
        \App\Models\Contribution::factory()->create([
            'project_id' => $projectWithContributions->id,
            'tenant_id' => $this->tenant->id,
        ]);

        $data = [
            'total_amount' => 2000.00,
            'payment_options' => ['full'],
            'installment_frequency' => 'monthly',
        ];

        $request = new UpdateProjectRequest();
        $request->setRouteResolver(function () use ($projectWithContributions) {
            return new class($projectWithContributions) {
                private $project;
                public function __construct($project) {
                    $this->project = $project;
                }
                public function parameter($key) {
                    return $this->project;
                }
            };
        });

        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('total_amount', $validator->errors()->toArray());
        $this->assertArrayHasKey('payment_options', $validator->errors()->toArray());
        $this->assertArrayHasKey('installment_frequency', $validator->errors()->toArray());
    }

    /**
     * Test start date cannot be changed for active projects.
     */
    public function test_start_date_prohibited_for_active_projects(): void
    {
        $activeProject = Project::factory()->make(['status' => ProjectStatus::ACTIVE]);

        $data = [
            'start_date' => now()->addDays(10)->format('Y-m-d'),
        ];

        $request = new UpdateProjectRequest();
        $request->setRouteResolver(function () use ($activeProject) {
            return new class($activeProject) {
                private $project;
                public function __construct($project) {
                    $this->project = $project;
                }
                public function parameter($key) {
                    return $this->project;
                }
            };
        });

        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('start_date', $validator->errors()->toArray());
    }

    /**
     * Test status transition validation.
     */
    public function test_status_transition_validation(): void
    {
        // Test valid transition: DRAFT -> ACTIVE
        $draftProject = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => ProjectStatus::DRAFT,
        ]);
        
        $data = ['status' => ProjectStatus::ACTIVE->value];

        $request = UpdateProjectRequest::create('/', 'PUT', $data);
        $request->setRouteResolver(function () use ($draftProject) {
            return new class($draftProject) {
                private $project;
                public function __construct($project) {
                    $this->project = $project;
                }
                public function parameter($key) {
                    return $this->project;
                }
            };
        });

        $validator = Validator::make($data, $request->rules());
        $request->withValidator($validator);

        // Should pass validation for valid transition
        $this->assertTrue($validator->passes());

        // Test invalid transition: COMPLETED -> ACTIVE
        $completedProject = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => ProjectStatus::COMPLETED,
        ]);
        
        $data = ['status' => ProjectStatus::ACTIVE->value];
        $request = UpdateProjectRequest::create('/', 'PUT', $data);
        $request->setRouteResolver(function () use ($completedProject) {
            return new class($completedProject) {
                private $project;
                public function __construct($project) {
                    $this->project = $project;
                }
                public function parameter($key) {
                    return $this->project;
                }
            };
        });

        $validator = Validator::make($data, $request->rules());
        $request->withValidator($validator);

        // This should fail in the withValidator method
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
    }

    /**
     * Test product price changes are prevented if product has contributions.
     */
    public function test_product_price_changes_prevented_with_contributions(): void
    {
        $project = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        // Create a product for this project
        $product = \App\Models\Product::factory()->create([
            'project_id' => $project->id,
            'tenant_id' => $this->tenant->id,
            'price' => 100.00,
        ]);

        // Create a contribution for this product
        \App\Models\Contribution::factory()->create([
            'project_id' => $project->id,
            'tenant_id' => $this->tenant->id,
        ]);

        $data = [
            'products' => [
                [
                    'id' => $product->id,
                    'name' => 'Product 1',
                    'price' => 200.00, // Changed price
                ],
            ],
        ];

        $request = UpdateProjectRequest::create('/', 'PUT', $data);
        $request->setRouteResolver(function () use ($project) {
            return new class($project) {
                private $project;
                public function __construct($project) {
                    $this->project = $project;
                }
                public function parameter($key) {
                    return $this->project;
                }
            };
        });

        $validator = Validator::make($data, $request->rules());

        // The custom validation rule should catch this
        $this->assertFalse($validator->passes());
    }

    /**
     * Test product deletion validation.
     */
    public function test_product_deletion_validation(): void
    {
        $data = [
            'products' => [
                [
                    'id' => 1,
                    'name' => 'Product 1',
                    'price' => 100.00,
                    'delete' => true,
                ],
            ],
        ];

        $request = new UpdateProjectRequest();
        $request->setRouteResolver(function () {
            return new class {
                public function parameter($key) {
                    return Project::factory()->make();
                }
            };
        });

        $validator = Validator::make($data, $request->rules());
        $request->withValidator($validator);

        // Should fail because deleting all products leaves project empty
        $this->assertFalse($validator->passes());
    }

    /**
     * Test date validation for updates.
     */
    public function test_date_validation_for_updates(): void
    {
        $data = [
            'start_date' => now()->addDays(10)->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'), // Before start date
        ];

        $request = new UpdateProjectRequest();
        $request->setRouteResolver(function () {
            return new class {
                public function parameter($key) {
                    return Project::factory()->make(['status' => ProjectStatus::DRAFT]);
                }
            };
        });

        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('end_date', $validator->errors()->toArray());
    }

    /**
     * Test registration deadline validation.
     */
    public function test_registration_deadline_validation(): void
    {
        $data = [
            'start_date' => now()->addDays(10)->format('Y-m-d'),
            'end_date' => now()->addDays(40)->format('Y-m-d'),
            'registration_deadline' => now()->addDays(45)->format('Y-m-d'), // After end date
        ];

        $request = new UpdateProjectRequest();
        $request->setRouteResolver(function () {
            return new class {
                public function parameter($key) {
                    return Project::factory()->make(['status' => ProjectStatus::DRAFT]);
                }
            };
        });

        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('registration_deadline', $validator->errors()->toArray());
    }

    /**
     * Test installment validation for updates.
     */
    public function test_installment_validation_for_updates(): void
    {
        $project = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => ProjectStatus::DRAFT,
        ]);

        $data = [
            'payment_options' => ['installments'],
            'installment_frequency' => null, // Missing required frequency
        ];

        $request = UpdateProjectRequest::create('/', 'PUT', $data);
        $request->setRouteResolver(function () use ($project) {
            return new class($project) {
                private $project;
                public function __construct($project) {
                    $this->project = $project;
                }
                public function parameter($key) {
                    return $this->project;
                }
            };
        });

        $validator = Validator::make($data, $request->rules());
        $request->withValidator($validator);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('installment_frequency', $validator->errors()->toArray());
    }

    /**
     * Test tenant change is prohibited with contributions.
     */
    public function test_tenant_change_prohibited_with_contributions(): void
    {
        $projectWithContributions = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);
        
        // Create a contribution for this project
        \App\Models\Contribution::factory()->create([
            'project_id' => $projectWithContributions->id,
            'tenant_id' => $this->tenant->id,
        ]);

        $data = [
            'tenant_id' => Tenant::factory()->create()->id,
        ];

        $request = new UpdateProjectRequest();
        $request->setRouteResolver(function () use ($projectWithContributions) {
            return new class($projectWithContributions) {
                private $project;
                public function __construct($project) {
                    $this->project = $project;
                }
                public function parameter($key) {
                    return $this->project;
                }
            };
        });

        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('tenant_id', $validator->errors()->toArray());
    }
}