<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Console\Command;

class TenantCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:cache {action=clear : Action to perform (clear|warm)} {slug? : Specific tenant slug}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage tenant cache (clear or warm up)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');
        $slug = $this->argument('slug');

        switch ($action) {
            case 'clear':
                $this->clearCache($slug);
                break;
            case 'warm':
                $this->warmCache($slug);
                break;
            default:
                $this->error("Invalid action: {$action}. Use 'clear' or 'warm'.");
                return 1;
        }

        return 0;
    }

    protected function clearCache(?string $slug): void
    {
        if ($slug) {
            TenantService::clearCache($slug);
            $this->info("Cleared cache for tenant: {$slug}");
        } else {
            $tenants = Tenant::pluck('slug');
            foreach ($tenants as $tenantSlug) {
                TenantService::clearCache($tenantSlug);
            }
            $this->info("Cleared cache for all tenants ({$tenants->count()} tenants)");
        }
    }

    protected function warmCache(?string $slug): void
    {
        if ($slug) {
            $tenant = TenantService::findBySlug($slug);
            if ($tenant) {
                $this->info("Warmed cache for tenant: {$slug}");
            } else {
                $this->error("Tenant not found: {$slug}");
            }
        } else {
            $tenants = Tenant::where('is_active', true)->get();
            foreach ($tenants as $tenant) {
                TenantService::findBySlug($tenant->slug);
            }
            $this->info("Warmed cache for all active tenants ({$tenants->count()} tenants)");
        }
    }
}
