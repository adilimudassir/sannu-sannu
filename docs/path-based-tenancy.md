# Path-Based Multi-Tenancy Setup
## Sannu-Sannu SaaS Platform

### Overview

The Sannu-Sannu platform uses **path-based tenant identification** where each tenant is accessed via a unique URL path. This approach is simple, requires no DNS configuration, and provides instant tenant setup.

---

## URL Structure

### Tenant Access Pattern
```
https://sannu-sannu.com/{tenant-slug}/
```

### Examples
```
https://sannu-sannu.com/acme-corp/
https://sannu-sannu.com/tech-startup/
https://sannu-sannu.com/retail-company/
https://sannu-sannu.com/consulting-firm/
```

### Page Examples
```
# Tenant Dashboard
https://sannu-sannu.com/acme-corp/

# Projects
https://sannu-sannu.com/acme-corp/projects
https://sannu-sannu.com/acme-corp/projects/summer-campaign

# Public Browse
https://sannu-sannu.com/acme-corp/browse

# User Management
https://sannu-sannu.com/acme-corp/users

# Payments
https://sannu-sannu.com/acme-corp/payments
```

---

## Implementation

### 1. Route Configuration

```php
<?php
// routes/web.php

use App\Http\Controllers\Tenant;
use App\Http\Controllers\Auth;

// Main platform routes (no tenant)
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/pricing', [HomeController::class, 'pricing'])->name('pricing');
Route::get('/register-tenant', [TenantRegistrationController::class, 'show'])->name('tenant.register');
Route::post('/register-tenant', [TenantRegistrationController::class, 'store']);

// Path-based tenant routes
Route::prefix('{tenant}')->middleware(['tenant'])->group(function () {
    // Authentication routes
    Route::get('/login', [Auth\LoginController::class, 'show'])->name('login');
    Route::post('/login', [Auth\LoginController::class, 'authenticate']);
    Route::post('/logout', [Auth\LoginController::class, 'logout'])->name('logout');
    
    // Tenant dashboard
    Route::get('/', [Tenant\DashboardController::class, 'index'])->name('tenant.dashboard');
    
    // Projects
    Route::resource('projects', Tenant\ProjectController::class);
    Route::post('projects/{project}/join', [Tenant\ContributionController::class, 'store'])
        ->name('projects.join');
    
    // Public browsing (no auth required)
    Route::get('browse', [Tenant\PublicController::class, 'projects'])->name('public.projects');
    Route::get('browse/{project}', [Tenant\PublicController::class, 'show'])->name('public.project');
    
    // Authenticated routes
    Route::middleware(['auth'])->group(function () {
        Route::get('my-contributions', [Tenant\ContributionController::class, 'index'])
            ->name('contributions.index');
        Route::resource('payments', Tenant\PaymentController::class);
    });
    
    // Admin routes
    Route::middleware(['auth', 'tenant.admin'])->group(function () {
        Route::resource('users', Tenant\UserController::class);
        Route::get('settings', [Tenant\SettingsController::class, 'index'])->name('tenant.settings');
    });
});
```

### 2. Tenant Identification Middleware

```php
<?php
// app/Http/Middleware/IdentifyTenant.php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;

class IdentifyTenant
{
    public function handle(Request $request, Closure $next)
    {
        // Get tenant slug from URL path
        $tenantSlug = $request->route('tenant');
        
        if (!$tenantSlug) {
            return response()->view('errors.tenant-not-found', [], 404);
        }
        
        // Find tenant by slug
        $tenant = Tenant::where('slug', $tenantSlug)
            ->where('status', 'active')
            ->first();
        
        if (!$tenant) {
            return response()->view('errors.tenant-not-found', [
                'slug' => $tenantSlug
            ], 404);
        }
        
        // Set tenant in application context
        app()->instance('tenant', $tenant);
        config(['app.tenant' => $tenant]);
        
        // Add tenant to request for easy access
        $request->merge(['current_tenant' => $tenant]);
        
        return $next($request);
    }
}
```

### 3. Tenant Model Updates

```php
<?php
// app/Models/Tenant.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Tenant extends Model
{
    protected $fillable = [
        'slug', 'name', 'domain', 'logo_url',
        'primary_color', 'secondary_color', 'platform_fee_percentage',
        'status', 'trial_ends_at', 'max_projects', 'max_users', 'max_storage_mb',
        'contact_name', 'contact_email', 'contact_phone', 'settings'
    ];

    protected $casts = [
        'settings' => 'array',
        'trial_ends_at' => 'datetime',
        'platform_fee_percentage' => 'decimal:2',
    ];

    // Generate URL for tenant
    public function getUrlAttribute(): string
    {
        return url("/{$this->slug}");
    }

    // Generate slug from name
    public static function generateSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;
        
        while (static::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }

    // Validate slug format
    public static function isValidSlug(string $slug): bool
    {
        return preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) && 
               strlen($slug) >= 3 && 
               strlen($slug) <= 50;
    }
}
```

### 4. Tenant Registration

