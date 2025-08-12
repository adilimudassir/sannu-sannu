<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserTenantRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tenant_id',
        'role',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'role' => Role::class,
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scope to active roles only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to specific role
     */
    public function scopeWithRole($query, Role $role)
    {
        return $query->where('role', $role);
    }
}