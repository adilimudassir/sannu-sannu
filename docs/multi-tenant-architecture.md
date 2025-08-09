# Multi-Tenant Architecture

## Sannu-Sannu SaaS Platform

### Overview

Sannu-Sannu is designed as a multi-tenant SaaS platform where companies and businesses can register, create their own workspace, and manage contribution-based projects. The architecture ensures complete data isolation, scalability, and customization per tenant.

---

## Tenancy Model

### Single Database Multi-Tenancy

The platform uses a **single database with tenant isolation** approach, providing:

- **Cost Efficiency**: Shared infrastructure reduces operational costs
- **Maintenance Simplicity**: Single codebase and database to maintain
- **Scalability**: Easier to scale horizontally
- **Data Isolation**: Complete separation of tenant data through foreign keys

### Tenant Identification

The platform uses **path-based tenant identification** for simplicity and ease of management.

```php
// Primary tenant identification method
Path-based: sannu-sannu.com/{tenant-slug}

// Examples:
- sannu-sannu.com/acme-corp
- sannu-sannu.com/tech-startup
- sannu-sannu.com/retail-company

// Future enhancement (optional):
- Custom Domain: company.com (with CNAME pointing to sannu-sannu.com)
```

#### Benefits of Path-Based Approach

- **No DNS Management**: No need to create subdomains
- **Instant Setup**: Tenants are immediately accessible
- **Simple Routing**: Single domain with path routing
- **Easy SSL**: One SSL certificate covers all tenants
- **Cost Effective**: No additional DNS or SSL costs

---

## Database Schema Updates

### Core Tenancy Tables

#### TENANTS Table

```sql
CREATE TABLE tenants (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(100) NOT NULL UNIQUE, -- Used in URL path: sannu-sannu.com/{slug}
    name VARCHAR(255) NOT NULL,
    domain VARCHAR(255) NULL UNIQUE, -- Optional custom domain for future
    logo_url VARCHAR(500) NULL,
    primary_color VARCHAR(7) DEFAULT '#3B82F6',
    secondary_color VARCHAR(7) DEFAULT '#10B981',

    -- Revenue sharing model
    platform_fee_percentage DECIMAL(5,2) DEFAULT 5.00, -- Platform fee (e.g., 5%)
    status ENUM('active', 'suspended', 'cancelled') DEFAULT 'active',
    trial_ends_at TIMESTAMP NULL,

    -- Optional limits (for abuse prevention)
    max_projects INT NULL, -- NULL = unlimited
    max_users INT NULL, -- NULL = unlimited
    max_storage_mb INT DEFAULT 10000, -- 10GB default

    -- Contact information
    contact_name VARCHAR(255) NOT NULL,
    contact_email VARCHAR(255) NOT NULL,
    contact_phone VARCHAR(20) NULL,

    -- Settings
    settings JSON NULL,
    is_active BOOLEAN DEFAULT TRUE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_tenants_slug (slug),
    INDEX idx_tenants_domain (domain),
    INDEX idx_tenants_status (subscription_status),
    INDEX idx_tenants_active (is_active)
) ENGINE=InnoDB;
```

#### Updated USERS Table

```sql
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,

    -- Role within tenant
    role ENUM('tenant_admin', 'project_manager', 'contributor') NOT NULL DEFAULT 'contributor',

    -- User preferences
    theme_preference VARCHAR(50) DEFAULT 'default',
    custom_theme_colors JSON NULL,

    -- Profile information
    phone VARCHAR(20) NULL,
    avatar_url VARCHAR(500) NULL,
    bio TEXT NULL,

    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    email_verified_at TIMESTAMP NULL,
    last_login_at TIMESTAMP NULL,

    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    UNIQUE KEY uk_users_tenant_email (tenant_id, email),
    INDEX idx_users_tenant (tenant_id),
    INDEX idx_users_email (email),
    INDEX idx_users_role (role),
    INDEX idx_users_active (is_active)
) ENGINE=InnoDB;
```

#### Updated PROJECTS Table

