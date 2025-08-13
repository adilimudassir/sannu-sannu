<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Project;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Services\ProjectService;
use App\Services\ProductService;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Requests\SearchProjectsRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;
use InvalidArgumentException;
use RuntimeException;

class ProjectController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private ProjectService $projectService,
        private ProductService $productService
    ) {}

    /**
     * Display a listing of projects with filtering and pagination
     */
    public function index(SearchProjectsRequest $request): Response
    {
        $this->authorize('viewAny', Project::class);

        $tenant = $request->tenant();
        $filters = $request->getFilters();

        $projects = $this->projectService->getTenantProjects($tenant, $filters);

        return Inertia::render('projects/index', [
            'projects' => $projects,
            'filters' => $filters,
            'tenant' => $tenant,
        ]);
    }

    /**
     * Show the form for creating a new project
     */
    public function create(): Response
    {
        $this->authorize('create', Project::class);

        return Inertia::render('projects/create', [
            'tenant' => request()->tenant(),
        ]);
    }

    /**
     * Store a newly created project with products
     */
    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $this->authorize('create', Project::class);

        try {
            $tenant = $request->tenant();
            $user = $request->user();

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

            return redirect()
                ->route('tenant.projects.show', ['tenant' => $tenant->slug, 'project' => $project])
                ->with('success', 'Project created successfully.');
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
     * Display the specified project with statistics
     */
    public function show(string $tenant, Project $project): Response
    {
        $this->authorize('view', $project);

        // Load relationships
        $project->load(['tenant', 'creator', 'products' => function ($query) {
            $query->ordered();
        }]);

        // Get project statistics
        $statistics = $this->projectService->getProjectStatistics($project);

        return Inertia::render('projects/show', [
            'project' => $project,
            'statistics' => $statistics,
            'canEdit' => $this->canAuthorize('update', $project),
            'canDelete' => $this->canAuthorize('delete', $project),
            'canActivate' => $this->canAuthorize('activate', $project),
            'canPause' => $this->canAuthorize('pause', $project),
        ]);
    }

    /**
     * Show the form for editing the specified project
     */
    public function edit(string $tenant, Project $project): Response
    {
        $this->authorize('update', $project);

        // Load relationships
        $project->load(['products' => function ($query) {
            $query->ordered();
        }]);

        return Inertia::render('projects/edit', [
            'project' => $project,
            'tenant' => $project->tenant,
        ]);
    }

    /**
     * Update the specified project with product management
     */
    public function update(UpdateProjectRequest $request, string $tenant, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        try {
            $user = $request->user();

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

            return redirect()
                ->route('tenant.projects.show', ['tenant' => $project->tenant->slug, 'project' => $project])
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
     * Remove the specified project with safety checks
     */
    public function destroy(string $tenant, Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);

        try {
            $user = request()->user();

            $this->projectService->deleteProject($project, $user);

            return redirect()
                ->route('tenant.projects.index', ['tenant' => $project->tenant->slug])
                ->with('success', 'Project deleted successfully.');
        } catch (InvalidArgumentException $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()]);
        } catch (RuntimeException $e) {
            return back()
                ->withErrors(['error' => 'Failed to delete project. Please try again.']);
        }
    }

    /**
     * Activate a project
     */
    public function activate(string $tenant, Project $project): RedirectResponse
    {
        $this->authorize('activate', $project);

        try {
            $user = request()->user();

            $this->projectService->activateProject($project, $user);

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
     * Pause a project
     */
    public function pause(string $tenant, Project $project): RedirectResponse
    {
        $this->authorize('pause', $project);

        try {
            $user = request()->user();

            $this->projectService->pauseProject($project, $user);

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
     * Complete a project
     */
    public function complete(string $tenant, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        try {
            $user = request()->user();

            $this->projectService->completeProject($project, $user);

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
        if (!empty($updatedProductIds)) {
            $this->productService->reorderProducts($project, $updatedProductIds);
        }
    }

    /**
     * Helper method to check authorization without throwing exceptions
     */
    private function canAuthorize(string $ability, $arguments = []): bool
    {
        try {
            $this->authorize($ability, $arguments);
            return true;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return false;
        }
    }
}
