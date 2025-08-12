<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
    ];

    protected $casts = [
        'settings' => 'array',
        'trial_ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

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
}
