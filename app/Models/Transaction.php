<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'contribution_id',
        'user_id',
        'paystack_reference',
        'amount',
        'type',
        'status',
        'paystack_response',
        'failure_reason',
        'processed_at',
    ];

    protected $casts = [
        'paystack_response' => 'array',
        'processed_at' => 'datetime',
    ];

    public function contribution()
    {
        return $this->belongsTo(Contribution::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function platformFee()
    {
        return $this->hasOne(PlatformFee::class);
    }
}
