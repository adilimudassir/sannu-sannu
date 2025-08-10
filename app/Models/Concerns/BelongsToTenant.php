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
            if (app()->bound('tenant')) {
                $builder->where(self::getTenantForeignKeyName(), app('tenant')->id);
            }
        });

        static::creating(function (Model $model) {
            if (app()->bound('tenant')) {
                $model->{self::getTenantForeignKeyName()} = app('tenant')->id;
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
}
