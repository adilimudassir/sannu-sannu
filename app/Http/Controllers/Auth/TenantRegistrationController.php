<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantApplicationRequest;
use App\Models\TenantApplication;
use App\Services\TenantApplicationService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TenantRegistrationController extends Controller
{
    public function __construct(
        private TenantApplicationService $tenantApplicationService
    ) {}

    /**
     * Show the tenant application form.
     */
    public function create(): Response
    {
        return Inertia::render('auth/tenant-application', [
            'industryTypes' => $this->getIndustryTypes(),
        ]);
    }

    /**
     * Store a new tenant application.
     */
    public function store(TenantApplicationRequest $request): RedirectResponse
    {
        $application = $this->tenantApplicationService->createApplication(
            $request->validated()
        );

        return redirect()->route('tenant-application.status', $application->reference_number)
            ->with('success', 'Your organization application has been submitted successfully. You will receive a confirmation email shortly.');
    }

    /**
     * Show the application status page.
     */
    public function showStatus(string $referenceNumber): Response
    {
        $application = TenantApplication::where('reference_number', $referenceNumber)
            ->firstOrFail();

        return Inertia::render('auth/tenant-application-status', [
            'application' => [
                'reference_number' => $application->reference_number,
                'organization_name' => $application->organization_name,
                'status' => $application->status->value,
                'submitted_at' => $application->submitted_at->toISOString(),
                'reviewed_at' => $application->reviewed_at?->toISOString(),
                'rejection_reason' => $application->rejection_reason,
            ],
        ]);
    }

    /**
     * Get application status via API.
     */
    public function getStatusApi(string $referenceNumber)
    {
        $application = TenantApplication::where('reference_number', $referenceNumber)
            ->first();

        if (! $application) {
            return response()->json([
                'error' => 'Application not found',
                'message' => 'No application found with the provided reference number.',
            ], 404);
        }

        return response()->json([
            'reference_number' => $application->reference_number,
            'organization_name' => $application->organization_name,
            'status' => $application->status->value,
            'status_label' => $application->status->label(),
            'submitted_at' => $application->submitted_at->toISOString(),
            'reviewed_at' => $application->reviewed_at?->toISOString(),
            'rejection_reason' => $application->rejection_reason,
        ]);
    }

    /**
     * Get available industry types.
     */
    private function getIndustryTypes(): array
    {
        return [
            'technology' => 'Technology',
            'healthcare' => 'Healthcare',
            'finance' => 'Finance',
            'education' => 'Education',
            'retail' => 'Retail',
            'manufacturing' => 'Manufacturing',
            'consulting' => 'Consulting',
            'nonprofit' => 'Non-Profit',
            'media' => 'Media & Entertainment',
            'real_estate' => 'Real Estate',
            'other' => 'Other',
        ];
    }
}
