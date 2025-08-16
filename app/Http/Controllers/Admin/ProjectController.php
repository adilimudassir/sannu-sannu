<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchProjectsRequest;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Product;
use App\Models\Project;
use App\Models\Tenant;
use App\Services\AuditLogService;
use App\Services\ProductService;
use App\Services\ProjectService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;
use RuntimeException;

class ProjectController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private ProjectService $projectService,
        private ProductService $productService,
        private AuditLogService $auditLogService
    ) {}

    /**
     * Display a listing of all projects across tenants (System Admin)
     */
    public function index(SearchProjectsRequest $request): Response
    {
        $this->authorize('manage-platform');

        $filters = $request->getFilters();

        // Get all projects across tenants for system admin
        $projects = $this->projectService->getAllProjects($filters);

        // Get all tenants for filtering
        $tenants = Tenant::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return Inertia::render('admin/projects/index', [
            'projects' => $projects,
            'filters' => $filters,
            'tenants' => $tenants,
        ]);
    }

    /**
     * Show the form for creating a new project (System Admin)
     */
    public function create(): Response
    {
        $this->authorize('manage-platform');

        // Get all active tenants for selection
        $tenants = Tenant::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return Inertia::render('admin/projects/create', [
            'tenants' => $tenants,
        ]);
    }

    /**
     * Store a newly created project with products (System Admin)
     */
    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $this->authorize('manage-platform');

        try {
            $user = $request->user();

            // Get the selected tenant
            $tenantId = $request->input('tenant_id');
            if (! $tenantId) {
                throw new InvalidArgumentException('Tenant selection is required for system admin project creation.');
            }

            $tenant = Tenant::findOrFail($tenantId);

            // Create the project
            $project = $this->projectService->createProject(
                $request->getProjectData(),
                $tenant,
                $user
            );

            // Add products to the project
            $productsData = $request->getProductsData();
            foreach ($productsData as $productData) {
                $this->productService->addProduct($project, $productData);
            }

            // Recalculate project total after adding products
            $this->projectService->calculateProjectTotal($project);

            // Log system admin action
            $this->auditLogService->log(
                'project_created_by_admin',
                $project,
                $user,
                [
                    'tenant_id' => $tenant->id,
                    'tenant_name' => $tenant->name,
                    'project_name' => $project->name,
                    'admin_override' => true,
                ]
            );

            return redirect()
                ->route('admin.projects.show', ['project' => $project])
                ->with('success', 'Project created successfully for tenant: '.$tenant->name);

        } catch (InvalidArgumentException $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        } catch (RuntimeException $e) {
            return back()
                ->withErrors(['error' => 'Failed to create project. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Display the specified project with statistics (System Admin)
     */
    public function show(Project $project): Response
    {
        $this->authorize('manage-platform');

        // Load relationships
        $project->load(['tenant', 'creator', 'products' => function ($query) {
            $query->ordered();
        }]);

        // Get project statistics
        $statistics = $this->projectService->getProjectStatistics($project);

        return Inertia::render('admin/projects/show', [
            'project' => $project,
            'statistics' => $statistics,
            'canEdit' => true, // System admin can always edit
            'canDelete' => true, // System admin can always delete
            'canActivate' => true, // System admin can always activate
            'canPause' => true, // System admin can always pause
            'canResume' => true, // System admin can always resume
            'canComplete' => true, // System admin can always complete
            'canCancel' => true, // System admin can always cancel
        ]);
    }

    /**
     * Show the form for editing the specified project (System Admin)
     */
    public function edit(Project $project): Response
    {
        $this->authorize('manage-platform');

        // Load relationships
        $project->load(['tenant', 'products' => function ($query) {
            $query->ordered();
        }]);

        // Get all active tenants for potential tenant change
        $tenants = Tenant::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return Inertia::render('admin/projects/edit', [
            'project' => $project,
            'tenants' => $tenants,
        ]);
    }

    /**
     * Update the specified project with product management (System Admin)
     */
    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('manage-platform');

        try {
            $user = $request->user();
            $originalTenantId = $project->tenant_id;

            // Handle tenant change if provided
            $newTenantId = $request->input('tenant_id');
            if ($newTenantId && $newTenantId != $originalTenantId) {
                $newTenant = Tenant::findOrFail($newTenantId);
                $project->tenant_id = $newTenantId;
                $project->save();

                // Log tenant change
                $this->auditLogService->log(
                    'project_tenant_changed_by_admin',
                    $project,
                    $user,
                    [
                        'original_tenant_id' => $originalTenantId,
                        'new_tenant_id' => $newTenantId,
                        'new_tenant_name' => $newTenant->name,
                        'admin_override' => true,
                    ]
                );
            }

            // Update the project
            $project = $this->projectService->updateProject(
                $project,
                $request->getProjectData(),
                $user
            );

            // Handle product updates if provided
            if ($request->hasProductsData()) {
                $this->handleProductUpdates($project, $request->getProductsData());
            }

            // Recalculate project total after product changes
            $this->projectService->calculateProjectTotal($project);

            // Log system admin action
            $this->auditLogService->log(
                'project_updated_by_admin',
                $project,
                $user,
                [
                    'tenant_id' => $project->tenant_id,
                    'tenant_name' => $project->tenant->name,
                    'project_name' => $project->name,
                    'admin_override' => true,
                ]
            );

            return redirect()
                ->route('admin.projects.show', ['project' => $project])
                ->with('success', 'Project updated successfully.');

        } catch (InvalidArgumentException $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        } catch (RuntimeException $e) {
            return back()
                ->withErrors(['error' => 'Failed to update project. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Remove the specified project with safety checks (System Admin)
     */
    public function destroy(Project $project): RedirectResponse
    {
        $this->authorize('manage-platform');

        try {
            $user = request()->user();
            $tenantName = $project->tenant->name;
            $projectName = $project->name;

            // Log before deletion
            $this->auditLogService->log(
                'project_deleted_by_admin',
                $project,
                $user,
                [
                    'tenant_id' => $project->tenant_id,
                    'tenant_name' => $tenantName,
                    'project_name' => $projectName,
                    'admin_override' => true,
                ]
            );

            $this->projectService->deleteProject($project, $user);

            return redirect()
                ->route('admin.projects.index')
                ->with('success', "Project '{$projectName}' from tenant '{$tenantName}' deleted successfully.");

        } catch (InvalidArgumentException $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()]);
        } catch (RuntimeException $e) {
            return back()
                ->withErrors(['error' => 'Failed to delete project. Please try again.']);
        }
    }

    /**
     * Activate a project (System Admin Override)
     */
    public function activate(Project $project): RedirectResponse
    {
        $this->authorize('manage-platform');

        try {
            $user = request()->user();

            $this->projectService->activateProject($project, $user);

            // Log system admin action
            $this->auditLogService->log(
                'project_activated_by_admin',
                $project,
                $user,
                [
                    'tenant_id' => $project->tenant_id,
                    'tenant_name' => $project->tenant->name,
                    'project_name' => $project->name,
                    'admin_override' => true,
                ]
            );

            return back()
                ->with('success', 'Project activated successfully.');

        } catch (InvalidArgumentException $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()]);
        } catch (RuntimeException $e) {
            return back()
                ->withErrors(['error' => 'Failed to activate project. Please try again.']);
        }
    }

    /**
     * Pause a project (System Admin Override)
     */
    public function pause(Project $project): RedirectResponse
    {
        $this->authorize('manage-platform');

        try {
            $user = request()->user();

            $this->projectService->pauseProject($project, $user);

            // Log system admin action
            $this->auditLogService->log(
                'project_paused_by_admin',
                $project,
                $user,
                [
                    'tenant_id' => $project->tenant_id,
                    'tenant_name' => $project->tenant->name,
                    'project_name' => $project->name,
                    'admin_override' => true,
                ]
            );

            return back()
                ->with('success', 'Project paused successfully.');

        } catch (InvalidArgumentException $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()]);
        } catch (RuntimeException $e) {
            return back()
                ->withErrors(['error' => 'Failed to pause project. Please try again.']);
        }
    }

    /**
     * Complete a project (System Admin Override)
     */
    public function complete(Project $project): RedirectResponse
    {
        $this->authorize('manage-platform');

        try {
            $user = request()->user();

            $this->projectService->completeProject($project, $user);

            // Log system admin action
            $this->auditLogService->log(
                'project_completed_by_admin',
                $project,
                $user,
                [
                    'tenant_id' => $project->tenant_id,
                    'tenant_name' => $project->tenant->name,
                    'project_name' => $project->name,
                    'admin_override' => true,
                ]
            );

            return back()
                ->with('success', 'Project completed successfully.');

        } catch (InvalidArgumentException $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()]);
        } catch (RuntimeException $e) {
            return back()
                ->withErrors(['error' => 'Failed to complete project. Please try again.']);
        }
    }

    /**
     * Resume a paused project (System Admin Override)
     */
    public function resume(Project $project): RedirectResponse
    {
        $this->authorize('manage-platform');

        try {
            $user = request()->user();

            $this->projectService->resumeProject($project, $user);

            // Log system admin action
            $this->auditLogService->log(
                'project_resumed_by_admin',
                $project,
                $user,
                [
                    'tenant_id' => $project->tenant_id,
                    'tenant_name' => $project->tenant->name,
                    'project_name' => $project->name,
                    'admin_override' => true,
                ]
            );

            return back()
                ->with('success', 'Project resumed successfully.');

        } catch (InvalidArgumentException $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()]);
        } catch (RuntimeException $e) {
            return back()
                ->withErrors(['error' => 'Failed to resume project. Please try again.']);
        }
    }

    /**
     * Cancel a project (System Admin Override)
     */
    public function cancel(Project $project): RedirectResponse
    {
        $this->authorize('manage-platform');

        try {
            $user = request()->user();
            $reason = request()->input('reason');

            $this->projectService->cancelProject($project, $user, $reason);

            // Log system admin action
            $this->auditLogService->log(
                'project_cancelled_by_admin',
                $project,
                $user,
                [
                    'tenant_id' => $project->tenant_id,
                    'tenant_name' => $project->tenant->name,
                    'project_name' => $project->name,
                    'reason' => $reason,
                    'admin_override' => true,
                ]
            );

            return back()
                ->with('success', 'Project cancelled successfully.');

        } catch (InvalidArgumentException $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()]);
        } catch (RuntimeException $e) {
            return back()
                ->withErrors(['error' => 'Failed to cancel project. Please try again.']);
        }
    }

    /**
     * Handle product updates for a project
     */
    private function handleProductUpdates(Project $project, array $productsData): void
    {
        $existingProductIds = $project->products->pluck('id')->toArray();
        $updatedProductIds = [];

        foreach ($productsData as $productData) {
            if (isset($productData['id']) && in_array($productData['id'], $existingProductIds)) {
                // Update existing product
                $product = Product::find($productData['id']);
                $this->productService->updateProduct($product, $productData);
                $updatedProductIds[] = $productData['id'];
            } else {
                // Add new product
                $newProduct = $this->productService->addProduct($project, $productData);
                $updatedProductIds[] = $newProduct->id;
            }
        }

        // Remove products that are no longer in the update
        $productsToRemove = array_diff($existingProductIds, $updatedProductIds);
        foreach ($productsToRemove as $productId) {
            $product = Product::find($productId);
            if ($product) {
                try {
                    $this->productService->deleteProduct($product);
                } catch (RuntimeException $e) {
                    // If we can't delete due to contributions, just skip
                    continue;
                }
            }
        }

        // Handle product reordering if sort orders are provided
        if (! empty($updatedProductIds)) {
            $this->productService->reorderProducts($project, $updatedProductIds);
        }
    }
}
