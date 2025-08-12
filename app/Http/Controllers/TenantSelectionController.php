<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class TenantSelectionController extends Controller
{
    /**
     * Show tenant selection page for admin users
     */
    public function show(Request $request)
    {
        $user = $request->user();
        
        // If user doesn't need tenant selection, redirect to global dashboard
        if (!$user->needsTenantSelection()) {
            return redirect()->route('global.dashboard');
        }
        
        // Get tenants where user has admin roles
        $tenants = $user->getAdminTenants();
        
        return Inertia::render('auth/select-tenant', [
            'tenants' => $tenants->map(function ($tenant) use ($user) {
                return [
                    'id' => $tenant->id,
                    'slug' => $tenant->slug,
                    'name' => $tenant->name,
                    'logo_url' => $tenant->logo_url,
                    'role' => $user->getRoleInTenant($tenant->id)?->value,
                ];
            }),
        ]);
    }
    
    /**
     * Handle tenant selection
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
        ]);
        
        $user = $request->user();
        $tenantId = $request->tenant_id;
        
        // Verify user has access to this tenant
        if (!$user->hasAnyTenantRole() || !$user->tenants()->where('tenants.id', $tenantId)->exists()) {
            abort(403, 'You do not have access to this tenant.');
        }
        
        // Store selected tenant in session
        session(['selected_tenant_id' => $tenantId]);
        
        // Get tenant for redirect
        $tenant = Tenant::findOrFail($tenantId);
        
        // Redirect to tenant dashboard
        return redirect()->route('tenant.dashboard', ['tenant' => $tenant->slug]);
    }
}