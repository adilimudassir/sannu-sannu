<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class IdentifyTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if this is a tenant-scoped route
        $isTenantRoute = $this->isTenantRoute($request);
        
        $tenant = $this->resolveTenant($request);
        
        if ($tenant) {
            $this->setTenantContext($tenant, $request);
        } elseif ($isTenantRoute) {
            // If this is a tenant route but no tenant was found, handle the error
            $this->handleTenantRequired($request);
        }

        return $next($request);
    }

    /**
     * Check if the current route requires tenant context
     */
    protected function isTenantRoute(Request $request): bool
    {
        $route = $request->route();
        
        if (!$route) {
            return false;
        }

        // Check if route has tenant parameter
        if ($route->hasParameter('tenant')) {
            return true;
        }

        $routeNamesExceptions = [
            'tenant.select',
            'tenant.select.store'
        ];

        if (in_array($route->getName(), $routeNamesExceptions)) {
            return false;
        }

        // Check if route name starts with 'tenant.'
        $routeName = $route->getName();
        if ($routeName && str_starts_with($routeName, 'tenant.')) {
            return true;
        }

        // Check if this is a subdomain-based tenant route
        $host = $request->getHost();
        $appUrl = parse_url(config('app.url'), PHP_URL_HOST);
        
        return $appUrl && str_ends_with($host, '.' . $appUrl) && $host !== $appUrl;
    }

    /**
     * Resolve tenant from the request
     */
    protected function resolveTenant(Request $request): ?Tenant
    {
        // Try to get tenant from subdomain first
        $tenant = $this->resolveTenantFromSubdomain($request);
        
        // If not found, try to get from URL path
        if (!$tenant) {
            $tenant = $this->resolveTenantFromPath($request);
        }

        return $tenant;
    }

    /**
     * Resolve tenant from subdomain
     */
    protected function resolveTenantFromSubdomain(Request $request): ?Tenant
    {
        $host = $request->getHost();
        $appUrl = parse_url(config('app.url'), PHP_URL_HOST);
        
        // Check if this is a subdomain request
        if ($appUrl && str_ends_with($host, '.' . $appUrl)) {
            $slug = str_replace('.' . $appUrl, '', $host);
            
            try {
                return Tenant::where('slug', $slug)
                    ->where('is_active', true)
                    ->firstOrFail();
            } catch (ModelNotFoundException $e) {
                $this->handleTenantNotFound($request, $slug, 'subdomain');
                return null;
            }
        }

        return null;
    }

    /**
     * Resolve tenant from URL path
     */
    protected function resolveTenantFromPath(Request $request): ?Tenant
    {
        $slug = $request->route('tenant');

        if ($slug) {
            try {
                return Tenant::where('slug', $slug)
                    ->where('is_active', true)
                    ->firstOrFail();
            } catch (ModelNotFoundException $e) {
                $this->handleTenantNotFound($request, $slug, 'path');
                return null;
            }
        }

        return null;
    }

    /**
     * Set tenant context in the application
     */
    protected function setTenantContext(Tenant $tenant, Request $request): void
    {
        // Store tenant instance in the service container
        app()->instance('tenant', $tenant);
        
        // Share tenant with all views
        View::share('tenant', $tenant);
        
        // Set tenant-specific configuration if needed
        $this->configureTenantSettings($tenant);
        
        // Log tenant resolution for debugging
        Log::debug('Tenant resolved', [
            'tenant_id' => $tenant->id,
            'tenant_slug' => $tenant->slug,
            'request_url' => $request->fullUrl(),
            'ip_address' => $request->ip(),
        ]);
    }

    /**
     * Configure tenant-specific settings
     */
    protected function configureTenantSettings(Tenant $tenant): void
    {
        // Set tenant-specific mail configuration if needed
        if ($tenant->settings && isset($tenant->settings['mail_from_address'])) {
            Config::set('mail.from.address', $tenant->settings['mail_from_address']);
        }
        
        if ($tenant->settings && isset($tenant->settings['mail_from_name'])) {
            Config::set('mail.from.name', $tenant->settings['mail_from_name']);
        }
    }

    /**
     * Handle tenant not found scenarios
     */
    protected function handleTenantNotFound(Request $request, string $slug, string $source): void
    {
        Log::warning('Tenant not found', [
            'slug' => $slug,
            'source' => $source,
            'request_url' => $request->fullUrl(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // For API requests, throw exception to return JSON error
        if ($request->expectsJson()) {
            abort(404, 'Tenant not found');
        }

        // For web requests, redirect to main site with error message
        if ($source === 'subdomain') {
            // Redirect to main domain with error
            $mainUrl = config('app.url') . '/tenant-not-found?slug=' . urlencode($slug);
            abort(redirect($mainUrl));
        } else {
            // For path-based, redirect to tenant not found page
            abort(redirect()->route('tenant.not-found', ['slug' => $slug]));
        }
    }

    /**
     * Handle cases where tenant is required but not found
     */
    protected function handleTenantRequired(Request $request): void
    {
        $slug = $request->route('tenant') ?? 'unknown';
        
        Log::warning('Tenant required but not found', [
            'slug' => $slug,
            'request_url' => $request->fullUrl(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // For API requests, return JSON error
        if ($request->expectsJson()) {
            abort(404, 'Organization not found');
        }

        // For web requests, redirect to tenant not found page
        abort(redirect()->route('tenant.not-found', ['slug' => $slug]));
    }

    /**
     * Get the current tenant instance
     */
    public static function current(): ?Tenant
    {
        return app('tenant');
    }
}
