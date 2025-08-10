<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'description',
        'visibility',
        'requires_approval',
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
        'payment_options' => 'array',
        'managed_by' => 'array',
        'settings' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'registration_deadline' => 'date',
        'requires_approval' => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function contributions()
    {
        return $this->hasMany(Contribution::class);
    }

    public function invitations()
    {
        return $this->hasMany(ProjectInvitation::class);
    }

    public function platformFees()
    {
        return $this->hasMany(PlatformFee::class);
    }
}
