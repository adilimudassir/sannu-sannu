<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Theme extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'type',
        'colors',
        'radius',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'colors' => 'array',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];
}
