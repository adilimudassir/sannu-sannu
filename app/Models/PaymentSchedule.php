<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentSchedule extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'contribution_id',
        'amount',
        'due_date',
        'status',
        'transaction_id',
        'paid_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_at' => 'datetime',
    ];

    public function contribution()
    {
        return $this->belongsTo(Contribution::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
