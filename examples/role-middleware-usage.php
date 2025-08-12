<?php

// Example of how to use the RoleMiddleware in routes

use Illuminate\Support\Facades\Route;

// Routes that require system admin role only
Route::middleware(['auth', 'role:system_admin'])->group(function () {
    Route::get('/system/tenants', [SystemTenantController::class, 'index']);
    Route::post('/system/tenants', [SystemTenantController::class, 'store']);
    Route::put('/system/tenants/{tenant}', [SystemTenantController::class, 'update']);
    Route::delete('/system/tenants/{tenant}', [SystemTenantController::class, 'destroy']);
    Route::get('/system/analytics', [SystemAnalyticsController::class, 'index']);
    Route::get('/system/platform-fees', [PlatformFeeController::class, 'index']);
    Route::get('/system/users', [SystemUserController::class, 'index']);
});

// Routes that require tenant admin role only
Route::middleware(['auth', 'role:tenant_admin'])->group(function () {
    Route::get('/admin/users', [UserController::class, 'index']);
    Route::post('/admin/users/{user}/change-role', [UserController::class, 'changeRole']);
    Route::delete('/admin/users/{user}', [UserController::class, 'destroy']);
    Route::get('/admin/tenant/settings', [TenantController::class, 'settings']);
});

// Routes that require either system admin, tenant admin, or project manager
Route::middleware(['auth', 'role:system_admin,tenant_admin,project_manager'])->group(function () {
    Route::get('/projects/create', [ProjectController::class, 'create']);
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::put('/projects/{project}', [ProjectController::class, 'update']);
    Route::get('/projects/{project}/invitations', [ProjectInvitationController::class, 'index']);
    Route::post('/projects/{project}/invitations', [ProjectInvitationController::class, 'store']);
});

// Routes accessible to all authenticated users (any role)
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::get('/projects/{project}', [ProjectController::class, 'show']);
    Route::post('/contributions', [ContributionController::class, 'store']);
});

// Example controller methods using policies
class SystemTenantController extends Controller
{
    public function index()
    {
        // Gate checks if user can manage tenants (system admin only)
        $this->authorize('manage-tenants');

        return Tenant::all();
    }

    public function store(Request $request)
    {
        // Policy checks if user can create tenants
        $this->authorize('create', Tenant::class);

        $tenant = Tenant::create($request->validated());

        return response()->json($tenant, 201);
    }
}

class UserController extends Controller
{
    public function index()
    {
        // Policy automatically checks if user can viewAny users
        $this->authorize('viewAny', User::class);

        // System admins see all users, tenant admins see only their tenant users
        if (auth()->user()->isSystemAdmin()) {
            return User::all();
        }

        return User::forCurrentTenant()->get();
    }

    public function changeRole(User $user, Request $request)
    {
        // Policy checks if current user can change this user's role
        $this->authorize('changeRole', $user);

        // Additional check for system admin role assignment
        if ($request->role === 'system_admin') {
            $this->authorize('assignSystemAdminRole', $user);
        }

        $user->update(['role' => $request->role]);

        return response()->json(['message' => 'Role updated successfully']);
    }
}

class ProjectController extends Controller
{
    public function store(Request $request)
    {
        // Policy automatically checks if user can create projects
        $this->authorize('create', Project::class);

        $project = Project::create($request->validated());

        return redirect()->route('projects.show', $project);
    }

    public function update(Project $project, Request $request)
    {
        // Policy checks if user can update this specific project
        $this->authorize('update', $project);

        $project->update($request->validated());

        return redirect()->route('projects.show', $project);
    }
}
