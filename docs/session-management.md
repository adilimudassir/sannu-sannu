# Global Session Management with Optional Tenant Context

## Overview

The session management system provides secure global authentication with optional tenant context for multi-tenant applications. It includes features like session expiration, multi-device session management, and tenant context switching.

## Key Features

### 1. Global Session Creation
- Secure session creation using Laravel Sanctum
- Automatic session regeneration for security
- User activity tracking and audit logging
- Role-based redirect logic after login

### 2. Tenant Context Management
- Optional tenant context storage for admin users
- Secure tenant selection and validation
- Session-based tenant context persistence
- Easy tenant context switching

### 3. Session Expiration Handling
- Configurable session lifetime (default: 120 minutes)
- Automatic session expiration detection
- User-friendly expiration messages
- Graceful session cleanup

### 4. Multi-Device Session Management
- View all active sessions across devices
- Revoke specific sessions remotely
- Bulk revocation of other sessions
- Session invalidation on password changes

### 5. Security Features
- IP address change detection
- Session verification prompts
- Comprehensive audit logging
- Rate limiting protection

## Components

### SessionService
Main service class handling all session operations:

```php
// Create global session
$sessionService->createGlobalSession($user, $request);

// Set tenant context
$sessionService->setTenantContext($tenantId, $request);

// Check session expiration
$isExpired = $sessionService->isSessionExpired($request);

// Invalidate all user sessions
$sessionService->invalidateAllUserSessions($user);
```

### SessionActivityMiddleware
Middleware that handles session activity tracking and expiration:

- Automatically updates last activity timestamp
- Checks for session expiration on each request
- Redirects expired sessions to login page
- Bypasses checks for guest users

### SessionManagementController
Controller for user session management interface:

- View active sessions
- Revoke specific sessions
- Revoke all other sessions
- Clear tenant context

## Usage Examples

### Basic Session Creation (Login)
```php
public function store(LoginRequest $request): RedirectResponse
{
    $request->authenticate();
    $user = $request->user();
    
    // Create secure global session
    $this->sessionService->createGlobalSession($user, $request);
    
    // Role-based redirect
    if ($user->isSystemAdmin()) {
        return redirect()->route('admin.dashboard');
    } elseif ($user->needsTenantSelection()) {
        return redirect()->route('tenant.select');
    } else {
        return redirect()->route('global.dashboard');
    }
}
```

### Tenant Context Management
```php
public function store(Request $request): RedirectResponse
{
    $tenantId = $request->tenant_id;
    
    // Set tenant context
    if (!$this->sessionService->setTenantContext($tenantId, $request)) {
        return back()->withErrors(['tenant_id' => 'Unable to set tenant context.']);
    }
    
    return redirect()->route('tenant.dashboard', ['tenant' => $tenant->slug]);
}
```

### Session Invalidation (Password Change)
```php
public function update(Request $request): RedirectResponse
{
    $user = $request->user();
    
    // Update password
    $user->update(['password' => Hash::make($validated['password'])]);
    
    // Invalidate all other sessions for security
    $this->sessionService->invalidateAllUserSessions($user);
    
    // Create new session for current user
    $this->sessionService->createGlobalSession($user, $request);
    
    return back()->with('status', 'Password updated successfully.');
}
```

## Configuration

### Session Settings
Configure in `config/session.php`:

```php
'lifetime' => env('SESSION_LIFETIME', 120), // minutes
'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false),
'encrypt' => env('SESSION_ENCRYPT', false),
```

### Middleware Registration
The `SessionActivityMiddleware` is automatically registered in the web middleware group.

## Database Schema

### Sessions Table
The standard Laravel sessions table is used:

```sql
CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload LONGTEXT NOT NULL,
    last_activity INTEGER NOT NULL,
    INDEX sessions_user_id_index (user_id),
    INDEX sessions_last_activity_index (last_activity)
);
```

## Frontend Integration

### Session Management Page
Users can view and manage their active sessions:

```tsx
// View active sessions
<SessionList sessions={sessions} />

// Revoke specific session
<Button onClick={() => revokeSession(sessionId)}>
  Revoke Session
</Button>

// Revoke all other sessions
<Button onClick={revokeAllOthers}>
  Revoke All Other Sessions
</Button>
```

## Console Commands

### Cleanup Expired Sessions
```bash
php artisan sessions:cleanup
```

This command removes expired sessions from the database and should be run regularly via cron job.

## Security Considerations

1. **Session Regeneration**: Sessions are regenerated on login for security
2. **IP Tracking**: IP address changes trigger verification prompts
3. **Audit Logging**: All session events are logged for security monitoring
4. **Rate Limiting**: Login attempts are rate limited to prevent brute force attacks
5. **Secure Cookies**: Session cookies use secure flags in production

## Testing

Comprehensive test coverage includes:

- Unit tests for SessionService methods
- Feature tests for authentication flows
- Integration tests for middleware behavior
- Session management UI tests

Run tests with:
```bash
php artisan test tests/Unit/SessionServiceTest.php
php artisan test tests/Feature/SessionManagementTest.php
```

## Monitoring and Maintenance

### Log Monitoring
Monitor these log events:
- `session_created`: New session creation
- `session_expired`: Session expiration
- `session_revoked`: Manual session revocation
- `all_sessions_invalidated`: Bulk session invalidation

### Performance Considerations
- Use Redis for session storage in production
- Regularly clean up expired sessions
- Monitor session table size
- Consider session data compression for large payloads

### Troubleshooting

Common issues and solutions:

1. **Sessions expiring too quickly**: Check `SESSION_LIFETIME` configuration
2. **Middleware redirects**: Ensure `last_activity` is set properly
3. **Tenant context lost**: Verify tenant selection flow
4. **Multiple redirects**: Check middleware order and conditions