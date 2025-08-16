<?php

namespace App\Models;

use App\Enums\TenantStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'domain',
        'logo_url',
        'primary_color',
        'secondary_color',
        'platform_fee_percentage',
        'status',
        'trial_ends_at',
        'max_projects',
        'max_users',
        'max_storage_mb',
        'contact_name',
        'contact_email',
        'contact_phone',
        'settings',
        'is_active',
        'application_id',
        'suspended_at',
        'suspended_reason',
        'suspended_by',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'trial_ends_at' => 'datetime',
            'is_active' => 'boolean',
            'status' => TenantStatus::class,
            'suspended_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(TenantApplication::class, 'application_id');
    }

    public function suspendedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'suspended_by');
    }

    public function onboardingProgress(): HasMany
    {
        return $this->hasMany(OnboardingProgress::class);
    }

    /**
     * Users with roles in this tenant (many-to-many through pivot)
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_tenant_roles')
            ->withPivot('role', 'is_active')
            ->withTimestamps()
            ->wherePivot('is_active', true);
    }

    /**
     * User tenant roles for this tenant
     */
    public function userTenantRoles()
    {
        return $this->hasMany(UserTenantRole::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function contributions()
    {
        return $this->hasMany(Contribution::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function paymentSchedules()
    {
        return $this->hasMany(PaymentSchedule::class);
    }

    public function platformFees()
    {
        return $this->hasMany(PlatformFee::class);
    }

    public function isActive(): bool
    {
        return $this->is_active && $this->status === TenantStatus::ACTIVE;
    }

    public function isSuspended(): bool
    {
        return $this->status === TenantStatus::SUSPENDED;
    }

    public function getMetrics(): array
    {
        return [
            'total_users' => $this->users()->count(),
            'total_projects' => $this->projects()->count(),
            'active_projects' => $this->projects()->where('status', 'active')->count(),
            'total_contributions' => $this->contributions()->count(),
            'total_revenue' => $this->contributions()->sum('total_paid'),
        ];
    }
}
