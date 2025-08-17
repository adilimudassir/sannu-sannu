<?php

use App\Enums\Role;
use App\Enums\TenantApplicationStatus;
use App\Mail\SystemAdminNotificationMail;
use App\Mail\TenantApplicationApprovalMail;
use App\Mail\TenantApplicationConfirmationMail;
use App\Mail\TenantApplicationRejectionMail;
use App\Models\TenantApplication;
use App\Models\User;
use App\Services\TenantApplicationService;
use Illuminate\Support\Facades\Mail;

describe('Tenant Application Email Notifications', function () {

    beforeEach(function () {
        Mail::fake();
    });

    it('sends confirmation email when application is submitted', function () {
        $applicationData = [
            'organization_name' => 'Test Organization',
            'business_description' => 'This is a detailed business description that is more than 50 characters long.',
            'industry_type' => 'technology',
            'contact_person_name' => 'Jane Smith',
            'contact_person_email' => 'jane@testorg.com',
        ];

        $response = $this->post(route('tenant-application.store'), $applicationData);

        $application = TenantApplication::where('organization_name', 'Test Organization')->first();
        $response->assertRedirect(route('tenant-application.status', $application->reference_number));

        Mail::assertQueued(TenantApplicationConfirmationMail::class);
    });

    it('notifies system administrators when application is submitted', function () {
        $applicationData = [
            'organization_name' => 'Test Organization',
            'business_description' => 'This is a detailed business description that is more than 50 characters long.',
            'industry_type' => 'technology',
            'contact_person_name' => 'Jane Smith',
            'contact_person_email' => 'jane@testorg.com',
        ];

        $response = $this->post(route('tenant-application.store'), $applicationData);

        $application = TenantApplication::where('organization_name', 'Test Organization')->first();
        $response->assertRedirect(route('tenant-application.status', $application->reference_number));

        Mail::assertQueued(SystemAdminNotificationMail::class);
    });

    it('sends approval email when application is approved', function () {
        $application = TenantApplication::factory()->create([
            'status' => TenantApplicationStatus::PENDING,
        ]);

        $systemAdmin = User::factory()->create([
            'role' => Role::SYSTEM_ADMIN,
        ]);

        $service = app(TenantApplicationService::class);
        $tenant = $service->approveApplication($application, $systemAdmin);

        expect($tenant)->not->toBeNull();
        expect($application->fresh()->status)->toBe(TenantApplicationStatus::APPROVED);

        Mail::assertQueued(TenantApplicationApprovalMail::class);
    });

    it('sends rejection email when application is rejected', function () {
        $application = TenantApplication::factory()->create([
            'status' => TenantApplicationStatus::PENDING,
        ]);

        $systemAdmin = User::factory()->create([
            'role' => Role::SYSTEM_ADMIN,
        ]);

        $service = app(TenantApplicationService::class);
        $service->rejectApplication($application, 'Insufficient business information provided.', $systemAdmin);

        expect($application->fresh()->status)->toBe(TenantApplicationStatus::REJECTED);
        expect($application->fresh()->rejection_reason)->toBe('Insufficient business information provided.');

        Mail::assertQueued(TenantApplicationRejectionMail::class);
    });

    it('includes correct data in confirmation email', function () {
        $applicationData = [
            'organization_name' => 'Test Organization',
            'business_description' => 'This is a detailed business description that is more than 50 characters long.',
            'industry_type' => 'technology',
            'contact_person_name' => 'Jane Smith',
            'contact_person_email' => 'jane@testorg.com',
        ];

        $this->post(route('tenant-application.store'), $applicationData);

        $application = TenantApplication::where('organization_name', 'Test Organization')->first();

        Mail::assertQueued(TenantApplicationConfirmationMail::class, function ($mail) use ($application) {
            return $mail->application->id === $application->id;
        });
    });

    it('includes correct data in admin notification', function () {
        $applicationData = [
            'organization_name' => 'Test Organization',
            'business_description' => 'This is a detailed business description that is more than 50 characters long.',
            'industry_type' => 'technology',
            'contact_person_name' => 'Jane Smith',
            'contact_person_email' => 'jane@testorg.com',
        ];

        $this->post(route('tenant-application.store'), $applicationData);

        $application = TenantApplication::where('organization_name', 'Test Organization')->first();

        Mail::assertQueued(SystemAdminNotificationMail::class, function ($mail) use ($application) {
            return $mail->application->id === $application->id;
        });
    });

    it('includes correct data in approval email', function () {
        $application = TenantApplication::factory()->create([
            'status' => TenantApplicationStatus::PENDING,
            'organization_name' => 'Test Organization',
        ]);

        $systemAdmin = User::factory()->create([
            'role' => Role::SYSTEM_ADMIN,
        ]);

        $service = app(TenantApplicationService::class);
        $tenant = $service->approveApplication($application, $systemAdmin);

        Mail::assertQueued(TenantApplicationApprovalMail::class, function ($mail) use ($application, $tenant) {
            return $mail->application->id === $application->id &&
                   $mail->tenant->id === $tenant->id;
        });
    });

    it('includes correct data in rejection email', function () {
        $application = TenantApplication::factory()->create([
            'status' => TenantApplicationStatus::PENDING,
            'contact_person_email' => 'jane@testorg.com',
        ]);

        $systemAdmin = User::factory()->create([
            'role' => Role::SYSTEM_ADMIN,
        ]);

        $rejectionReason = 'Insufficient business information provided.';

        $service = app(TenantApplicationService::class);
        $service->rejectApplication($application, $rejectionReason, $systemAdmin);

        Mail::assertQueued(TenantApplicationRejectionMail::class, function ($mail) use ($application) {
            return $mail->application->id === $application->id;
        });
    });
});
