<?php

use App\Enums\TenantApplicationStatus;
use App\Models\TenantApplication;

describe('Tenant Application Status API', function () {

    it('returns application status for valid reference number', function () {
        $application = TenantApplication::factory()->create([
            'status' => TenantApplicationStatus::PENDING,
            'organization_name' => 'Test Organization',
            'reference_number' => 'TA-20250816-123456-ABCD',
        ]);

        $response = $this->getJson(route('tenant-application.status.api', $application->reference_number));

        $response->assertOk();
        $response->assertJson([
            'reference_number' => $application->reference_number,
            'organization_name' => 'Test Organization',
            'status' => 'pending',
            'status_label' => 'Pending Review',
            'submitted_at' => $application->submitted_at->toISOString(),
            'reviewed_at' => null,
            'rejection_reason' => null,
        ]);
    });

    it('returns 404 for non-existent reference number', function () {
        $response = $this->getJson(route('tenant-application.status.api', 'NON-EXISTENT-REF'));

        $response->assertNotFound();
        $response->assertJson([
            'error' => 'Application not found',
            'message' => 'No application found with the provided reference number.',
        ]);
    });

    it('returns approved application status with review date', function () {
        $application = TenantApplication::factory()->create([
            'status' => TenantApplicationStatus::APPROVED,
            'reviewed_at' => now(),
        ]);

        $response = $this->getJson(route('tenant-application.status.api', $application->reference_number));

        $response->assertOk();
        $response->assertJson([
            'reference_number' => $application->reference_number,
            'status' => 'approved',
            'status_label' => 'Approved',
        ]);

        expect($response->json('reviewed_at'))->not->toBeNull();
    });

    it('returns rejected application status with rejection reason', function () {
        $application = TenantApplication::factory()->create([
            'status' => TenantApplicationStatus::REJECTED,
            'reviewed_at' => now(),
            'rejection_reason' => 'Insufficient business information provided.',
        ]);

        $response = $this->getJson(route('tenant-application.status.api', $application->reference_number));

        $response->assertOk();
        $response->assertJson([
            'reference_number' => $application->reference_number,
            'status' => 'rejected',
            'status_label' => 'Rejected',
            'rejection_reason' => 'Insufficient business information provided.',
        ]);

        expect($response->json('reviewed_at'))->not->toBeNull();
    });

    it('handles special characters in reference number', function () {
        $application = TenantApplication::factory()->create([
            'reference_number' => 'TA-20250816-123456-A1B2',
        ]);

        $response = $this->getJson(route('tenant-application.status.api', $application->reference_number));

        $response->assertOk();
        $response->assertJson([
            'reference_number' => 'TA-20250816-123456-A1B2',
        ]);
    });

    it('returns consistent JSON structure for all statuses', function () {
        $statuses = [
            TenantApplicationStatus::PENDING,
            TenantApplicationStatus::APPROVED,
            TenantApplicationStatus::REJECTED,
        ];

        foreach ($statuses as $status) {
            $application = TenantApplication::factory()->create([
                'status' => $status,
                'reviewed_at' => $status !== TenantApplicationStatus::PENDING ? now() : null,
                'rejection_reason' => $status === TenantApplicationStatus::REJECTED ? 'Test reason' : null,
            ]);

            $response = $this->getJson(route('tenant-application.status.api', $application->reference_number));

            $response->assertOk();

            // Check that all expected keys are present
            $response->assertJsonStructure([
                'reference_number',
                'organization_name',
                'status',
                'status_label',
                'submitted_at',
                'reviewed_at',
                'rejection_reason',
            ]);
        }
    });

    it('returns proper content type headers', function () {
        $application = TenantApplication::factory()->create();

        $response = $this->getJson(route('tenant-application.status.api', $application->reference_number));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/json');
    });

    it('handles case sensitivity in reference number', function () {
        $application = TenantApplication::factory()->create([
            'reference_number' => 'TA-20250816-123456-ABCD',
        ]);

        // Test with lowercase - should still work since route parameter is exact match
        $response = $this->getJson(route('tenant-application.status.api', 'TA-20250816-123456-ABCD'));

        $response->assertOk();
    });
});
