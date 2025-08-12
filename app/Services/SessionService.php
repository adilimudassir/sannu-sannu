<?php

namespace App\Services;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SessionService
{
    /**
     * Create a secure global session for the user
     */
    public function createGlobalSession(User $user, Request $request): void
    {
        // Regenerate session ID for security
        $request->session()->regenerate();
        
        // Store global user information in session
        $request->session()->put([
            'user_id' => $user->id,
            'global_role' => $user->role->value,
            'login_time' => now(),
            'last_activity' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Update user's last login timestamp
        $user->update(['last_login_at' => now()]);

        // Log session creation
        AuditLogService::logAuthEvent('session_created', $user, $request);
    }

    /**
     * Add tenant context to the session for admin users
     */
    public function setTenantContext(int $tenantId, Request $request): bool
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }

        // Verify user has permissions in this tenant
        if (!$user->hasAnyTenantRole() && !$user->isSystemAdmin()) {
            return false;
        }

        // For non-system admins, verify they have a role in this specific tenant
        if (!$user->isSystemAdmin()) {
            $userRole = $user->getRoleInTenant($tenantId);
            if (!$userRole) {
                return false;
            }
        }

        // Load tenant to verify it exists
        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            return false;
        }

        // Store tenant context in session
        $request->session()->put([
            'selected_tenant_id' => $tenantId,
            'selected_tenant_slug' => $tenant->slug,
            'tenant_role' => $user->getRoleInTenant($tenantId)?->value,
            'tenant_context_set_at' => now(),
        ]);

        // Log tenant context change
        AuditLogService::logAuthEvent('tenant_context_set', $user, $request, [
            'tenant_id' => $tenantId,
            'tenant_slug' => $tenant->slug,
        ]);

        return true;
    }

    /**
     * Clear tenant context from session
     */
    public function clearTenantContext(Request $request): void
    {
        $user = Auth::user();
        
        $request->session()->forget([
            'selected_tenant_id',
            'selected_tenant_slug', 
            'tenant_role',
            'tenant_context_set_at',
        ]);

        if ($user) {
            AuditLogService::logAuthEvent('tenant_context_cleared', $user, $request);
        }
    }

    /**
     * Get current tenant context from session
     */
    public function getTenantContext(Request $request): ?array
    {
        $tenantId = $request->session()->get('selected_tenant_id');
        
        if (!$tenantId) {
            return null;
        }

        return [
            'tenant_id' => $tenantId,
            'tenant_slug' => $request->session()->get('selected_tenant_slug'),
            'tenant_role' => $request->session()->get('tenant_role'),
            'context_set_at' => $request->session()->get('tenant_context_set_at'),
        ];
    }

    /**
     * Check if session has expired based on inactivity
     */
    public function isSessionExpired(Request $request): bool
    {
        $lastActivity = $request->session()->get('last_activity');
        
        if (!$lastActivity) {
            return true;
        }

        $sessionLifetime = config('session.lifetime', 120); // minutes
        $expirationTime = Carbon::parse($lastActivity)->addMinutes($sessionLifetime);
        
        return now()->greaterThan($expirationTime);
    }

    /**
     * Update session activity timestamp
     */
    public function updateActivity(Request $request): void
    {
        $request->session()->put('last_activity', now());
    }

    /**
     * Handle session expiration
     */
    public function handleExpiredSession(Request $request): void
    {
        $user = Auth::user();
        
        if ($user) {
            AuditLogService::logAuthEvent('session_expired', $user, $request);
        }

        // Clear all session data
        $this->destroySession($request);
    }

    /**
     * Destroy the current session completely
     */
    public function destroySession(Request $request): void
    {
        $user = Auth::user();
        
        if ($user) {
            AuditLogService::logAuthEvent('session_destroyed', $user, $request);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    /**
     * Invalidate all sessions for a user (e.g., on password change)
     */
    public function invalidateAllUserSessions(User $user): void
    {
        // Get all active sessions for this user from database
        $activeSessions = DB::table('sessions')
            ->where('user_id', $user->id)
            ->get();

        // Delete all sessions for this user
        DB::table('sessions')
            ->where('user_id', $user->id)
            ->delete();

        // Log the session invalidation
        AuditLogService::logAuthEvent('all_sessions_invalidated', $user, null, [
            'invalidated_sessions_count' => $activeSessions->count(),
            'reason' => 'password_change',
        ]);

        Log::info('All sessions invalidated for user', [
            'user_id' => $user->id,
            'sessions_count' => $activeSessions->count(),
        ]);
    }

    /**
     * Get all active sessions for a user
     */
    public function getUserActiveSessions(User $user): array
    {
        $sessions = DB::table('sessions')
            ->where('user_id', $user->id)
            ->orderBy('last_activity', 'desc')
            ->get();

        return $sessions->map(function ($session) {
            $payload = unserialize(base64_decode($session->payload));
            
            return [
                'id' => $session->id,
                'ip_address' => $session->ip_address ?? 'Unknown',
                'user_agent' => $session->user_agent ?? 'Unknown',
                'last_activity' => Carbon::createFromTimestamp($session->last_activity),
                'is_current' => $session->id === session()->getId(),
                'location' => $this->getLocationFromIp($session->ip_address ?? ''),
            ];
        })->toArray();
    }

    /**
     * Revoke a specific session
     */
    public function revokeSession(string $sessionId, User $user): bool
    {
        $deleted = DB::table('sessions')
            ->where('id', $sessionId)
            ->where('user_id', $user->id)
            ->delete();

        if ($deleted) {
            AuditLogService::logAuthEvent('session_revoked', $user, null, [
                'revoked_session_id' => $sessionId,
            ]);
        }

        return $deleted > 0;
    }

    /**
     * Check if user should be prompted for session verification
     */
    public function shouldPromptForVerification(Request $request): bool
    {
        $loginTime = $request->session()->get('login_time');
        $currentIp = $request->ip();
        $sessionIp = $request->session()->get('ip_address');
        
        // Prompt if IP changed or session is older than 24 hours
        if ($currentIp !== $sessionIp) {
            return true;
        }

        if ($loginTime && Carbon::parse($loginTime)->diffInHours(now()) > 24) {
            return true;
        }

        return false;
    }

    /**
     * Get approximate location from IP address (basic implementation)
     */
    private function getLocationFromIp(string $ip): string
    {
        // This is a basic implementation - in production you might want to use
        // a proper IP geolocation service like MaxMind or similar
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return 'External Location';
        }
        
        return 'Local Network';
    }

    /**
     * Clean up expired sessions (can be called by scheduled command)
     */
    public function cleanupExpiredSessions(): int
    {
        $sessionLifetime = config('session.lifetime', 120); // minutes
        $expirationTimestamp = now()->subMinutes($sessionLifetime)->timestamp;

        $deletedCount = DB::table('sessions')
            ->where('last_activity', '<', $expirationTimestamp)
            ->delete();

        if ($deletedCount > 0) {
            Log::info('Cleaned up expired sessions', [
                'deleted_count' => $deletedCount,
            ]);
        }

        return $deletedCount;
    }
}