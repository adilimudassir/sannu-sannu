<?php

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\Tenant;
use App\Models\User;
use App\Services\ProjectService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->create();
    $this->projectService = app(ProjectService::class);
});

describe('Project Status Transitions', function () {
    it('can activate a draft project', function () {
        $project = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => ProjectStatus::DRAFT,
            'created_by' => $this->user->id,
            'payment_options' => ['full'],
            'installment_frequency' => 'monthly',
        ]);

        // Add a product to make it valid for activation
        $project->products()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 100.00,
            'sort_order' => 1,
        ]);

        // Update project total to match product price
        $project->update(['total_amount' => 100.00]);

        $updatedProject = $this->projectService->activateProject($project, $this->user);

        expect($updatedProject->status)->toBe(ProjectStatus::ACTIVE);
    });

    it('can pause an active project', function () {
        $project = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => ProjectStatus::ACTIVE,
            'created_by' => $this->user->id,
        ]);

        $updatedProject = $this->projectService->pauseProject($project, $this->user);

        expect($updatedProject->status)->toBe(ProjectStatus::PAUSED);
    });

    it('can resume a paused project', function () {
        $project = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => ProjectStatus::PAUSED,
            'created_by' => $this->user->id,
        ]);

        // Add a product to make it valid for resumption
        $project->products()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 100.00,
            'sort_order' => 1,
        ]);

        // Update project total to match product price
        $project->update(['total_amount' => 100.00]);

        $updatedProject = $this->projectService->resumeProject($project, $this->user);

        expect($updatedProject->status)->toBe(ProjectStatus::ACTIVE);
    });

    it('can complete an active project', function () {
        $project = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => ProjectStatus::ACTIVE,
            'created_by' => $this->user->id,
        ]);

        $updatedProject = $this->projectService->completeProject($project, $this->user);

        expect($updatedProject->status)->toBe(ProjectStatus::COMPLETED);
    });

    it('can complete a paused project', function () {
        $project = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => ProjectStatus::PAUSED,
            'created_by' => $this->user->id,
        ]);

        $updatedProject = $this->projectService->completeProject($project, $this->user);

        expect($updatedProject->status)->toBe(ProjectStatus::COMPLETED);
    });

    it('can cancel a draft project', function () {
        $project = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => ProjectStatus::DRAFT,
            'created_by' => $this->user->id,
        ]);

        $reason = 'Project no longer needed';
        $updatedProject = $this->projectService->cancelProject($project, $this->user, $reason);

        expect($updatedProject->status)->toBe(ProjectStatus::CANCELLED);
        expect($updatedProject->settings['cancellation_reason'])->toBe($reason);
        expect($updatedProject->settings['cancelled_by'])->toBe($this->user->id);
    });

    it('can cancel an active project', function () {
        $project = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => ProjectStatus::ACTIVE,
            'created_by' => $this->user->id,
        ]);

        $updatedProject = $this->projectService->cancelProject($project, $this->user);

        expect($updatedProject->status)->toBe(ProjectStatus::CANCELLED);
    });

    it('can cancel a paused project', function () {
        $project = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => ProjectStatus::PAUSED,
            'created_by' => $this->user->id,
        ]);

        $updatedProject = $this->projectService->cancelProject($project, $this->user);

        expect($updatedProject->status)->toBe(ProjectStatus::CANCELLED);
    });
});

describe('Invalid Status Transitions', function () {
    it('cannot activate a completed project', function () {
        $project = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => ProjectStatus::COMPLETED,
            'created_by' => $this->user->id,
        ]);

        expect(fn () => $this->projectService->activateProject($project, $this->user))
            ->toThrow(InvalidArgumentException::class);
    });

    it('cannot pause a draft project', function () {
        $project = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => ProjectStatus::DRAFT,
            'created_by' => $this->user->id,
        ]);

        expect(fn () => $this->projectService->pauseProject($project, $this->user))
            ->toThrow(InvalidArgumentException::class);
    });

    it('cannot resume an active project', function () {
        $project = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => ProjectStatus::ACTIVE,
            'created_by' => $this->user->id,
        ]);

        expect(fn () => $this->projectService->resumeProject($project, $this->user))
            ->toThrow(InvalidArgumentException::class);
    });

    it('cannot complete a draft project', function () {
        $project = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => ProjectStatus::DRAFT,
            'created_by' => $this->user->id,
        ]);

        expect(fn () => $this->projectService->completeProject($project, $this->user))
            ->toThrow(InvalidArgumentException::class);
    });

    it('cannot cancel a completed project', function () {
        $project = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => ProjectStatus::COMPLETED,
            'created_by' => $this->user->id,
        ]);

        expect(fn () => $this->projectService->cancelProject($project, $this->user))
            ->toThrow(InvalidArgumentException::class);
    });

    it('cannot cancel an already cancelled project', function () {
        $project = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => ProjectStatus::CANCELLED,
            'created_by' => $this->user->id,
        ]);

        expect(fn () => $this->projectService->cancelProject($project, $this->user))
            ->toThrow(InvalidArgumentException::class);
    });
});

