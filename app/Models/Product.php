<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Services\ImageService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'project_id',
        'name',
        'description',
        'price',
        'image_url',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    /**
     * Get the tenant that owns the product
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the project this product belongs to
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Check if the product has an image
     */
    public function hasImage(): bool
    {
        return ! empty($this->image_url);
    }

    /**
     * Get the full URL for the product image
     */
    public function getImageUrl(): ?string
    {
        if (! $this->hasImage()) {
            return null;
        }

        return app(ImageService::class)->getImageUrl($this->image_url);
    }

    /**
     * Scope to order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Scope to filter by project
     */
    public function scopeForProject($query, Project $project)
    {
        return $query->where('project_id', $project->id);
    }
}
