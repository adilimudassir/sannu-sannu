<?php

namespace App\Models\Concerns;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant()
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (app()->bound('tenant') && app('tenant')) {
                $builder->where(static::getTenantForeignKeyName(), app('tenant')->id);
            }
        });

        static::creating(function (Model $model) {
            if (app()->bound('tenant') && app('tenant')) {
                $model->{static::getTenantForeignKeyName()} = app('tenant')->id;
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, self::getTenantForeignKeyName());
    }

    public static function getTenantForeignKeyName()
    {
        return 'tenant_id';
    }

    /**
     * Check if the model belongs to the current tenant
     */
    public function belongsToCurrentTenant(): bool
    {
        if (!app()->bound('tenant')) {
            return false;
        }

        return $this->{self::getTenantForeignKeyName()} === app('tenant')->id;
    }

    /**
     * Check if the model belongs to a specific tenant
     */
    public function belongsToTenant($tenantId): bool
    {
        return $this->{self::getTenantForeignKeyName()} === $tenantId;
    }

    /**
     * Scope query to exclude tenant scoping (use with caution)
     */
    public function scopeWithoutTenantScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('tenant');
    }
}
