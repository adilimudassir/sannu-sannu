# Tenant Middleware Documentation

## Overview

The Tenant Middleware (`IdentifyTenant`) is responsible for extracting tenant information from incoming requests and setting the appropriate tenant context throughout the application. **Note**: With the new Authentication system, tenant context is now optional for many operations.

## Features

### Multi-Resolution Strategy
- **Subdomain Resolution**: Extracts tenant from subdomain (e.g., `acme.sannu-sannu.com`)
- **Path Resolution**: Extracts tenant from URL path (e.g., `/acme/dashboard`)
- **Fallback Support**: Gracefully handles both resolution methods
- **Optional Context**: Many routes now work without tenant context (Authentication)

### Error Handling
- **Invalid Tenant**: Returns 404 for non-existent tenants
- **Inactive Tenant**: Blocks access to deactivated tenants
- **Logging**: Comprehensive logging for debugging and security

### Context Management
- **Service Container**: Stores tenant instance in Laravel's service container
- **View Sharing**: Makes tenant available to all Blade views
- **Frontend Context**: Provides tenant data to Inertia.js frontend

## Usage

### Accessing Current Tenant

```php
// Using the service container
$tenant = app('tenant');

// Using the TenantService helper
$tenant = \App\Services\TenantService::current();

// Check if tenant context exists
if (\App\Services\TenantService::hasTenant()) {
    // Tenant context is available
}
```

### Frontend Access

The tenant context is automatically shared with all Inertia.js pages:

```typescript
// In any React component
import { usePage } from '@inertiajs/react';

const { tenant } = usePage().props;
console.log(tenant.slug, tenant.name);
```

### Model Scoping

Models that belong to tenants should use the `BelongsToTenant` trait:

```php
use App\Models\Concerns\BelongsToTenant;

class Project extends Model
{
    use BelongsToTenant;
    
    // Model will automatically be scoped to current tenant
}
```

## Configuration

### Route Groups

The middleware is applied to tenant-scoped routes, but many routes now work globally:

```php
// Global routes (no tenant context required)
Route::middleware('auth')->group(function () {
    Route::get('dashboard', GlobalDashboardController::class)->name('dashboard');
    Route::get('select-tenant', TenantSelectionController::class)->name('tenant.select');
    // System admin routes
    Route::prefix('admin')->group(function () {
        Route::get('dashboard', SystemDashboardController::class)->name('admin.dashboard');
    });
});

// Tenant-specific routes (operational context)
Route::prefix('{tenant:slug}')
    ->middleware(['web', 'tenant'])
    ->group(function () {
        // Routes here have tenant context for management operations
    });
```

### Middleware Registration

The middleware is registered in `bootstrap/app.php`:

```php
$middleware->web(append: [
    \App\Http\Middleware\IdentifyTenant::class,
    // ... other middleware
]);

$middleware->alias([
    'tenant' => \App\Http\Middleware\IdentifyTenant::class,
]);
```

## Cache Management

Use the tenant cache command to manage tenant resolution caching:

```bash
# Clear cache for all tenants
php artisan tenant:cache clear

# Clear cache for specific tenant
php artisan tenant:cache clear acme

# Warm cache for all tenants
php artisan tenant:cache warm

# Warm cache for specific tenant
php artisan tenant:cache warm acme
```

## Security Considerations

- **Tenant Isolation**: Ensures complete data separation between tenants
- **Active Status**: Only allows access to active tenants
- **Audit Logging**: Logs all tenant resolution attempts
- **Error Handling**: Prevents information disclosure through error messages

## Testing

The middleware includes comprehensive tests covering:
- Tenant resolution from paths
- Invalid tenant handling
- Inactive tenant blocking
- Context sharing
- Service integration

Run tests with:
```bash
php artisan test tests/Feature/TenantMiddlewareTest.php
```

## Troubleshooting

### Common Issues

1. **Tenant Not Found**: Check tenant slug and active status
2. **Context Missing**: Ensure middleware is properly registered
3. **Cache Issues**: Clear tenant cache if data seems stale

### Debug Information

The middleware logs detailed information for debugging:
- Tenant resolution attempts
- Failed lookups
- Context setting operations

Check logs in `storage/logs/laravel.log` for tenant-related entries.