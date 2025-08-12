<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Tenant;
use App\Models\Project;
use App\Models\Contribution;
use App\Models\ProjectInvitation;
use App\Policies\UserPolicy;
use App\Policies\TenantPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\ContributionPolicy;
use App\Policies\ProjectInvitationPolicy;
use App\Policies\SystemAdminPolicy;
use App\Policies\PlatformPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Tenant::class => TenantPolicy::class,
        Project::class => ProjectPolicy::class,
        Contribution::class => ContributionPolicy::class,
        ProjectInvitation::class => ProjectInvitationPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
        
        // Register platform management gates
        Gate::define('manage-platform', [PlatformPolicy::class, 'managePlatform']);
        Gate::define('view-all-tenants', [PlatformPolicy::class, 'viewAllTenants']);
        Gate::define('view-all-users', [PlatformPolicy::class, 'viewAllUsers']);
        Gate::define('view-system-analytics', [PlatformPolicy::class, 'viewSystemAnalytics']);
        
        // Register system admin policy for platform-wide operations
        Gate::define('system-admin', [SystemAdminPolicy::class, 'accessAdminPanel']);
        Gate::define('manage-tenants', [SystemAdminPolicy::class, 'manageTenants']);
        Gate::define('view-platform-analytics', [SystemAdminPolicy::class, 'viewPlatformAnalytics']);
        Gate::define('manage-platform-fees', [SystemAdminPolicy::class, 'managePlatformFees']);
        Gate::define('manage-system-settings', [SystemAdminPolicy::class, 'manageSystemSettings']);
        Gate::define('manage-user-roles', [SystemAdminPolicy::class, 'manageUserRoles']);
        Gate::define('view-system-logs', [SystemAdminPolicy::class, 'viewSystemLogs']);
        Gate::define('manage-integrations', [SystemAdminPolicy::class, 'manageIntegrations']);
        Gate::define('perform-maintenance', [SystemAdminPolicy::class, 'performMaintenance']);
        Gate::define('export-platform-data', [SystemAdminPolicy::class, 'exportPlatformData']);
        Gate::define('manage-payment-providers', [SystemAdminPolicy::class, 'managePaymentProviders']);
    }
}