```sql
CREATE TABLE projects (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    description TEXT NULL,

    -- Project visibility and access
    visibility ENUM('public', 'private') NOT NULL DEFAULT 'public',
    requires_approval BOOLEAN DEFAULT FALSE,

    -- Financial details
    total_amount DECIMAL(12,2) NOT NULL,
    minimum_contribution DECIMAL(10,2) NULL,

    -- Payment flexibility
    payment_options JSON NOT NULL, -- ['full', 'installments']
    installment_frequency ENUM('monthly', 'quarterly', 'custom') DEFAULT 'monthly',
    custom_installment_months INT NULL,

    -- Timeline
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    registration_deadline DATE NULL,

    -- Project management
    created_by INT UNSIGNED NOT NULL,
    managed_by JSON NULL, -- Array of user IDs who can manage
    status ENUM('draft', 'active', 'paused', 'completed', 'cancelled') NOT NULL DEFAULT 'draft',

    -- Settings
    settings JSON NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    UNIQUE KEY uk_projects_tenant_slug (tenant_id, slug),
    INDEX idx_projects_tenant (tenant_id),
    INDEX idx_projects_visibility (visibility),
    INDEX idx_projects_status (status),
    INDEX idx_projects_dates (start_date, end_date),

    CONSTRAINT chk_projects_dates CHECK (end_date > start_date),
    CONSTRAINT chk_projects_amounts CHECK (total_amount > 0)
) ENGINE=InnoDB;
```

#### PROJECT_INVITATIONS Table (for private projects)

```sql
CREATE TABLE project_invitations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id INT UNSIGNED NOT NULL,
    email VARCHAR(255) NOT NULL,
    invited_by INT UNSIGNED NOT NULL,
    token VARCHAR(100) NOT NULL UNIQUE,

    status ENUM('pending', 'accepted', 'declined', 'expired') DEFAULT 'pending',
    expires_at TIMESTAMP NOT NULL,
    accepted_at TIMESTAMP NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (invited_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_invitations_project (project_id),
    INDEX idx_invitations_email (email),
    INDEX idx_invitations_token (token),
    INDEX idx_invitations_status (status)
) ENGINE=InnoDB;
```

#### Updated CONTRIBUTIONS Table

```sql
CREATE TABLE contributions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    project_id INT UNSIGNED NOT NULL,

    -- Contribution details
    total_committed DECIMAL(12,2) NOT NULL,
    payment_type ENUM('full', 'installments') NOT NULL,

    -- Installment details (if applicable)
    installment_amount DECIMAL(10,2) NULL,
    installment_frequency ENUM('monthly', 'quarterly', 'custom') NULL,
    total_installments INT NULL,

    -- Arrears calculation for late joiners
    arrears_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    arrears_paid DECIMAL(10,2) NOT NULL DEFAULT 0.00,

    -- Payment tracking
    total_paid DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    next_payment_due DATE NULL,

    -- Status and timeline
    status ENUM('active', 'completed', 'suspended', 'cancelled') NOT NULL DEFAULT 'active',
    joined_date DATE NOT NULL,

    -- Approval workflow (for private projects)
    approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'approved',
    approved_by INT UNSIGNED NULL,
    approved_at TIMESTAMP NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,

    UNIQUE KEY uk_contributions_user_project (user_id, project_id),
    INDEX idx_contributions_tenant (tenant_id),
    INDEX idx_contributions_user (user_id),
    INDEX idx_contributions_project (project_id),
    INDEX idx_contributions_status (status),
    INDEX idx_contributions_approval (approval_status),
    INDEX idx_contributions_next_payment (next_payment_due),

    CONSTRAINT chk_contributions_amounts CHECK (
        total_committed > 0 AND
        total_paid >= 0 AND
        arrears_amount >= 0 AND
        arrears_paid >= 0
    )
) ENGINE=InnoDB;
```

---

## Laravel Implementation

### Tenant Model

