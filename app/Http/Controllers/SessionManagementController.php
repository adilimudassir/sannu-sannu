<?php

namespace App\Http\Controllers;

use App\Services\SessionService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SessionManagementController extends Controller
{
    public function __construct(
        private SessionService $sessionService
    ) {}

    /**
     * Show active sessions for the authenticated user
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $activeSessions = $this->sessionService->getUserActiveSessions($user);

        return Inertia::render('settings/sessions', [
            'sessions' => $activeSessions,
        ]);
    }

    /**
     * Revoke a specific session
     */
    public function destroy(Request $request, string $sessionId): RedirectResponse
    {
        $user = $request->user();
        
        $success = $this->sessionService->revokeSession($sessionId, $user);
        
        if ($success) {
            return back()->with('status', 'Session revoked successfully.');
        }
        
        return back()->withErrors(['session' => 'Unable to revoke session.']);
    }

    /**
     * Revoke all other sessions (keep current session active)
     */
    public function destroyOthers(Request $request): RedirectResponse
    {
        $user = $request->user();
        $currentSessionId = $request->session()->getId();
        
        // Get all sessions except current
        $activeSessions = $this->sessionService->getUserActiveSessions($user);
        $revokedCount = 0;
        
        foreach ($activeSessions as $session) {
            if ($session['id'] !== $currentSessionId) {
                if ($this->sessionService->revokeSession($session['id'], $user)) {
                    $revokedCount++;
                }
            }
        }
        
        return back()->with('status', "Revoked {$revokedCount} other sessions.");
    }

    /**
     * Clear tenant context from current session
     */
    public function clearTenantContext(Request $request): RedirectResponse
    {
        $this->sessionService->clearTenantContext($request);
        
        return redirect()->route('tenant.select')
            ->with('status', 'Tenant context cleared. Please select a tenant.');
    }
}