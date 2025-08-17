<?php

use App\Enums\Role;
use App\Enums\TenantApplicationStatus;
use App\Models\TenantApplication;
use App\Models\User;

describe('Enhanced Registration Flow', function () {
    it('displays registration type selection page', function () {
        $response = $this->get(route('register'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('auth/register')
        );
    });

    it('allows contributor registration with immediate access', function () {
        $userData = [
            'name' => 'John Contributor',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post(route('register.store'), $userData);

        $response->assertRedirect(route('verification.notice'));

        $this->assertDatabaseHas('users', [
            'name' => 'John Contributor',
            'email' => 'john@example.com',
            'role' => Role::CONTRIBUTOR->value,
        ]);

        $user = User::where('email', 'john@example.com')->first();
        $this->assertAuthenticatedAs($user);
    });

    it('redirects to tenant application form when organization type is selected', function () {
        $response = $this->get(route('tenant-application.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('auth/tenant-application')
            ->has('industryTypes')
        );
    });

    it('validates tenant application form data', function () {
        $response = $this->post(route('tenant-application.store'), []);

        $response->assertSessionHasErrors([
            'organization_name',
            'business_description',
            'industry_type',
            'contact_person_name',
            'contact_person_email',
        ]);
    });

    it('creates tenant application with valid data', function () {
        $applicationData = [
            'organization_name' => 'Test Organization',
            'business_description' => 'This is a detailed business description that is more than 50 characters long to meet the validation requirements.',
            'industry_type' => 'technology',
            'contact_person_name' => 'Jane Smith',
            'contact_person_email' => 'jane@testorg.com',
            'contact_person_phone' => '+1234567890',
            'business_registration_number' => 'REG123456',
            'website_url' => 'https://testorg.com',
        ];

        $response = $this->post(route('tenant-application.store'), $applicationData);

        $application = TenantApplication::where('organization_name', 'Test Organization')->first();

        $response->assertRedirect(route('tenant-application.status', $application->reference_number));

        $this->assertDatabaseHas('tenant_applications', [
            'organization_name' => 'Test Organization',
            'business_description' => $applicationData['business_description'],
            'industry_type' => 'technology',
            'contact_person_name' => 'Jane Smith',
            'contact_person_email' => 'jane@testorg.com',
            'status' => TenantApplicationStatus::PENDING->value,
        ]);

        expect($application->reference_number)->toStartWith('TA-');
        expect($application->submitted_at)->not->toBeNull();
    });

    it('prevents duplicate organization names', function () {
        TenantApplication::factory()->create([
            'organization_name' => 'Existing Organization',
        ]);

        $applicationData = [
            'organization_name' => 'Existing Organization',
            'business_description' => 'This is a detailed business description that is more than 50 characters long.',
            'industry_type' => 'technology',
            'contact_person_name' => 'Jane Smith',
            'contact_person_email' => 'jane@testorg.com',
        ];

        $response = $this->post(route('tenant-application.store'), $applicationData);

        $response->assertSessionHasErrors(['organization_name']);
    });

    it('validates business description minimum length', function () {
        $applicationData = [
            'organization_name' => 'Test Organization',
            'business_description' => 'Too short', // Less than 50 characters
            'industry_type' => 'technology',
            'contact_person_name' => 'Jane Smith',
            'contact_person_email' => 'jane@testorg.com',
        ];

        $response = $this->post(route('tenant-application.store'), $applicationData);

        $response->assertSessionHasErrors(['business_description']);
    });

    it('validates industry type selection', function () {
        $applicationData = [
            'organization_name' => 'Test Organization',
            'business_description' => 'This is a detailed business description that is more than 50 characters long.',
            'industry_type' => 'invalid_industry',
            'contact_person_name' => 'Jane Smith',
            'contact_person_email' => 'jane@testorg.com',
        ];

        $response = $this->post(route('tenant-application.store'), $applicationData);

        $response->assertSessionHasErrors(['industry_type']);
    });

    it('validates email format', function () {
        $applicationData = [
            'organization_name' => 'Test Organization',
            'business_description' => 'This is a detailed business description that is more than 50 characters long.',
            'industry_type' => 'technology',
            'contact_person_name' => 'Jane Smith',
            'contact_person_email' => 'invalid-email',
        ];

        $response = $this->post(route('tenant-application.store'), $applicationData);

        $response->assertSessionHasErrors(['contact_person_email']);
    });

    it('validates phone number format', function () {
        $applicationData = [
            'organization_name' => 'Test Organization',
            'business_description' => 'This is a detailed business description that is more than 50 characters long.',
            'industry_type' => 'technology',
            'contact_person_name' => 'Jane Smith',
            'contact_person_email' => 'jane@testorg.com',
            'contact_person_phone' => 'invalid-phone-format',
        ];

        $response = $this->post(route('tenant-application.store'), $applicationData);

        $response->assertSessionHasErrors(['contact_person_phone']);
    });

    it('validates website URL format', function () {
        $applicationData = [
            'organization_name' => 'Test Organization',
            'business_description' => 'This is a detailed business description that is more than 50 characters long.',
            'industry_type' => 'technology',
            'contact_person_name' => 'Jane Smith',
            'contact_person_email' => 'jane@testorg.com',
            'website_url' => 'not-a-valid-url',
        ];

        $response = $this->post(route('tenant-application.store'), $applicationData);

        $response->assertSessionHasErrors(['website_url']);
    });

    it('displays application status page', function () {
        $application = TenantApplication::factory()->create([
            'status' => TenantApplicationStatus::PENDING,
        ]);

        $response = $this->get(route('tenant-application.status', $application->reference_number));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('auth/tenant-application-status')
            ->has('application')
            ->where('application.reference_number', $application->reference_number)
            ->where('application.status', 'pending')
        );
    });

    it('handles non-existent application reference gracefully', function () {
        $response = $this->get(route('tenant-application.status', 'NON-EXISTENT'));

        $response->assertNotFound();
    });

    it('sanitizes input data properly', function () {
        $applicationData = [
            'organization_name' => '  Test Organization  ',
            'business_description' => '  This is a detailed business description that is more than 50 characters long.  ',
            'industry_type' => 'technology',
            'contact_person_name' => '  Jane Smith  ',
            'contact_person_email' => '  JANE@TESTORG.COM  ',
        ];

        $response = $this->post(route('tenant-application.store'), $applicationData);

        $application = TenantApplication::where('organization_name', 'Test Organization')->first();

        expect($application->organization_name)->toBe('Test Organization');
        expect($application->contact_person_name)->toBe('Jane Smith');
        expect($application->contact_person_email)->toBe('jane@testorg.com');
    });
});
