<?php

namespace App\Services;

use App\Enums\Role;
use App\Enums\TenantApplicationStatus;
use App\Enums\TenantStatus;
use App\Mail\SystemAdminNotificationMail;
use App\Mail\TenantApplicationApprovalMail;
use App\Mail\TenantApplicationConfirmationMail;
use App\Mail\TenantApplicationRejectionMail;
use App\Models\Tenant;
use App\Models\TenantApplication;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TenantApplicationService
{
    /**
     * Create a new tenant application.
     */
    public function createApplication(array $data): TenantApplication
    {
        return DB::transaction(function () use ($data) {
            $application = TenantApplication::create([
                'reference_number' => $this->generateReferenceNumber(),
                'organization_name' => $data['organization_name'],
                'business_description' => $data['business_description'],
                'industry_type' => $data['industry_type'],
                'contact_person_name' => $data['contact_person_name'],
                'contact_person_email' => $data['contact_person_email'],
                'contact_person_phone' => $data['contact_person_phone'] ?? null,
                'business_registration_number' => $data['business_registration_number'] ?? null,
                'website_url' => $data['website_url'] ?? null,
                'status' => TenantApplicationStatus::PENDING,
                'submitted_at' => now(),
            ]);

            // Send confirmation email to applicant
            $this->sendApplicationConfirmationEmail($application);

            // Notify system administrators
            $this->notifySystemAdministrators($application);

            return $application;
        });
    }

    /**
     * Approve a tenant application and create the tenant.
     */
    public function approveApplication(TenantApplication $application, User $approver, ?string $notes = null): Tenant
    {
        if (! $application->canBeReviewed()) {
            throw new \InvalidArgumentException('Application cannot be reviewed in its current state.');
        }

        return DB::transaction(function () use ($application, $approver, $notes) {
            // Update application status
            $application->update([
                'status' => TenantApplicationStatus::APPROVED,
                'reviewed_at' => now(),
                'reviewer_id' => $approver->id,
                'notes' => $notes,
            ]);

            // Create tenant
            $tenant = Tenant::create([
                'slug' => $this->generateTenantSlug($application->organization_name),
                'name' => $application->organization_name,
                'contact_name' => $application->contact_person_name,
                'contact_email' => $application->contact_person_email,
                'contact_phone' => $application->contact_person_phone,
                'status' => TenantStatus::ACTIVE,
                'is_active' => true,
                'application_id' => $application->id,
                'platform_fee_percentage' => config('app.default_platform_fee_percentage', 5.0),
            ]);

            // Create or find the contact person as a user
            $user = User::firstOrCreate(
                ['email' => $application->contact_person_email],
                [
                    'name' => $application->contact_person_name,
                    'password' => Hash::make(Str::random(32)), // Temporary password
                    'role' => Role::CONTRIBUTOR,
                    'email_verified_at' => now(), // Auto-verify for approved organizations
                ]
            );

            // Assign tenant admin role
            $tenant->userTenantRoles()->create([
                'user_id' => $user->id,
                'role' => 'tenant_admin',
                'is_active' => true,
            ]);

            // Generate temporary password for the user
            $temporaryPassword = Str::random(12);
            $user->update(['password' => Hash::make($temporaryPassword)]);

            // Send approval email with login instructions
            $this->sendApprovalEmail($application, $tenant, $user, $temporaryPassword);

            // Log the approval
            app(AuditLogService::class)->log(
                'tenant_application_approved',
                $application,
                $approver,
                ['tenant_id' => $tenant->id]
            );

            return $tenant;
        });
    }

    /**
     * Reject a tenant application.
     */
    public function rejectApplication(TenantApplication $application, string $reason, User $rejector, ?string $notes = null): void
    {
        if (! $application->canBeReviewed()) {
            throw new \InvalidArgumentException('Application cannot be reviewed in its current state.');
        }

        DB::transaction(function () use ($application, $reason, $rejector, $notes) {
            $application->update([
                'status' => TenantApplicationStatus::REJECTED,
                'reviewed_at' => now(),
                'reviewer_id' => $rejector->id,
                'rejection_reason' => $reason,
                'notes' => $notes,
            ]);

            // Send rejection email
            $this->sendRejectionEmail($application);

            // Log the rejection
            app(AuditLogService::class)->log(
                'tenant_application_rejected',
                $application,
                $rejector,
                ['reason' => $reason, 'notes' => $notes]
            );
        });
    }

    /**
     * Generate a unique reference number for the application.
     */
    public function generateReferenceNumber(): string
    {
        $maxAttempts = 10;
        $attempts = 0;

        do {
            $attempts++;

            // Format: TA-YYYYMMDD-HHMMSS-XXXX (where XXXX is random alphanumeric)
            $timestamp = now();
            $dateStr = $timestamp->format('Ymd');
            $timeStr = $timestamp->format('His');
            $randomStr = strtoupper(Str::random(4));

            $referenceNumber = "TA-{$dateStr}-{$timeStr}-{$randomStr}";

            if ($attempts >= $maxAttempts) {
                // Fallback to UUID-based reference if we can't generate unique one
                $referenceNumber = 'TA-'.strtoupper(str_replace('-', '', Str::uuid()));
                break;
            }
        } while (TenantApplication::where('reference_number', $referenceNumber)->exists());

        return $referenceNumber;
    }

    /**
     * Generate a unique tenant slug.
     */
    private function generateTenantSlug(string $organizationName): string
    {
        $baseSlug = Str::slug($organizationName);
        $slug = $baseSlug;
        $counter = 1;

        while (Tenant::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    /**
     * Send confirmation email to the applicant.
     */
    private function sendApplicationConfirmationEmail(TenantApplication $application): void
    {
        try {
            Mail::send(new TenantApplicationConfirmationMail($application));

            Log::info('Tenant application confirmation email sent', [
                'reference_number' => $application->reference_number,
                'email' => $application->contact_person_email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send tenant application confirmation email', [
                'reference_number' => $application->reference_number,
                'email' => $application->contact_person_email,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify system administrators about new application.
     */
    private function notifySystemAdministrators(TenantApplication $application): void
    {
        try {
            Mail::send(new SystemAdminNotificationMail($application));

            Log::info('System administrators notified of new tenant application', [
                'reference_number' => $application->reference_number,
                'organization' => $application->organization_name,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to notify system administrators of new tenant application', [
                'reference_number' => $application->reference_number,
                'organization' => $application->organization_name,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send approval email to the applicant.
     */
    private function sendApprovalEmail(TenantApplication $application, Tenant $tenant, User $user, string $temporaryPassword): void
    {
        try {
            Mail::send(new TenantApplicationApprovalMail($application, $tenant, $user, $temporaryPassword));

            Log::info('Tenant application approval email sent', [
                'reference_number' => $application->reference_number,
                'tenant_slug' => $tenant->slug,
                'user_email' => $user->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send tenant application approval email', [
                'reference_number' => $application->reference_number,
                'tenant_slug' => $tenant->slug,
                'user_email' => $user->email,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send rejection email to the applicant.
     */
    private function sendRejectionEmail(TenantApplication $application): void
    {
        try {
            Mail::send(new TenantApplicationRejectionMail($application));

            Log::info('Tenant application rejection email sent', [
                'reference_number' => $application->reference_number,
                'email' => $application->contact_person_email,
                'reason' => $application->rejection_reason,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send tenant application rejection email', [
                'reference_number' => $application->reference_number,
                'email' => $application->contact_person_email,
                'reason' => $application->rejection_reason,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
