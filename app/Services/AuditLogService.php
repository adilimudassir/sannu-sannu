<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class AuditLogService
{
    /**
     * Log authentication events with standardized format.
     */
    public static function logAuthEvent(
        string $event,
        ?User $user = null,
        ?Request $request = null,
        array $additionalData = []
    ): void {
        $logData = [
            'event' => $event,
            'timestamp' => now()->toISOString(),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'session_id' => $request?->session()?->getId(),
        ];

        if ($user) {
            $logData['user_id'] = $user->id;
            $logData['user_email'] = $user->email;
            $logData['user_role'] = $user->role->value ?? null;
        }

        // Merge additional data
        $logData = array_merge($logData, $additionalData);

        // Remove null values
        $logData = array_filter($logData, fn($value) => $value !== null);

        // Log based on event type
        match ($event) {
            'login_success' => Log::info('Authentication: Login successful', $logData),
            'login_failed' => Log::warning('Authentication: Login failed', $logData),
            'login_locked' => Log::warning('Authentication: Account locked due to too many attempts', $logData),
            'registration_success' => Log::info('Authentication: User registered successfully', $logData),
            'registration_failed' => Log::warning('Authentication: Registration failed', $logData),
            'password_reset_requested' => Log::info('Authentication: Password reset requested', $logData),
            'password_reset_success' => Log::info('Authentication: Password reset successful', $logData),
            'password_reset_failed' => Log::warning('Authentication: Password reset failed', $logData),
            'logout' => Log::info('Authentication: User logged out', $logData),
            'session_expired' => Log::info('Authentication: Session expired', $logData),
            'session_created' => Log::info('Session: Global session created', $logData),
            'session_destroyed' => Log::info('Session: Session destroyed', $logData),
            'session_revoked' => Log::info('Session: Session revoked', $logData),
            'all_sessions_invalidated' => Log::info('Session: All user sessions invalidated', $logData),
            'tenant_context_set' => Log::info('Session: Tenant context set', $logData),
            'tenant_context_cleared' => Log::info('Session: Tenant context cleared', $logData),
            'suspicious_activity' => Log::warning('Security: Suspicious activity detected', $logData),
            'role_changed' => Log::info('Authorization: User role changed', $logData),
            'permission_denied' => Log::warning('Authorization: Permission denied', $logData),
            default => Log::info("Authentication: {$event}", $logData),
        };
    }

    /**
     * Log security events that require immediate attention.
     */
    public static function logSecurityEvent(
        string $event,
        string $severity = 'warning',
        ?User $user = null,
        ?Request $request = null,
        array $additionalData = []
    ): void {
        $logData = [
            'security_event' => $event,
            'severity' => $severity,
            'timestamp' => now()->toISOString(),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'session_id' => $request?->session()?->getId(),
        ];

        if ($user) {
            $logData['user_id'] = $user->id;
            $logData['user_email'] = $user->email;
        }

        // Merge additional data
        $logData = array_merge($logData, $additionalData);

        // Remove null values
        $logData = array_filter($logData, fn($value) => $value !== null);

        // Log based on severity
        match ($severity) {
            'critical' => Log::critical("Security Alert: {$event}", $logData),
            'error' => Log::error("Security Error: {$event}", $logData),
            'warning' => Log::warning("Security Warning: {$event}", $logData),
            'info' => Log::info("Security Info: {$event}", $logData),
            default => Log::warning("Security: {$event}", $logData),
        };
    }

    /**
     * Log rate limiting events.
     */
    public static function logRateLimitEvent(
        string $action,
        ?Request $request = null,
        array $additionalData = []
    ): void {
        self::logSecurityEvent(
            "Rate limit exceeded for {$action}",
            'warning',
            null,
            $request,
            array_merge($additionalData, [
                'action' => $action,
                'rate_limit_key' => $request?->ip() . '|' . $action,
            ])
        );
    }

    /**
     * Log CSRF token validation failures.
     */
    public static function logCSRFFailure(?Request $request = null): void
    {
        self::logSecurityEvent(
            'CSRF token validation failed',
            'warning',
            null,
            $request,
            [
                'referer' => $request?->header('referer'),
                'origin' => $request?->header('origin'),
            ]
        );
    }

    /**
     * Log suspicious login patterns.
     */
    public static function logSuspiciousLogin(
        string $email,
        ?Request $request = null,
        string $reason = 'Multiple failed attempts'
    ): void {
        self::logSecurityEvent(
            'Suspicious login activity detected',
            'warning',
            null,
            $request,
            [
                'email' => $email,
                'reason' => $reason,
                'requires_investigation' => true,
            ]
        );
    }
}