<?php

namespace App\Models;

use App\Enums\TenantApplicationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TenantApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_number',
        'organization_name',
        'business_description',
        'industry_type',
        'contact_person_name',
        'contact_person_email',
        'contact_person_phone',
        'business_registration_number',
        'website_url',
        'status',
        'submitted_at',
        'reviewed_at',
        'reviewer_id',
        'rejection_reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'status' => TenantApplicationStatus::class,
        ];
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function tenant(): HasOne
    {
        return $this->hasOne(Tenant::class, 'application_id');
    }

    public function isApproved(): bool
    {
        return $this->status === TenantApplicationStatus::APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === TenantApplicationStatus::REJECTED;
    }

    public function isPending(): bool
    {
        return $this->status === TenantApplicationStatus::PENDING;
    }

    public function canBeReviewed(): bool
    {
        return $this->isPending();
    }

    public function generateReferenceNumber(): string
    {
        return 'TA-'.now()->format('Ymd').'-'.str_pad($this->id, 4, '0', STR_PAD_LEFT);
    }
}
