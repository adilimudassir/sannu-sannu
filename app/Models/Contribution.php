<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contribution extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'project_id',
        'total_committed',
        'payment_type',
        'installment_amount',
        'installment_frequency',
        'total_installments',
        'arrears_amount',
        'arrears_paid',
        'total_paid',
        'next_payment_due',
        'status',
        'joined_date',
        'approval_status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'next_payment_due' => 'date',
        'joined_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function paymentSchedules()
    {
        return $this->hasMany(PaymentSchedule::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