describe('Project Activation Validation', function () {
    it('cannot activate project without products', function () {
        $project = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => ProjectStatus::DRAFT,
            'created_by' => $this->user->id,
        ]);

        expect(fn () => $this->projectService->activateProject($project, $this->user))
            ->toThrow(InvalidArgumentException::class, 'At least one product is required for activation');
    });

    it('cannot activate project with end date in the past', function () {
        $project = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => ProjectStatus::DRAFT,
            'created_by' => $this->user->id,
            'start_date' => now()->subWeek(),
            'end_date' => now()->subDay(),
        ]);

        $project->products()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 100.00,
            'sort_order' => 1,
        ]);

        // Update project total to match product price
        $project->update(['total_amount' => 100.00]);

        expect(fn () => $this->projectService->activateProject($project, $this->user))
            ->toThrow(InvalidArgumentException::class, 'Cannot activate project that has already ended');
    });

    it('cannot activate project without description', function () {
        $project = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => ProjectStatus::DRAFT,
            'created_by' => $this->user->id,
            'description' => null,
        ]);

        $project->products()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 100.00,
            'sort_order' => 1,
        ]);

        // Update project total to match product price
        $project->update(['total_amount' => 100.00]);

        expect(fn () => $this->projectService->activateProject($project, $this->user))
            ->toThrow(InvalidArgumentException::class, 'Project description is required for activation');
    });
});

describe('Automated Status Updates', function () {
    it('automatically completes expired active projects', function () {
        // Create an active project that has passed its end date
        $expiredProject = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => ProjectStatus::ACTIVE,
            'end_date' => now()->subDay(),
            'created_by' => $this->user->id,
        ]);

        // Create a project that hasn't expired yet
        $activeProject = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => ProjectStatus::ACTIVE,
            'end_date' => now()->addWeek(),
            'created_by' => $this->user->id,
        ]);

        $updatedCount = $this->projectService->updateProjectStatusByDate();

        expect($updatedCount)->toBe(1);
        expect($expiredProject->fresh()->status)->toBe(ProjectStatus::COMPLETED);
        expect($activeProject->fresh()->status)->toBe(ProjectStatus::ACTIVE);
    });

    it('automatically activates ready draft projects when start date arrives', function () {
        // Create a draft project that should be activated
        $readyProject = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => ProjectStatus::DRAFT,
            'start_date' => now()->subDay(),
            'end_date' => now()->addWeek(),
            'created_by' => $this->user->id,
            'payment_options' => ['full'],
            'installment_frequency' => 'monthly',
        ]);

        $readyProject->products()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 100.00,
            'sort_order' => 1,
        ]);

        // Update project total to match product price
        $readyProject->update(['total_amount' => 100.00]);

        // Create a draft project that's not ready (no products)
        $notReadyProject = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => ProjectStatus::DRAFT,
            'start_date' => now()->subDay(),
            'end_date' => now()->addWeek(),
            'created_by' => $this->user->id,
        ]);

        $updatedCount = $this->projectService->updateProjectStatusByDate();

        expect($updatedCount)->toBe(1);
        expect($readyProject->fresh()->status)->toBe(ProjectStatus::ACTIVE);
        expect($notReadyProject->fresh()->status)->toBe(ProjectStatus::DRAFT);
    });
});

describe('Status Transition Validation', function () {
    it('validates transitions using enum methods', function () {
        expect(ProjectStatus::DRAFT->canTransitionTo(ProjectStatus::ACTIVE))->toBeTrue();
        expect(ProjectStatus::DRAFT->canTransitionTo(ProjectStatus::CANCELLED))->toBeTrue();
        expect(ProjectStatus::DRAFT->canTransitionTo(ProjectStatus::COMPLETED))->toBeFalse();

        expect(ProjectStatus::ACTIVE->canTransitionTo(ProjectStatus::PAUSED))->toBeTrue();
        expect(ProjectStatus::ACTIVE->canTransitionTo(ProjectStatus::COMPLETED))->toBeTrue();
        expect(ProjectStatus::ACTIVE->canTransitionTo(ProjectStatus::CANCELLED))->toBeTrue();
        expect(ProjectStatus::ACTIVE->canTransitionTo(ProjectStatus::DRAFT))->toBeFalse();

        expect(ProjectStatus::PAUSED->canTransitionTo(ProjectStatus::ACTIVE))->toBeTrue();
        expect(ProjectStatus::PAUSED->canTransitionTo(ProjectStatus::COMPLETED))->toBeTrue();
        expect(ProjectStatus::PAUSED->canTransitionTo(ProjectStatus::CANCELLED))->toBeTrue();

        expect(ProjectStatus::COMPLETED->canTransitionTo(ProjectStatus::ACTIVE))->toBeFalse();
        expect(ProjectStatus::CANCELLED->canTransitionTo(ProjectStatus::ACTIVE))->toBeFalse();
    });

    it('provides meaningful transition descriptions', function () {
        expect(ProjectStatus::DRAFT->transitionDescription(ProjectStatus::ACTIVE))
            ->toBe('Activating project to accept contributions');

        expect(ProjectStatus::ACTIVE->transitionDescription(ProjectStatus::PAUSED))
            ->toBe('Pausing active project');

        expect(ProjectStatus::PAUSED->transitionDescription(ProjectStatus::ACTIVE))
            ->toBe('Resuming paused project');
    });
});