```php
<?php
// app/Http/Controllers/TenantRegistrationController.php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class TenantRegistrationController extends Controller
{
    public function show()
    {
        return Inertia::render('TenantRegistration');
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'slug' => 'required|string|min:3|max:50|unique:tenants,slug|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255',
            'admin_password' => 'required|string|min:8|confirmed',
            'contact_phone' => 'nullable|string|max:20',
        ]);

        DB::transaction(function () use ($request) {
            // Create tenant
            $tenant = Tenant::create([
                'slug' => $request->slug,
                'name' => $request->company_name,
                'contact_name' => $request->admin_name,
                'contact_email' => $request->admin_email,
                'contact_phone' => $request->contact_phone,
                'status' => 'active',
                'trial_ends_at' => now()->addDays(30), // 30-day trial
            ]);

            // Create admin user
            User::create([
                'tenant_id' => $tenant->id,
                'name' => $request->admin_name,
                'email' => $request->admin_email,
                'password' => Hash::make($request->admin_password),
                'role' => 'tenant_admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        });

        return redirect("/{$request->slug}")
            ->with('success', 'Tenant created successfully! You can now log in.');
    }
}
```

---

## Benefits of Path-Based Approach

### 1. Simplicity
- **No DNS Management**: No need to create subdomains or manage DNS records
- **Instant Setup**: Tenants are immediately accessible after creation
- **Single Domain**: All tenants use the same domain
- **Easy SSL**: One SSL certificate covers all tenants

### 2. Cost Effectiveness
- **No Additional DNS Costs**: No need for wildcard DNS or multiple A records
- **Single SSL Certificate**: No need for wildcard SSL or multiple certificates
- **Simplified Infrastructure**: Single server configuration

### 3. Development & Maintenance
- **Easy Local Development**: Works perfectly with localhost
- **Simple Routing**: Laravel handles path-based routing natively
- **Easy Testing**: No need to configure local DNS for testing
- **Straightforward Deployment**: No special server configuration needed

### 4. SEO & Marketing
- **Clear URLs**: Easy to understand and share
- **Branded Paths**: Company name visible in URL
- **Search Engine Friendly**: Each tenant has distinct URL structure

---

## URL Examples in Practice

### Tenant Registration Flow
```
1. User visits: sannu-sannu.com/register-tenant
2. Fills form with company name "Acme Corporation"
3. System suggests slug: "acme-corporation"
4. User can customize to: "acme-corp"
5. Tenant created and accessible at: sannu-sannu.com/acme-corp/
```

### Daily Usage
```
# Admin logs in
https://sannu-sannu.com/acme-corp/login

# Admin creates project
https://sannu-sannu.com/acme-corp/projects/create

# Public user browses projects
https://sannu-sannu.com/acme-corp/browse

# User joins project
https://sannu-sannu.com/acme-corp/projects/summer-campaign

# User makes payment
https://sannu-sannu.com/acme-corp/payments/initialize
```

---

## Slug Management

### Slug Rules
- **Format**: Lowercase letters, numbers, and hyphens only
- **Length**: 3-50 characters
- **Pattern**: `^[a-z0-9]+(?:-[a-z0-9]+)*$`
- **Uniqueness**: Must be unique across all tenants

### Reserved Slugs
```php
// app/Models/Tenant.php
protected static $reservedSlugs = [
    'admin', 'api', 'www', 'mail', 'ftp', 'blog', 'shop',
    'app', 'dashboard', 'login', 'register', 'pricing',
    'about', 'contact', 'help', 'support', 'docs'
];

public static function isReservedSlug(string $slug): bool
{
    return in_array($slug, static::$reservedSlugs);
}
```

### Slug Generation
```php
// Auto-generate from company name
$slug = Tenant::generateSlug('Acme Corporation'); // "acme-corporation"
$slug = Tenant::generateSlug('Tech Startup Inc'); // "tech-startup-inc"

// Handle duplicates
$slug = Tenant::generateSlug('Acme Corp'); // "acme-corp"
// If "acme-corp" exists, generates "acme-corp-1", "acme-corp-2", etc.
```

---

## Error Handling

### Tenant Not Found
```php
// resources/views/errors/tenant-not-found.blade.php
@extends('layouts.error')

@section('title', 'Tenant Not Found')

@section('content')
<div class="text-center">
    <h1 class="text-4xl font-bold text-gray-900">404</h1>
    <h2 class="text-xl font-semibold text-gray-700 mt-4">Tenant Not Found</h2>
    <p class="text-gray-600 mt-2">
        The tenant "{{ $slug ?? 'unknown' }}" does not exist or is not active.
    </p>
    <a href="{{ url('/') }}" class="mt-6 inline-block bg-blue-600 text-white px-6 py-3 rounded-lg">
        Go to Homepage
    </a>
</div>
@endsection
```

### Invalid Slug Format
```php
// Validation in tenant registration
'slug' => [
    'required',
    'string',
    'min:3',
    'max:50',
    'unique:tenants,slug',
    'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
    function ($attribute, $value, $fail) {
        if (Tenant::isReservedSlug($value)) {
            $fail('This slug is reserved and cannot be used.');
        }
    },
],
```

---

## Future Enhancements

### Custom Domain Support (Optional)
```php
// For enterprise clients who want their own domain
// company.com -> CNAME -> sannu-sannu.com
// Still works with path-based as fallback

private function identifyTenant(Request $request): ?Tenant
{
    // Try path-based first (primary method)
    $tenantSlug = $request->route('tenant');
    if ($tenantSlug) {
        return Tenant::where('slug', $tenantSlug)->first();
    }
    
    // Fallback to custom domain
    $host = $request->getHost();
    if ($host !== 'sannu-sannu.com') {
        return Tenant::where('domain', $host)->first();
    }
    
    return null;
}
```

This path-based approach provides a simple, cost-effective, and maintainable solution for multi-tenancy without the complexity of DNS management or subdomain configuration.