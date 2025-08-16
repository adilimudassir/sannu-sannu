<?php

namespace App\Services;

use App\Enums\ProjectStatus;
use App\Enums\ProjectVisibility;
use App\Models\Project;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class ProjectService
{
    public function __construct(
        private AuditLogService $auditLogService
    ) {}

    /**
     * Create a new project
     */
    public function createProject(array $data, Tenant $tenant, User $user): Project
    {
        $this->validateProjectData($data);

        try {
            return DB::transaction(function () use ($data, $tenant, $user) {
                // Generate unique slug
                $slug = $this->generateUniqueSlug($data['name'], $tenant);

                $project = Project::create([
                    'tenant_id' => $tenant->id,
                    'name' => $data['name'],
                    'slug' => $slug,
                    'description' => $data['description'] ?? null,
                    'visibility' => ProjectVisibility::from($data['visibility'] ?? 'public'),
                    'requires_approval' => $data['requires_approval'] ?? false,
                    'max_contributors' => $data['max_contributors'] ?? null,
                    'total_amount' => $data['total_amount'] ?? 0,
                    'minimum_contribution' => $data['minimum_contribution'] ?? null,
                    'payment_options' => $data['payment_options'] ?? ['full'],
                    'installment_frequency' => $data['installment_frequency'] ?? 'monthly',
                    'custom_installment_months' => $data['custom_installment_months'] ?? null,
                    'start_date' => $data['start_date'],
                    'end_date' => $data['end_date'],
                    'registration_deadline' => $data['registration_deadline'] ?? null,
                    'created_by' => $user->id,
                    'managed_by' => $data['managed_by'] ?? null,
                    'status' => ProjectStatus::DRAFT,
                    'settings' => $data['settings'] ?? null,
                ]);

                $this->auditLogService::logAuthEvent(
                    'project_created',
                    $user,
                    null,
                    [
                        'project_id' => $project->id,
                        'project_name' => $project->name,
                        'tenant_id' => $tenant->id,
                        'tenant_name' => $tenant->name,
                    ]
                );

                return $project;
            });
        } catch (\Exception $e) {
            Log::error('Failed to create project', [
                'error' => $e->getMessage(),
                'data' => $data,
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
            ]);

            throw new RuntimeException('Failed to create project: '.$e->getMessage());
        }
    }

    /**
     * Update an existing project
     */
    public function updateProject(Project $project, array $data, User $user): Project
    {
        $this->validateProjectData($data, $project);

        try {
            return DB::transaction(function () use ($project, $data, $user) {
                $originalData = $project->toArray();

                // Handle slug update if name changed
                if (isset($data['name']) && $data['name'] !== $project->name) {
                    $data['slug'] = $this->generateUniqueSlug($data['name'], $project->tenant, $project->id);
                }

                // Prevent critical field changes if project has contributions
                if ($project->contributions()->exists()) {
                    $protectedFields = ['total_amount', 'minimum_contribution', 'payment_options'];
                    foreach ($protectedFields as $field) {
                        if (isset($data[$field]) && $data[$field] !== $project->{$field}) {
                            throw new InvalidArgumentException("Cannot modify {$field} for projects with existing contributions");
                        }
                    }
                }

                $filteredData = array_filter($data, fn ($value) => $value !== null);
                $project->update($filteredData);

                try {
                    $changes = array_diff_assoc($project->toArray(), $originalData);

                    $this->auditLogService::logAuthEvent(
                        'project_updated',
                        $user,
                        null,
                        [
                            'project_id' => $project->id,
                            'project_name' => $project->name,
                            'tenant_id' => $project->tenant_id,
                            'changes' => array_keys($changes),
                            'changed_fields_count' => count($changes),
                        ]
                    );
                } catch (\Exception $auditException) {
                    // Log audit failure but don't fail the operation
                    Log::warning('Failed to log project update audit', [
                        'project_id' => $project->id,
                        'audit_error' => $auditException->getMessage(),
                    ]);
                }

                return $project->fresh();
            });
        } catch (InvalidArgumentException $e) {
            // Re-throw validation exceptions as-is
            throw $e;
        } catch (\Exception $e) {
            Log::error('Failed to update project', [
                'error' => $e->getMessage(),
                'project_id' => $project->id,
                'data_keys' => array_keys($data),
                'user_id' => $user->id,
            ]);

            throw new RuntimeException('Failed to update project: '.$e->getMessage());
        }
    }

    /**
     * Delete a project
     */
    public function deleteProject(Project $project, User $user): bool
    {
        try {
            return DB::transaction(function () use ($project, $user) {
                // Check if project has contributions
                if ($project->contributions()->exists()) {
                    throw new InvalidArgumentException('Cannot delete project with existing contributions');
                }

                $projectData = [
                    'id' => $project->id,
                    'name' => $project->name,
                    'tenant_id' => $project->tenant_id,
                ];

                // Delete associated products first
                $project->products()->delete();

                // Delete the project
                $deleted = $project->delete();

                if ($deleted) {
                    $this->auditLogService::logAuthEvent(
                        'project_deleted',
                        $user,
                        null,
                        $projectData
                    );
                }

                return $deleted;
            });
        } catch (InvalidArgumentException $e) {
            // Re-throw validation exceptions as-is
            throw $e;
        } catch (\Exception $e) {
            Log::error('Failed to delete project', [
                'error' => $e->getMessage(),
                'project_id' => $project->id,
                'user_id' => $user->id,
            ]);

            throw new RuntimeException('Failed to delete project: '.$e->getMessage());
        }
    }

    /**
     * Activate a project
     */
    public function activateProject(Project $project, User $user): Project
    {
        // Validate status transition
        $this->validateStatusTransition($project, ProjectStatus::ACTIVE, $user);

        try {
            return DB::transaction(function () use ($project, $user) {
                $project->update(['status' => ProjectStatus::ACTIVE]);

                $this->auditLogService::logAuthEvent(
                    'project_activated',
                    $user,
                    null,
                    [
                        'project_id' => $project->id,
                        'project_name' => $project->name,
                        'tenant_id' => $project->tenant_id,
                        'previous_status' => $project->getOriginal('status'),
                    ]
                );

                return $project->fresh();
            });
        } catch (\Exception $e) {
            Log::error('Failed to activate project', [
                'error' => $e->getMessage(),
                'project_id' => $project->id,
                'user_id' => $user->id,
            ]);

            throw new RuntimeException('Failed to activate project: '.$e->getMessage());
        }
    }

    /**
     * Pause a project
     */
    public function pauseProject(Project $project, User $user): Project
    {
        // Validate status transition
        $this->validateStatusTransition($project, ProjectStatus::PAUSED, $user);

        try {
            return DB::transaction(function () use ($project, $user) {
                $project->update(['status' => ProjectStatus::PAUSED]);

                $this->auditLogService::logAuthEvent(
                    'project_paused',
                    $user,
                    null,
                    [
                        'project_id' => $project->id,
                        'project_name' => $project->name,
                        'tenant_id' => $project->tenant_id,
                        'previous_status' => $project->getOriginal('status'),
                    ]
                );

                return $project->fresh();
            });
        } catch (\Exception $e) {
            Log::error('Failed to pause project', [
                'error' => $e->getMessage(),
                'project_id' => $project->id,
                'user_id' => $user->id,
            ]);

            throw new RuntimeException('Failed to pause project: '.$e->getMessage());
        }
    }

    /**
     * Complete a project
     */
    public function completeProject(Project $project, User $user): Project
    {
        // Validate status transition
        $this->validateStatusTransition($project, ProjectStatus::COMPLETED, $user);

        try {
            return DB::transaction(function () use ($project, $user) {
                $project->update(['status' => ProjectStatus::COMPLETED]);

                $this->auditLogService::logAuthEvent(
                    'project_completed',
                    $user,
                    null,
                    [
                        'project_id' => $project->id,
                        'project_name' => $project->name,
                        'tenant_id' => $project->tenant_id,
                        'previous_status' => $project->getOriginal('status'),
                        'statistics' => $project->getStatistics(),
                    ]
                );

                return $project->fresh();
            });
        } catch (\Exception $e) {
            Log::error('Failed to complete project', [
                'error' => $e->getMessage(),
                'project_id' => $project->id,
                'user_id' => $user->id,
            ]);

            throw new RuntimeException('Failed to complete project: '.$e->getMessage());
        }
    }

    /**
     * Cancel a project
     */
    public function cancelProject(Project $project, User $user, ?string $reason = null): Project
    {
        // Validate status transition
        $this->validateStatusTransition($project, ProjectStatus::CANCELLED, $user);

        try {
            return DB::transaction(function () use ($project, $user, $reason) {
                $project->update([
                    'status' => ProjectStatus::CANCELLED,
                    'settings' => array_merge($project->settings ?? [], [
                        'cancellation_reason' => $reason,
                        'cancelled_at' => now()->toISOString(),
                        'cancelled_by' => $user->id,
                    ]),
                ]);

                $this->auditLogService::logAuthEvent(
                    'project_cancelled',
                    $user,
                    null,
                    [
                        'project_id' => $project->id,
                        'project_name' => $project->name,
                        'tenant_id' => $project->tenant_id,
                        'previous_status' => $project->getOriginal('status'),
                        'reason' => $reason,
                    ]
                );

                return $project->fresh();
            });
        } catch (\Exception $e) {
            Log::error('Failed to cancel project', [
                'error' => $e->getMessage(),
                'project_id' => $project->id,
                'user_id' => $user->id,
            ]);

            throw new RuntimeException('Failed to cancel project: '.$e->getMessage());
        }
    }

    /**
     * Calculate project total from products
     */
    public function calculateProjectTotal(Project $project): float
    {
        return $project->calculateTotalAmount();
    }

    /**
     * Get project statistics
     */
    public function getProjectStatistics(Project $project): array
    {
        return $project->getStatistics();
    }

    /**
     * Get public projects for discovery
     */
    public function getPublicProjects(array $filters = []): LengthAwarePaginator
    {
        $query = Project::with(['tenant', 'creator', 'products'])
            ->publiclyDiscoverable();

        $query = $this->applyFilters($query, $filters);

        $projects = $query->paginate($filters['per_page'] ?? 15);

        // Add statistics to each project
        $projects->getCollection()->transform(function ($project) {
            $project->statistics = $project->getStatistics();

            return $project;
        });

        return $projects;
    }

    /**
     * Search projects
     */
    public function searchProjects(string $searchTerm, array $filters = [], ?Tenant $tenant = null): LengthAwarePaginator
    {
        $query = Project::with(['tenant', 'creator', 'products']);

        // Apply tenant scope if provided
        if ($tenant) {
            $query->where('tenant_id', $tenant->id);
        } else {
            // For public search, only show publicly discoverable projects
            $query->publiclyDiscoverable();
        }

        // Apply search
        if (! empty($searchTerm)) {
            $query->search($searchTerm);
        }

        // Apply filters
        $query = $this->applyFilters($query, $filters);

        $projects = $query->paginate($filters['per_page'] ?? 15);

        // Add statistics to each project
        $projects->getCollection()->transform(function ($project) {
            $project->statistics = $project->getStatistics();

            return $project;
        });

        return $projects;
    }

    /**
     * Get projects for a tenant
     */
    public function getTenantProjects(Tenant $tenant, array $filters = []): LengthAwarePaginator
    {
        $query = Project::with(['creator', 'products'])
            ->where('tenant_id', $tenant->id);

        $query = $this->applyFilters($query, $filters);

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Get all projects (for system admin)
     */
    public function getAllProjects(array $filters = []): LengthAwarePaginator
    {
        $query = Project::with(['tenant', 'creator', 'products']);

        $query = $this->applyFilters($query, $filters);

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Apply filters to project query
     */
    private function applyFilters(Builder $query, array $filters): Builder
    {
        // Search filter
        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Status filter
        if (! empty($filters['status'])) {
            if (is_array($filters['status'])) {
                $query->whereIn('status', $filters['status']);
            } else {
                $query->where('status', $filters['status']);
            }
        }

        // Visibility filter
        if (! empty($filters['visibility'])) {
            if (is_array($filters['visibility'])) {
                $query->whereIn('visibility', $filters['visibility']);
            } else {
                $query->where('visibility', $filters['visibility']);
            }
        }

        // Tenant filter
        if (! empty($filters['tenant_id'])) {
            $query->where('tenant_id', $filters['tenant_id']);
        }

        // Amount range filter
        if (! empty($filters['min_amount'])) {
            $query->where('total_amount', '>=', $filters['min_amount']);
        }
        if (! empty($filters['max_amount'])) {
            $query->where('total_amount', '<=', $filters['max_amount']);
        }

        // Date range filter
        if (! empty($filters['start_date'])) {
            $query->where('start_date', '>=', $filters['start_date']);
        }
        if (! empty($filters['end_date'])) {
            $query->where('end_date', '<=', $filters['end_date']);
        }

        // Created by filter
        if (! empty($filters['created_by'])) {
            $query->where('created_by', $filters['created_by']);
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        $allowedSortFields = ['created_at', 'updated_at', 'name', 'start_date', 'end_date', 'total_amount'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortDirection);
        }

        return $query;
    }

    /**
     * Validate project data
     */
    private function validateProjectData(array $data, ?Project $existingProject = null): void
    {
        // Basic validation - only check name if it's provided or if it's a new project
        if (isset($data['name']) && empty($data['name'])) {
            throw new InvalidArgumentException('Project name cannot be empty');
        }

        if (! $existingProject && empty($data['name'])) {
            throw new InvalidArgumentException('Project name is required');
        }

        if (isset($data['start_date']) && isset($data['end_date'])) {
            $startDate = is_string($data['start_date']) ? \Carbon\Carbon::parse($data['start_date']) : $data['start_date'];
            $endDate = is_string($data['end_date']) ? \Carbon\Carbon::parse($data['end_date']) : $data['end_date'];

            if ($endDate <= $startDate) {
                throw new InvalidArgumentException('End date must be after start date');
            }
        }

        if (isset($data['total_amount']) && $data['total_amount'] < 0) {
            throw new InvalidArgumentException('Total amount must be positive');
        }

        if (isset($data['minimum_contribution']) && $data['minimum_contribution'] < 0) {
            throw new InvalidArgumentException('Minimum contribution must be positive');
        }

        if (isset($data['max_contributors']) && $data['max_contributors'] < 1) {
            throw new InvalidArgumentException('Maximum contributors must be at least 1');
        }

        // Validate visibility
        if (isset($data['visibility']) && ! in_array($data['visibility'], ['public', 'private', 'invite_only'])) {
            throw new InvalidArgumentException('Invalid visibility option');
        }
    }

    /**
     * Validate project for activation
     */
    private function validateProjectForActivation(Project $project): void
    {
        if (empty($project->name)) {
            throw new InvalidArgumentException('Project name is required for activation');
        }

        if (empty($project->description)) {
            throw new InvalidArgumentException('Project description is required for activation');
        }

        if (! $project->start_date || ! $project->end_date) {
            throw new InvalidArgumentException('Start and end dates are required for activation');
        }

        // Validate date logic
        if ($project->end_date <= $project->start_date) {
            throw new InvalidArgumentException('End date must be after start date');
        }

        // Check if project has already ended
        if ($project->end_date < now()->toDateString()) {
            throw new InvalidArgumentException('Cannot activate project that has already ended');
        }

        // Validate products
        if ($project->products()->count() === 0) {
            throw new InvalidArgumentException('At least one product is required for activation');
        }

        // Validate product pricing
        $productTotal = $project->calculateTotalAmount();
        if ($productTotal <= 0) {
            throw new InvalidArgumentException('Project must have products with positive total amount for activation');
        }

        // Validate total amount matches product total
        if ($project->total_amount > 0 && abs($project->total_amount - $productTotal) > 0.01) {
            throw new InvalidArgumentException('Project total amount must match sum of product prices');
        }

        // Validate minimum contribution if set
        if ($project->minimum_contribution && $project->minimum_contribution > $productTotal) {
            throw new InvalidArgumentException('Minimum contribution cannot exceed total project amount');
        }

        // Validate payment options
        if (empty($project->payment_options) || ! is_array($project->payment_options)) {
            throw new InvalidArgumentException('At least one payment option must be specified');
        }

        // Validate installment settings if installments are allowed
        if (in_array('installments', $project->payment_options)) {
            if ($project->installment_frequency === 'custom' && ! $project->custom_installment_months) {
                throw new InvalidArgumentException('Custom installment months must be specified when using custom frequency');
            }
        }

        // Validate registration deadline if set
        if ($project->registration_deadline && $project->registration_deadline >= $project->end_date) {
            throw new InvalidArgumentException('Registration deadline must be before project end date');
        }

        // Validate max contributors if set
        if ($project->max_contributors && $project->max_contributors < 1) {
            throw new InvalidArgumentException('Maximum contributors must be at least 1');
        }
    }

    /**
     * Generate unique slug for project
     */
    private function generateUniqueSlug(string $name, Tenant $tenant, ?int $excludeId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (true) {
            $query = Project::where('tenant_id', $tenant->id)
                ->where('slug', $slug);

            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }

            if (! $query->exists()) {
                break;
            }

            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Resume a paused project
     */
    public function resumeProject(Project $project, User $user): Project
    {
        // Validate status transition (this will also validate project readiness)
        $this->validateStatusTransition($project, ProjectStatus::ACTIVE, $user);

        try {
            return DB::transaction(function () use ($project, $user) {
                $project->update(['status' => ProjectStatus::ACTIVE]);

                $this->auditLogService::logAuthEvent(
                    'project_resumed',
                    $user,
                    null,
                    [
                        'project_id' => $project->id,
                        'project_name' => $project->name,
                        'tenant_id' => $project->tenant_id,
                        'previous_status' => $project->getOriginal('status'),
                    ]
                );

                return $project->fresh();
            });
        } catch (\Exception $e) {
            Log::error('Failed to resume project', [
                'error' => $e->getMessage(),
                'project_id' => $project->id,
                'user_id' => $user->id,
            ]);

            throw new RuntimeException('Failed to resume project: '.$e->getMessage());
        }
    }

    /**
     * Validate status transition
     */
    public function validateStatusTransition(Project $project, ProjectStatus $newStatus, User $user): void
    {
        $currentStatus = $project->status;

        // Use enum's transition validation
        if (! $currentStatus->canTransitionTo($newStatus)) {
            throw new InvalidArgumentException($currentStatus->transitionDescription($newStatus));
        }

        // Additional validation based on project state
        if ($newStatus === ProjectStatus::ACTIVE) {
            $this->validateProjectForActivation($project);
        }

        // Check if project has contributions for certain transitions
        if (in_array($newStatus, [ProjectStatus::CANCELLED]) && $project->contributions()->exists()) {
            // Allow cancellation but log it as a significant event
            Log::warning('Project with contributions is being cancelled', [
                'project_id' => $project->id,
                'project_name' => $project->name,
                'contributions_count' => $project->contributions()->count(),
                'user_id' => $user->id,
                'transition' => $currentStatus->transitionDescription($newStatus),
            ]);
        }

        // Log the transition for audit purposes
        Log::info('Project status transition validated', [
            'project_id' => $project->id,
            'project_name' => $project->name,
            'from_status' => $currentStatus->value,
            'to_status' => $newStatus->value,
            'transition_description' => $currentStatus->transitionDescription($newStatus),
            'user_id' => $user->id,
        ]);
    }

    /**
     * Update project status automatically based on dates
     */
    public function updateProjectStatusByDate(): int
    {
        $updatedCount = 0;

        try {
            // Complete projects that have passed their end date
            $expiredProjects = Project::where('status', ProjectStatus::ACTIVE)
                ->where('end_date', '<', now()->toDateString())
                ->get();

            foreach ($expiredProjects as $project) {
                try {
                    $project->update(['status' => ProjectStatus::COMPLETED]);
                    $updatedCount++;

                    // Log the automatic completion
                    Log::info('Project automatically completed', [
                        'project_id' => $project->id,
                        'project_name' => $project->name,
                        'tenant_id' => $project->tenant_id,
                        'end_date' => $project->end_date->toDateString(),
                        'completion_reason' => 'end_date_reached',
                    ]);

                    Log::info('Project automatically completed due to end date', [
                        'project_id' => $project->id,
                        'project_name' => $project->name,
                        'end_date' => $project->end_date,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to auto-complete individual project', [
                        'project_id' => $project->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Also handle projects that should be activated (if start date has arrived)
            $projectsToActivate = Project::where('status', ProjectStatus::DRAFT)
                ->where('start_date', '<=', now()->toDateString())
                ->whereNotNull('start_date')
                ->get();

            foreach ($projectsToActivate as $project) {
                try {
                    // Only auto-activate if project is complete and ready
                    if ($this->isProjectReadyForActivation($project)) {
                        $project->update(['status' => ProjectStatus::ACTIVE]);
                        $updatedCount++;

                        Log::info('Project automatically activated', [
                            'project_id' => $project->id,
                            'project_name' => $project->name,
                            'tenant_id' => $project->tenant_id,
                            'start_date' => $project->start_date->toDateString(),
                            'activation_reason' => 'start_date_reached',
                        ]);

                        Log::info('Project automatically activated due to start date', [
                            'project_id' => $project->id,
                            'project_name' => $project->name,
                            'start_date' => $project->start_date,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to auto-activate individual project', [
                        'project_id' => $project->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to update project statuses by date', [
                'error' => $e->getMessage(),
            ]);
        }

        return $updatedCount;
    }

    /**
     * Check if project is ready for automatic activation
     */
    private function isProjectReadyForActivation(Project $project): bool
    {
        try {
            $this->validateProjectForActivation($project);

            return true;
        } catch (InvalidArgumentException $e) {
            Log::debug('Project not ready for auto-activation', [
                'project_id' => $project->id,
                'reason' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
