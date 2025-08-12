<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectService
{
    public function createProject(Request $request): Project
    {
        $tenant = app(Tenant::class);

        return Project::create([
            'name' => $request->name,
            'description' => $request->description,
            'tenant_id' => $tenant->id,
            'created_by' => $request->user()->id,
        ]);
    }

    public function updateProject(Request $request, Project $project): Project
    {
        $project->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return $project;
    }
}
