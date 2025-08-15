<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;

class TenantService
{
    /**
     * Get the current tenant
     */
    public static function current(): ?Tenant
    {
        return app()->bound('tenant') ? app('tenant') : null;
    }

    /**
     * Check if we're in a tenant context
     */
    public static function hasTenant(): bool
    {
        return app()->bound('tenant') && app('tenant') instanceof Tenant;
    }

    /**
     * Get tenant by slug with caching
     */
    public static function findBySlug(string $slug): ?Tenant
    {
        return Cache::remember(
            "tenant.slug.{$slug}",
            now()->addMinutes(60),
            fn () => Tenant::where('slug', $slug)
                ->where('is_active', true)
                ->first()
        );
    }

    /**
     * Clear tenant cache
     */
    public static function clearCache(string $slug): void
    {
        Cache::forget("tenant.slug.{$slug}");
    }

    /**
     * Get tenant URL for a given slug
     */
    public static function getUrl(string $slug, string $path = ''): string
    {
        $baseUrl = config('app.url');
        $host = parse_url($baseUrl, PHP_URL_HOST);
        $scheme = parse_url($baseUrl, PHP_URL_SCHEME);
        $port = parse_url($baseUrl, PHP_URL_PORT);

        // Build subdomain URL
        $url = $scheme.'://'.$slug.'.'.$host;

        if ($port && ! in_array($port, [80, 443])) {
            $url .= ':'.$port;
        }

        if ($path) {
            $url .= '/'.ltrim($path, '/');
        }

        return $url;
    }

    /**
     * Get the tenant context for frontend
     */
    public static function getContextForFrontend(): array
    {
        $tenant = self::current();

        if (! $tenant) {
            return [];
        }

        // Get session service to check for tenant context
        $sessionService = app(SessionService::class);
        $tenantContext = $sessionService->getTenantContext(request());

        return [
            'id' => $tenant->id,
            'slug' => $tenant->slug,
            'name' => $tenant->name,
            'logo_url' => $tenant->logo_url,
            'primary_color' => $tenant->primary_color,
            'secondary_color' => $tenant->secondary_color,
            'role' => $tenantContext['tenant_role'] ?? null,
            'context_set_at' => $tenantContext['context_set_at'] ?? null,
        ];
    }

    /**
     * Switch tenant context (useful for admin operations)
     */
    public static function switchTo(Tenant $tenant): void
    {
        app()->instance('tenant', $tenant);
    }

    /**
     * Clear tenant context
     */
    public static function clear(): void
    {
        app()->forgetInstance('tenant');
    }
}
