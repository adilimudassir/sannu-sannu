<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchProjectsRequest;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicProjectController extends Controller
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

        return Inertia::render('public/projects/index', [
            'projects' => $projects,
            'filters' => $filters,
            'meta' => [
                'title' => 'Discover Projects',
                'description' => 'Browse and discover contribution-based projects from organizations across the platform.',
                'keywords' => 'projects, contributions, crowdfunding, community projects',
            ],
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

        // SEO-friendly meta data
        $meta = [
            'title' => $project->name.' - '.$project->tenant->name,
            'description' => $project->description ?
                substr(strip_tags($project->description), 0, 160) :
                "Join the {$project->name} project by {$project->tenant->name}",
            'keywords' => implode(', ', [
                $project->name,
                $project->tenant->name,
                'project',
                'contribution',
                'community',
            ]),
            'og:title' => $project->name,
            'og:description' => $project->description,
            'og:type' => 'website',
            'og:url' => route('public.projects.show', $project),
            'canonical' => route('public.projects.show', $project),
        ];

        // Add project image if available
        if ($project->products->isNotEmpty() && $project->products->first()->image_url) {
            $meta['og:image'] = $project->products->first()->image_url;
        }

        return Inertia::render('public/projects/show', [
            'project' => $project,
            'statistics' => $statistics,
            'meta' => $meta,
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

        return Inertia::render('public/projects/search', [
            'projects' => $projects,
            'searchTerm' => $searchTerm,
            'filters' => $filters,
            'meta' => [
                'title' => $searchTerm ? "Search Results for '{$searchTerm}'" : 'Search Projects',
                'description' => 'Search and filter contribution-based projects to find ones that interest you.',
                'noindex' => true, // Don't index search result pages
            ],
        ]);
    }
}
