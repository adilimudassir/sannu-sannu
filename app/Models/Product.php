<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'project_id',
        'name',
        'description',
        'price',
        'image_url',
        'sort_order',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
