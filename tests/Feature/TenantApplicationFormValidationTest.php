<?php

use App\Enums\TenantApplicationStatus;
use App\Models\TenantApplication;

describe('Tenant Application Form Validation', function () {

    it('validates all required fields are present', function () {
        $response = $this->post(route('tenant-application.store'), []);

        $response->assertSessionHasErrors([
            'organization_name',
            'business_description',
            'industry_type',
            'contact_person_name',
            'contact_person_email',
        ]);
    });

    it('validates organization name format and uniqueness', function () {
        // Test invalid characters
        $response = $this->post(route('tenant-application.store'), [
            'organization_name' => 'Test<script>alert("xss")</script>Org',
            'business_description' => 'This is a detailed business description that is more than 50 characters long.',
            'industry_type' => 'technology',
            'contact_person_name' => 'Jane Smith',
            'contact_person_email' => 'jane@testorg.com',
        ]);

        $response->assertSessionHasErrors(['organization_name']);

        // Test uniqueness
        TenantApplication::factory()->create([
            'organization_name' => 'Existing Organization',
        ]);

        $response = $this->post(route('tenant-application.store'), [
            'organization_name' => 'Existing Organization',
            'business_description' => 'This is a detailed business description that is more than 50 characters long.',
            'industry_type' => 'technology',
            'contact_person_name' => 'Jane Smith',
            'contact_person_email' => 'jane@testorg.com',
        ]);

        $response->assertSessionHasErrors(['organization_name']);
    });

    it('validates business description length requirements', function () {
        // Test too short
        $response = $this->post(route('tenant-application.store'), [
            'organization_name' => 'Test Organization',
            'business_description' => 'Too short',
            'industry_type' => 'technology',
            'contact_person_name' => 'Jane Smith',
            'contact_person_email' => 'jane@testorg.com',
        ]);

        $response->assertSessionHasErrors(['business_description']);

        // Test too long
        $longDescription = str_repeat('A', 1001);
        $response = $this->post(route('tenant-application.store'), [
            'organization_name' => 'Test Organization',
            'business_description' => $longDescription,
            'industry_type' => 'technology',
            'contact_person_name' => 'Jane Smith',
            'contact_person_email' => 'jane@testorg.com',
        ]);

        $response->assertSessionHasErrors(['business_description']);
    });

    it('validates industry type is from allowed list', function () {
        $response = $this->post(route('tenant-application.store'), [
            'organization_name' => 'Test Organization',
            'business_description' => 'This is a detailed business description that is more than 50 characters long.',
            'industry_type' => 'invalid_industry_type',
            'contact_person_name' => 'Jane Smith',
            'contact_person_email' => 'jane@testorg.com',
        ]);

        $response->assertSessionHasErrors(['industry_type']);
    });

    it('validates contact person name format', function () {
        $response = $this->post(route('tenant-application.store'), [
            'organization_name' => 'Test Organization',
            'business_description' => 'This is a detailed business description that is more than 50 characters long.',
            'industry_type' => 'technology',
            'contact_person_name' => 'Jane123Smith',
            'contact_person_email' => 'jane@testorg.com',
        ]);

        $response->assertSessionHasErrors(['contact_person_name']);
    });

    it('validates email format', function () {
        $response = $this->post(route('tenant-application.store'), [
            'organization_name' => 'Test Organization',
            'business_description' => 'This is a detailed business description that is more than 50 characters long.',
            'industry_type' => 'technology',
            'contact_person_name' => 'Jane Smith',
            'contact_person_email' => 'invalid-email',
        ]);

        $response->assertSessionHasErrors(['contact_person_email']);

        // Test another invalid email
        $response = $this->post(route('tenant-application.store'), [
            'organization_name' => 'Test Organization 2',
            'business_description' => 'This is a detailed business description that is more than 50 characters long.',
            'industry_type' => 'technology',
            'contact_person_name' => 'Jane Smith',
            'contact_person_email' => 'invalid@',
        ]);

        $response->assertSessionHasErrors(['contact_person_email']);
    });

    it('validates phone number format when provided', function () {
        $response = $this->post(route('tenant-application.store'), [
            'organization_name' => 'Test Organization Phone 1',
            'business_description' => 'This is a detailed business description that is more than 50 characters long.',
            'industry_type' => 'technology',
            'contact_person_name' => 'Jane Smith',
            'contact_person_email' => 'jane1@testorg.com',
            'contact_person_phone' => 'abc123',
        ]);

        $response->assertSessionHasErrors(['contact_person_phone']);

        $response = $this->post(route('tenant-application.store'), [
            'organization_name' => 'Test Organization Phone 2',
            'business_description' => 'This is a detailed business description that is more than 50 characters long.',
            'industry_type' => 'technology',
            'contact_person_name' => 'Jane Smith',
            'contact_person_email' => 'jane2@testorg.com',
            'contact_person_phone' => '++1234567890',
        ]);

        $response->assertSessionHasErrors(['contact_person_phone']);
    });

    it('validates website URL format when provided', function () {
        $response = $this->post(route('tenant-application.store'), [
            'organization_name' => 'Test Organization URL 1',
            'business_description' => 'This is a detailed business description that is more than 50 characters long.',
            'industry_type' => 'technology',
            'contact_person_name' => 'Jane Smith',
            'contact_person_email' => 'jane1@testorg.com',
            'website_url' => 'not-a-url',
        ]);

        $response->assertSessionHasErrors(['website_url']);

        $response = $this->post(route('tenant-application.store'), [
            'organization_name' => 'Test Organization URL 2',
            'business_description' => 'This is a detailed business description that is more than 50 characters long.',
            'industry_type' => 'technology',
            'contact_person_name' => 'Jane Smith',
            'contact_person_email' => 'jane2@testorg.com',
            'website_url' => 'javascript:alert("xss")',
        ]);

        $response->assertSessionHasErrors(['website_url']);
    });

    it('accepts valid phone number formats', function () {
        $validPhones = [
            '+1234567890',
            '(123) 456-7890',
            '123-456-7890',
            '123 456 7890',
            '+1 (123) 456-7890',
        ];

        foreach ($validPhones as $phone) {
            $response = $this->post(route('tenant-application.store'), [
                'organization_name' => 'Test Organization '.uniqid(),
                'business_description' => 'This is a detailed business description that is more than 50 characters long.',
                'industry_type' => 'technology',
                'contact_person_name' => 'Jane Smith',
                'contact_person_email' => 'jane'.uniqid().'@testorg.com',
                'contact_person_phone' => $phone,
            ]);

            $response->assertSessionDoesntHaveErrors(['contact_person_phone']);
        }
    });

    it('accepts valid website URL formats', function () {
        $validUrls = [
            'https://example.com',
            'http://example.com',
            'https://www.example.com',
            'https://subdomain.example.com',
            'https://example.com/path',
        ];

        foreach ($validUrls as $url) {
            $response = $this->post(route('tenant-application.store'), [
                'organization_name' => 'Test Organization '.uniqid(),
                'business_description' => 'This is a detailed business description that is more than 50 characters long.',
                'industry_type' => 'technology',
                'contact_person_name' => 'Jane Smith',
                'contact_person_email' => 'jane'.uniqid().'@testorg.com',
                'website_url' => $url,
            ]);

            $response->assertSessionDoesntHaveErrors(['website_url']);
        }
    });

    it('properly sanitizes input data', function () {
        $response = $this->post(route('tenant-application.store'), [
            'organization_name' => '  Test Organization  ',
            'business_description' => '  This is a detailed business description that is more than 50 characters long.  ',
            'industry_type' => 'technology',
            'contact_person_name' => '  Jane Smith  ',
            'contact_person_email' => '  JANE@TESTORG.COM  ',
            'contact_person_phone' => '  +1234567890  ',
            'business_registration_number' => '  REG123456  ',
            'website_url' => '  https://testorg.com  ',
        ]);

        $application = TenantApplication::where('organization_name', 'Test Organization')->first();

        expect($application)->not->toBeNull();
        expect($application->organization_name)->toBe('Test Organization');
        expect($application->contact_person_name)->toBe('Jane Smith');
        expect($application->contact_person_email)->toBe('jane@testorg.com');
        expect($application->contact_person_phone)->toBe('+1234567890');
        expect($application->business_registration_number)->toBe('REG123456');
        expect($application->website_url)->toBe('https://testorg.com');
    });

    it('creates application with all valid data', function () {
        $applicationData = [
            'organization_name' => 'Test Organization',
            'business_description' => 'This is a detailed business description that is more than 50 characters long and provides comprehensive information about our business.',
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
            'contact_person_phone' => '+1234567890',
            'business_registration_number' => 'REG123456',
            'website_url' => 'https://testorg.com',
            'status' => TenantApplicationStatus::PENDING->value,
        ]);

        expect($application->reference_number)->toStartWith('TA-');
        expect($application->submitted_at)->not->toBeNull();
    });

    it('handles edge cases for optional fields', function () {
        $applicationData = [
            'organization_name' => 'Test Organization',
            'business_description' => 'This is a detailed business description that is more than 50 characters long.',
            'industry_type' => 'technology',
            'contact_person_name' => 'Jane Smith',
            'contact_person_email' => 'jane@testorg.com',
            // Optional fields are null/empty
            'contact_person_phone' => '',
            'business_registration_number' => null,
            'website_url' => '',
        ];

        $response = $this->post(route('tenant-application.store'), $applicationData);

        $application = TenantApplication::where('organization_name', 'Test Organization')->first();

        $response->assertRedirect(route('tenant-application.status', $application->reference_number));

        expect($application->contact_person_phone)->toBeNull();
        expect($application->business_registration_number)->toBeNull();
        expect($application->website_url)->toBeNull();
    });
});
