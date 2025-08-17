<?php

use App\Models\TenantApplication;
use App\Services\TenantApplicationService;

describe('Tenant Application Reference Number Generation', function () {

    it('generates unique reference numbers', function () {
        $service = app(TenantApplicationService::class);

        $referenceNumbers = [];

        // Generate multiple reference numbers
        for ($i = 0; $i < 10; $i++) {
            $referenceNumber = $service->generateReferenceNumber();
            $referenceNumbers[] = $referenceNumber;
        }

        // All should be unique
        expect(count($referenceNumbers))->toBe(count(array_unique($referenceNumbers)));

        // All should start with TA-
        foreach ($referenceNumbers as $refNum) {
            expect($refNum)->toStartWith('TA-');
        }
    });

    it('generates reference numbers with correct format', function () {
        $service = app(TenantApplicationService::class);
        $referenceNumber = $service->generateReferenceNumber();

        // Should match pattern: TA-YYYYMMDD-HHMMSS-XXXX
        expect($referenceNumber)->toMatch('/^TA-\d{8}-\d{6}-[A-Z0-9]{4}$/');

        // Extract date part and verify it's today
        $parts = explode('-', $referenceNumber);
        $dateStr = $parts[1];
        $expectedDate = now()->format('Ymd');

        expect($dateStr)->toBe($expectedDate);
    });

    it('avoids duplicate reference numbers in database', function () {
        $service = app(TenantApplicationService::class);

        // Create an application with a specific reference number
        $existingRefNum = 'TA-'.now()->format('Ymd').'-'.now()->format('His').'-TEST';
        TenantApplication::factory()->create([
            'reference_number' => $existingRefNum,
        ]);

        // Generate new reference numbers - they should not match the existing one
        for ($i = 0; $i < 5; $i++) {
            $newRefNum = $service->generateReferenceNumber();
            expect($newRefNum)->not->toBe($existingRefNum);
        }
    });

    it('falls back to UUID-based reference when needed', function () {
        $service = app(TenantApplicationService::class);

        // Mock the scenario where many reference numbers already exist
        // by creating applications with predictable reference numbers
        $baseDate = now()->format('Ymd');
        $baseTime = now()->format('His');

        // Create applications that would conflict with the first few attempts
        for ($i = 0; $i < 15; $i++) {
            TenantApplication::factory()->create([
                'reference_number' => "TA-{$baseDate}-{$baseTime}-".str_pad($i, 4, '0', STR_PAD_LEFT),
            ]);
        }

        // The service should still generate a unique reference number
        $referenceNumber = $service->generateReferenceNumber();

        expect($referenceNumber)->toStartWith('TA-');
        expect(TenantApplication::where('reference_number', $referenceNumber)->exists())->toBeFalse();
    });

    it('includes reference number in created applications', function () {
        $applicationData = [
            'organization_name' => 'Test Organization',
            'business_description' => 'This is a detailed business description that is more than 50 characters long.',
            'industry_type' => 'technology',
            'contact_person_name' => 'Jane Smith',
            'contact_person_email' => 'jane@testorg.com',
        ];

        $response = $this->post(route('tenant-application.store'), $applicationData);

        $application = TenantApplication::where('organization_name', 'Test Organization')->first();

        expect($application->reference_number)->not->toBeNull();
        expect($application->reference_number)->toStartWith('TA-');
        expect($application->reference_number)->toMatch('/^TA-\d{8}-\d{6}-[A-Z0-9]{4}$/');
    });

    it('allows status checking with reference number', function () {
        $application = TenantApplication::factory()->create();

        $response = $this->get(route('tenant-application.status', $application->reference_number));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('auth/tenant-application-status')
            ->where('application.reference_number', $application->reference_number)
        );
    });

    it('handles non-existent reference numbers gracefully', function () {
        $response = $this->get(route('tenant-application.status', 'TA-99999999-999999-XXXX'));

        $response->assertNotFound();
    });

    it('generates reference numbers that are URL-safe', function () {
        $service = app(TenantApplicationService::class);

        for ($i = 0; $i < 10; $i++) {
            $referenceNumber = $service->generateReferenceNumber();

            // Should not contain characters that need URL encoding
            expect($referenceNumber)->not->toContain(' ');
            expect($referenceNumber)->not->toContain('/');
            expect($referenceNumber)->not->toContain('?');
            expect($referenceNumber)->not->toContain('#');
            expect($referenceNumber)->not->toContain('&');

            // Should be safe to use in URLs
            expect(urlencode($referenceNumber))->toBe($referenceNumber);
        }
    });

    it('maintains reference number uniqueness across multiple concurrent requests', function () {
        $service = app(TenantApplicationService::class);
        $referenceNumbers = [];

        // Simulate concurrent reference number generation
        for ($i = 0; $i < 50; $i++) {
            $referenceNumber = $service->generateReferenceNumber();
            $referenceNumbers[] = $referenceNumber;

            // Immediately create an application with this reference number
            // to simulate database constraints
            TenantApplication::factory()->create([
                'reference_number' => $referenceNumber,
                'organization_name' => "Test Org {$i}",
            ]);
        }

        // All reference numbers should be unique
        expect(count($referenceNumbers))->toBe(count(array_unique($referenceNumbers)));
    });
});