```php
<?php
// app/Models/Tenant.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    protected $fillable = [
        'slug', 'name', 'domain', 'subdomain', 'logo_url',
        'primary_color', 'secondary_color', 'plan_type',
        'subscription_status', 'trial_ends_at', 'subscription_ends_at',
        'max_projects', 'max_users', 'max_storage_mb',
        'contact_name', 'contact_email', 'contact_phone',
        'settings', 'is_active'
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'trial_ends_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
    ];

    // Relationships
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(Contribution::class);
    }

    // Helper methods
    public function isActive(): bool
    {
        return $this->is_active &&
               $this->subscription_status === 'active' &&
               ($this->subscription_ends_at === null || $this->subscription_ends_at->isFuture());
    }

    public function isOnTrial(): bool
    {
        return $this->subscription_status === 'trial' &&
               $this->trial_ends_at !== null &&
               $this->trial_ends_at->isFuture();
    }

    public function canCreateProject(): bool
    {
        return $this->projects()->count() < $this->max_projects;
    }

    public function canAddUser(): bool
    {
        return $this->users()->count() < $this->max_users;
    }

    public function getFullDomain(): string
    {
        return $this->domain ?: "{$this->subdomain}.sannu-sannu.com";
    }
}
```

### Tenant Middleware

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
        $tenant = $this->identifyTenant($request);

        if (!$tenant || !$tenant->isActive()) {
            return response()->view('errors.tenant-not-found', [], 404);
        }

        // Set tenant in application context
        app()->instance('tenant', $tenant);
        config(['app.tenant' => $tenant]);

        // Set tenant-specific database connection if needed
        // This is for advanced multi-database setups

        return $next($request);
    }

    private function identifyTenant(Request $request): ?Tenant
    {
        // Primary method: Path-based identification
        $tenantSlug = $request->segment(1);

        if ($tenantSlug) {
            $tenant = Tenant::where('slug', $tenantSlug)->first();

            if ($tenant) {
                return $tenant;
            }
        }

        // Optional: Custom domain support (future enhancement)
        $host = $request->getHost();
        if ($host !== 'sannu-sannu.com' && $host !== 'localhost') {
            $tenant = Tenant::where('domain', $host)->first();
            if ($tenant) {
                return $tenant;
            }
        }

        return null;
    }
}
```

### Tenant Scope Trait

```php
<?php
// app/Traits/BelongsToTenant.php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant()
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (app()->has('tenant')) {
                $builder->where('tenant_id', app('tenant')->id);
            }
        });

        static::creating(function ($model) {
            if (app()->has('tenant') && !$model->tenant_id) {
                $model->tenant_id = app('tenant')->id;
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
```

### Updated Models with Tenancy

```php
<?php
// app/Models/User.php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'name', 'email', 'password', 'role',
        'theme_preference', 'custom_theme_colors',
        'phone', 'avatar_url', 'bio', 'is_active'
    ];

    protected $casts = [
        'custom_theme_colors' => 'array',
        'is_active' => 'boolean',
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    public function isTenantAdmin(): bool
    {
        return $this->role === 'tenant_admin';
    }

    public function canManageProjects(): bool
    {
        return in_array($this->role, ['tenant_admin', 'project_manager']);
    }
}
```

```php
<?php
// app/Models/Project.php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'name', 'slug', 'description', 'visibility',
        'requires_approval', 'total_amount', 'minimum_contribution',
        'payment_options', 'installment_frequency', 'custom_installment_months',
        'start_date', 'end_date', 'registration_deadline',
        'created_by', 'managed_by', 'status', 'settings'
    ];

    protected $casts = [
        'payment_options' => 'array',
        'managed_by' => 'array',
        'settings' => 'array',
        'requires_approval' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'registration_deadline' => 'date',
        'total_amount' => 'decimal:2',
        'minimum_contribution' => 'decimal:2',
    ];

    public function isPublic(): bool
    {
        return $this->visibility === 'public';
    }

    public function acceptsInstallments(): bool
    {
        return in_array('installments', $this->payment_options ?? []);
    }

    public function calculateArrears(Carbon $joinDate): float
    {
        if ($joinDate->lte($this->start_date)) {
            return 0;
        }

        $monthsElapsed = $this->start_date->diffInMonths($joinDate);
        $monthlyAmount = $this->getMonthlyAmount();

        return $monthsElapsed * $monthlyAmount;
    }

    public function getMonthlyAmount(): float
    {
        $totalMonths = $this->start_date->diffInMonths($this->end_date);
        return $totalMonths > 0 ? $this->total_amount / $totalMonths : $this->total_amount;
    }
}
```

---

## Flexible Payment System

### Payment Calculation Service

```php
<?php
// app/Services/PaymentCalculationService.php

