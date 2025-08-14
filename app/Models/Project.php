<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use App\Enums\ProjectVisibility;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Project extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'description',
        'visibility',
        'requires_approval',
        'max_contributors',
        'total_amount',
        'minimum_contribution',
        'payment_options',
        'installment_frequency',
        'custom_installment_months',
        'start_date',
        'end_date',
        'registration_deadline',
        'created_by',
        'managed_by',
        'status',
        'settings',
    ];

    protected $casts = [
        'status' => ProjectStatus::class,
        'visibility' => ProjectVisibility::class,
        'payment_options' => 'array',
        'managed_by' => 'array',
        'settings' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'registration_deadline' => 'date',
        'requires_approval' => 'boolean',
        'total_amount' => 'decimal:2',
        'minimum_contribution' => 'decimal:2',
        'max_contributors' => 'integer',
    ];

    /**
     * Get the tenant that owns the project
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the user who created the project
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all products associated with this project
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class)->orderBy('sort_order');
    }

    /**
     * Get all contributions for this project
     */
    public function contributions(): HasMany
    {
        return $this->hasMany(Contribution::class);
    }

    /**
     * Get all invitations for this project
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(ProjectInvitation::class);
    }

    /**
     * Get all platform fees associated with this project
     */
    public function platformFees(): HasMany
    {
        return $this->hasMany(PlatformFee::class);
    }

    /**
     * Get all transactions through contributions
     */
    public function transactions(): HasManyThrough
    {
        return $this->hasManyThrough(Transaction::class, Contribution::class);
    }

    /**
     * Check if the project is in draft status
     */
    public function isDraft(): bool
    {
        return $this->status === ProjectStatus::DRAFT;
    }

    /**
     * Check if the project is active
     */
    public function isActive(): bool
    {
        return $this->status === ProjectStatus::ACTIVE;
    }

    /**
     * Check if the project is paused
     */
    public function isPaused(): bool
    {
        return $this->status === ProjectStatus::PAUSED;
    }

    /**
     * Check if the project is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === ProjectStatus::COMPLETED;
    }

    /**
     * Check if the project is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === ProjectStatus::CANCELLED;
    }

    /**
     * Check if the project accepts contributions
     */
    public function acceptsContributions(): bool
    {
        return $this->status->acceptsContributions();
    }

    /**
     * Check if the project is publicly discoverable
     */
    public function isPubliclyDiscoverable(): bool
    {
        return $this->visibility->isPubliclyDiscoverable();
    }

    /**
     * Check if the project has restricted access
     */
    public function hasRestrictedAccess(): bool
    {
        return $this->visibility->hasRestrictedAccess();
    }

    /**
     * Check if a user can manage this project
     */
    public function canBeManaged(User $user): bool
    {
        // Creator can always manage
        if ($this->created_by === $user->id) {
            return true;
        }

        // System admins can manage any project
        if ($user->isSystemAdmin()) {
            return true;
        }

        // Check if user is in managed_by array
        if (is_array($this->managed_by) && in_array($user->id, $this->managed_by)) {
            return true;
        }

        // Tenant admins can manage projects in their tenant
        if ($user->isTenantAdmin($this->tenant)) {
            return true;
        }

        return false;
    }

    /**
     * Calculate the total amount from all products
     */
    public function calculateTotalAmount(): float
    {
        return $this->products()->sum('price');
    }

    /**
     * Get project statistics
     */
    public function getStatistics(): array
    {
        $totalContributions = $this->contributions()->count();
        $totalRaised = $this->contributions()->sum('total_paid');
        $completionPercentage = $this->total_amount > 0
            ? min(100, ($totalRaised / $this->total_amount) * 100)
            : 0;

        $daysRemaining = $this->end_date ?
            max(0, now()->diffInDays($this->end_date->toDateString(), false)) :
            null;

        $averageContribution = $totalContributions > 0 ?
            $totalRaised / $totalContributions :
            0;

        return [
            'total_contributors' => $totalContributions,
            'total_raised' => $totalRaised,
            'completion_percentage' => round($completionPercentage, 2),
            'days_remaining' => round($daysRemaining, 0),
            'average_contribution' => round($averageContribution, 2),
        ];
    }

    /**
     * Scope to filter by status
     */
    public function scopeWithStatus($query, ProjectStatus $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by visibility
     */
    public function scopeWithVisibility($query, ProjectVisibility $visibility)
    {
        return $query->where('visibility', $visibility);
    }

    /**
     * Scope to get publicly discoverable projects
     */
    public function scopePubliclyDiscoverable($query)
    {
        return $query->where('visibility', ProjectVisibility::PUBLIC)
            ->where('status', ProjectStatus::ACTIVE);
    }

    /**
     * Scope to get active projects
     */
    public function scopeActive($query)
    {
        return $query->where('status', ProjectStatus::ACTIVE);
    }

    /**
     * Scope to search projects by name or description
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Get the route key for the model.
     * Uses slug for public routes, id for authenticated routes
     */
    public function getRouteKeyName(): string
    {
        // For public routes, use slug
        if (request()->is('projects/*') && ! request()->is('*/projects/*')) {
            return 'slug';
        }

        // For tenant and admin routes, use id
        return 'id';
    }

    /**
     * Retrieve the model for a bound value.
     * Handles both slug and id based routing
     */
    public function resolveRouteBinding($value, $field = null)
    {
        // If field is explicitly specified, use it
        if ($field) {
            return $this->where($field, $value)->first();
        }

        // For public routes using slug
        if (request()->is('projects/*') && ! request()->is('*/projects/*')) {
            return $this->where('slug', $value)
                ->where('visibility', ProjectVisibility::PUBLIC)
                ->where('status', ProjectStatus::ACTIVE)
                ->first();
        }

        // For tenant routes, scope to current tenant
        if (request()->route('tenant')) {
            $tenantParam = request()->route('tenant');

            // If tenant parameter is a string (slug), find the tenant
            if (is_string($tenantParam)) {
                $tenant = \App\Models\Tenant::where('slug', $tenantParam)->first();
                if ($tenant) {
                    return $this->where('id', $value)
                        ->where('tenant_id', $tenant->id)
                        ->first();
                }
            } elseif ($tenantParam instanceof \App\Models\Tenant) {
                // If tenant parameter is already a model
                return $this->where('id', $value)
                    ->where('tenant_id', $tenantParam->id)
                    ->first();
            }
        }

        // For admin routes, no scoping needed (system admin can access all)
        return $this->where('id', $value)->first();
    }
}
