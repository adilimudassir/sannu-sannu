<?php

namespace App\Http\Controllers\Contributor;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchProjectsRequest;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function __construct(
        private ProjectService $projectService
    ) {}

    /**
     * Display a listing of public projects for discovery
     */
    public function index(Request $request): Response
    {
        $filters = $request->only([
            'search',
            'status',
            'visibility',
            'min_amount',
            'max_amount',
            'start_date',
            'end_date',
            'sort_by',
            'sort_direction',
            'per_page',
        ]);

        $projects = $this->projectService->getPublicProjects($filters);

        return Inertia::render('contributor/projects/index', [
            'projects' => $projects,
            'filters' => $filters,
        ]);
    }

    /**
     * Display the specified public project
     */
    public function show(Project $project): Response
    {
        // Ensure project is publicly accessible (public visibility and active status)
        if (! $project->isPubliclyDiscoverable() || ! $project->isActive()) {
            abort(404);
        }

        // Load relationships and statistics
        $project->load(['tenant', 'creator', 'products']);
        $statistics = $project->getStatistics();

        // Add project image if available
        if ($project->products->isNotEmpty() && $project->products->first()->image_url) {
            $meta['og:image'] = $project->products->first()->image_url;
        }

        return Inertia::render('contributor/projects/show', [
            'project' => $project,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Search public projects
     */
    public function search(SearchProjectsRequest $request): Response
    {
        $searchTerm = $request->validated('search', '');
        $filters = $request->validated();

        $projects = $this->projectService->searchProjects($searchTerm, $filters);

        return Inertia::render('contributor/projects/search', [
            'projects' => $projects,
            'searchTerm' => $searchTerm,
            'filters' => $filters,
        ]);
    }
}