namespace App\Services;

use App\Models\Project;
use Carbon\Carbon;

class PaymentCalculationService
{
    public function calculateContribution(
        Project $project,
        string $paymentType,
        Carbon $joinDate,
        ?int $customInstallments = null
    ): array {
        $totalAmount = $project->total_amount;
        $arrears = $project->calculateArrears($joinDate);

        if ($paymentType === 'full') {
            return [
                'total_committed' => $totalAmount,
                'payment_type' => 'full',
                'arrears_amount' => $arrears,
                'immediate_payment' => $totalAmount + $arrears,
                'installment_amount' => null,
                'total_installments' => null,
            ];
        }

        // Calculate installments
        $remainingMonths = $joinDate->diffInMonths($project->end_date);
        $installments = $customInstallments ?? $remainingMonths;

        if ($installments <= 0) {
            throw new \InvalidArgumentException('Invalid installment period');
        }

        $installmentAmount = $totalAmount / $installments;
        $immediatePayment = $arrears + $installmentAmount; // First installment + arrears

        return [
            'total_committed' => $totalAmount,
            'payment_type' => 'installments',
            'arrears_amount' => $arrears,
            'immediate_payment' => $immediatePayment,
            'installment_amount' => $installmentAmount,
            'total_installments' => $installments,
            'installment_frequency' => $project->installment_frequency,
        ];
    }

    public function getNextPaymentDate(Contribution $contribution): ?Carbon
    {
        if ($contribution->payment_type === 'full') {
            return null; // No recurring payments for full payment
        }

        $lastPayment = $contribution->transactions()
            ->where('status', 'success')
            ->latest('processed_at')
            ->first();

        $baseDate = $lastPayment
            ? Carbon::parse($lastPayment->processed_at)
            : Carbon::parse($contribution->joined_date);

        return match ($contribution->installment_frequency) {
            'monthly' => $baseDate->addMonth(),
            'quarterly' => $baseDate->addMonths(3),
            'custom' => $baseDate->addMonths($contribution->project->custom_installment_months ?? 1),
            default => $baseDate->addMonth(),
        };
    }
}
```

---

## Multi-Tenant Routing

### Route Structure

```php
<?php
// routes/web.php

use App\Http\Controllers\Tenant;

// Path-based tenant routes: sannu-sannu.com/{tenant-slug}/...
Route::prefix('{tenant}')->middleware(['tenant'])->group(function () {
    // Tenant dashboard
    Route::get('/', [Tenant\DashboardController::class, 'index'])->name('tenant.dashboard');

    // Projects
    Route::resource('projects', Tenant\ProjectController::class);
    Route::post('projects/{project}/join', [Tenant\ContributionController::class, 'store'])
        ->name('projects.join');

    // Public project browsing (no auth required)
    Route::get('browse', [Tenant\PublicController::class, 'projects'])->name('public.projects');
    Route::get('browse/{project}', [Tenant\PublicController::class, 'show'])->name('public.project');

    // User management (tenant admin only)
    Route::middleware(['auth', 'tenant.admin'])->group(function () {
        Route::resource('users', Tenant\UserController::class);
        Route::resource('invitations', Tenant\InvitationController::class);
    });

    // Contributions and payments
    Route::middleware(['auth'])->group(function () {
        Route::get('my-contributions', [Tenant\ContributionController::class, 'index'])
            ->name('contributions.index');
        Route::resource('payments', Tenant\PaymentController::class);
    });
});

// Example URLs:
// sannu-sannu.com/acme-corp/
// sannu-sannu.com/acme-corp/projects
// sannu-sannu.com/acme-corp/projects/summer-campaign
// sannu-sannu.com/tech-startup/browse
```

This multi-tenant architecture provides:

1. **Complete Data Isolation**: Each tenant's data is completely separated
2. **Flexible Payment Options**: Full payment or customizable installments
3. **Public/Private Projects**: Granular access control
4. **Arrears Calculation**: Automatic catch-up payments for late joiners
5. **Scalable Architecture**: Single database with tenant scoping
6. **Subscription Management**: Built-in billing and plan management
7. **White-Label Support**: Customizable branding per tenant

The system is designed to scale efficiently while maintaining security and data isolation between tenants.
