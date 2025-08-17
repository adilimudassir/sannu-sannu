<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\TenantApplication;
use App\Http\Controllers\Controller;
use App\Services\TenantApplicationService;
use App\Http\Requests\Admin\RejectTenantApplicationRequest;
use App\Http\Requests\Admin\ApproveTenantApplicationRequest;

class TenantApplicationController extends Controller
{
    public function index(Request $request)
    {
        // Basic filtering and sorting
        $query = TenantApplication::query();

        try {
            if ($status = $request->input('status')) {
                $query->where('status', $status);
            }
            
            // Wrap all search conditions in a single where closure for proper OR grouping
            if ($search = $request->input('search')) {
                $query->where(function($q) use ($search) {
                    $q->where('organization_name', 'like', "%$search%")
                      ->orWhere('contact_person_email', 'like', "%$search%")
                      ->orWhere('reference_number', 'like', "%$search%")
                      ->orWhere('contact_person_name', 'like', "%$search%")
                      ->orWhere('business_registration_number', 'like', "%$search%");
                });
            }

            // Validate sorting parameters
            $allowedSortFields = ['submitted_at', 'organization_name', 'status'];
            $sort = in_array($request->input('sort'), $allowedSortFields) 
                ? $request->input('sort') 
                : 'submitted_at';
            $direction = in_array($request->input('direction'), ['asc', 'desc']) 
                ? $request->input('direction') 
                : 'desc';
            
            $query->orderBy($sort, $direction);

            $applications = $query->paginate(15);

            return Inertia::render('admin/tenant-applications/index', [
                'applications' => [
                    'data' => $applications->items(),
                    'links' => $applications->links()->toArray(),
                    'current_page' => $applications->currentPage(),
                    'last_page' => $applications->lastPage(),
                    'per_page' => $applications->perPage(),
                    'total' => $applications->total(),
                ],
                'filters' => array_merge([
                    'search' => '',
                    'status' => '',
                    'sort' => 'submitted_at',
                    'direction' => 'desc',
                ], $request->only(['status', 'search', 'sort', 'direction']))
            ]);
        } catch (\Exception $e) {
            // Log error and return empty data set rather than crashing
            \Log::error('Error fetching tenant applications: ' . $e->getMessage());
            return Inertia::render('admin/tenant-applications/index', [
                'applications' => [
                    'data' => [],
                    'links' => [],
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 15,
                    'total' => 0,
                ],
                'filters' => array_merge([
                    'search' => '',
                    'status' => '',
                    'sort' => 'submitted_at',
                    'direction' => 'desc',
                ], $request->only(['status', 'search', 'sort', 'direction']))
            ]);
        }
    }
    public function show(TenantApplication $tenantApplication)
    {
        return Inertia::render('admin/tenant-applications/show', [
            'application' => $tenantApplication
        ]);
    }

    public function approve(ApproveTenantApplicationRequest $request, TenantApplication $tenantApplication, TenantApplicationService $service)
    {
        $tenant = $service->approveApplication($tenantApplication, $request->user(), $request->input('notes'));
        return redirect()->route('admin.tenant-applications.show', $tenantApplication)
            ->with('success', 'Application approved and tenant created: ' . $tenant->name);
    }

    public function reject(RejectTenantApplicationRequest $request, TenantApplication $tenantApplication, TenantApplicationService $service)
    {
        $service->rejectApplication(
            $tenantApplication,
            $request->input('rejection_reason'),
            $request->user(),
            $request->input('notes')
        );
        return redirect()->route('admin.tenant-applications.show', $tenantApplication)->with('success', 'Application rejected.');
    }
}
