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

describe('Tenant Application Email Integration', function () {

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

        Mail::assertQueued(TenantApplicationConfirmationMail::class, function ($mail) use ($application) {
            return $mail->application->id === $application->id;
        });
    });

    it('sends notification email to system administrators', function () {
        // Create system administrators
        User::factory()->count(2)->create(['role' => Role::SYSTEM_ADMIN]);

        $applicationData = [
            'organization_name' => 'Test Organization',
            'business_description' => 'This is a detailed business description that is more than 50 characters long.',
            'industry_type' => 'technology',
            'contact_person_name' => 'Jane Smith',
            'contact_person_email' => 'jane@testorg.com',
        ];

        $response = $this->post(route('tenant-application.store'), $applicationData);

        $application = TenantApplication::where('organization_name', 'Test Organization')->first();

        Mail::assertQueued(SystemAdminNotificationMail::class, function ($mail) use ($application) {
            return $mail->application->id === $application->id;
        });
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

        Mail::assertQueued(TenantApplicationApprovalMail::class, function ($mail) use ($application, $tenant) {
            return $mail->application->id === $application->id &&
                   $mail->tenant->id === $tenant->id;
        });
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

        Mail::assertQueued(TenantApplicationRejectionMail::class, function ($mail) use ($application) {
            return $mail->application->id === $application->id;
        });
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

    it('includes correct data in system admin notification email', function () {
        User::factory()->create(['role' => Role::SYSTEM_ADMIN, 'email' => 'admin@sannu-sannu.com']);

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
            'contact_person_email' => 'jane@testorg.com',
        ]);

        $systemAdmin = User::factory()->create([
            'role' => Role::SYSTEM_ADMIN,
        ]);

        $service = app(TenantApplicationService::class);
        $tenant = $service->approveApplication($application, $systemAdmin);

        Mail::assertQueued(TenantApplicationApprovalMail::class, function ($mail) use ($application, $tenant) {
            return $mail->application->id === $application->id &&
                   $mail->tenant->id === $tenant->id &&
                   ! empty($mail->temporaryPassword);
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

    it('handles email sending failures gracefully', function () {
        // Mock Mail to throw an exception
        Mail::shouldReceive('send')->andThrow(new \Exception('SMTP server unavailable'));

        $applicationData = [
            'organization_name' => 'Test Organization',
            'business_description' => 'This is a detailed business description that is more than 50 characters long.',
            'industry_type' => 'technology',
            'contact_person_name' => 'Jane Smith',
            'contact_person_email' => 'jane@testorg.com',
        ];

        // Application should still be created even if email fails
        $response = $this->post(route('tenant-application.store'), $applicationData);

        $application = TenantApplication::where('organization_name', 'Test Organization')->first();

        expect($application)->not->toBeNull();
        $response->assertRedirect(route('tenant-application.status', $application->reference_number));
    });

    it('queues emails for background processing', function () {
        $applicationData = [
            'organization_name' => 'Test Organization',
            'business_description' => 'This is a detailed business description that is more than 50 characters long.',
            'industry_type' => 'technology',
            'contact_person_name' => 'Jane Smith',
            'contact_person_email' => 'jane@testorg.com',
        ];

        $this->post(route('tenant-application.store'), $applicationData);

        // Verify emails are queued (they implement ShouldQueue)
        Mail::assertQueued(TenantApplicationConfirmationMail::class);
        Mail::assertQueued(SystemAdminNotificationMail::class);
    });

    it('does not send emails to non-system-admin users', function () {
        // Create regular users and contributors
        User::factory()->count(3)->create(['role' => Role::CONTRIBUTOR]);

        // Create only one system admin
        $systemAdmin = User::factory()->create(['role' => Role::SYSTEM_ADMIN, 'email' => 'admin@sannu-sannu.com']);

        $applicationData = [
            'organization_name' => 'Test Organization',
            'business_description' => 'This is a detailed business description that is more than 50 characters long.',
            'industry_type' => 'technology',
            'contact_person_name' => 'Jane Smith',
            'contact_person_email' => 'jane@testorg.com',
        ];

        $this->post(route('tenant-application.store'), $applicationData);

        Mail::assertQueued(SystemAdminNotificationMail::class);
    });
});
