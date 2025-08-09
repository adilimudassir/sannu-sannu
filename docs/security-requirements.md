# Security Requirements
## Sannu-Sannu SaaS Platform

### Security Overview

The Sannu-Sannu platform handles sensitive user data, financial transactions, and personal information. This document outlines comprehensive security requirements and implementation guidelines to ensure data protection, user privacy, and system integrity.

---

## Authentication & Authorization

### User Authentication

#### Password Security
- **Minimum Requirements**: 8 characters, mix of uppercase, lowercase, numbers, and symbols
- **Hashing**: bcrypt with minimum 12 rounds
- **Storage**: Never store plain text passwords
- **Reset**: Secure password reset with time-limited tokens

```php
// Laravel implementation
use Illuminate\Support\Facades\Hash;

// Password hashing
$hashedPassword = Hash::make($password, ['rounds' => 12]);

// Password verification
if (Hash::check($password, $hashedPassword)) {
    // Password is correct
}
```

#### Session Management
- **Session Timeout**: 2 hours of inactivity
- **Secure Cookies**: HttpOnly, Secure, SameSite attributes
- **Session Regeneration**: On login and privilege escalation
- **Concurrent Sessions**: Limit to 3 active sessions per user

```php
// Laravel session configuration
'lifetime' => 120, // 2 hours
'expire_on_close' => false,
'encrypt' => true,
'http_only' => true,
'same_site' => 'lax',
'secure' => env('SESSION_SECURE_COOKIE', true),
```

#### Multi-Factor Authentication (Future Enhancement)
- **TOTP Support**: Time-based one-time passwords
- **SMS Backup**: Secondary authentication method
- **Recovery Codes**: One-time use backup codes

### Role-Based Access Control (RBAC)

#### User Roles
```php
enum UserRole: string
{
    case Admin = 'admin';
    case User = 'user';
}
```

#### Permission Matrix
| Resource | Admin | User |
|----------|-------|------|
| View Projects | ✓ | ✓ |
| Create Projects | ✓ | ✗ |
| Edit Projects | ✓ | ✗ |
| Delete Projects | ✓ | ✗ |
| View All Users | ✓ | ✗ |
| Export Data | ✓ | ✗ |
| Join Projects | ✓ | ✓ |
| Make Payments | ✓ | ✓ |
| View Own Data | ✓ | ✓ |

#### Laravel Policy Implementation
```php
// app/Policies/ProjectPolicy.php
class ProjectPolicy
{
    public function create(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function update(User $user, Project $project): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function viewContributors(User $user, Project $project): bool
    {
        return $user->role === UserRole::Admin;
    }
}
```

---

## Data Protection

### Personal Data Handling

#### Data Classification
- **Public**: Project names, descriptions, general statistics
- **Internal**: User names, contribution amounts, project participation
- **Confidential**: Email addresses, payment details, personal preferences
- **Restricted**: Password hashes, payment tokens, audit logs

#### Data Encryption
```php
// Laravel encryption for sensitive data
use Illuminate\Support\Facades\Crypt;

// Encrypt sensitive data before storage
$encryptedData = Crypt::encryptString($sensitiveData);

// Decrypt when needed
$decryptedData = Crypt::decryptString($encryptedData);
```

#### Database Security
```sql
-- Encrypt sensitive columns
ALTER TABLE users ADD COLUMN phone_encrypted VARBINARY(255);
ALTER TABLE users ADD COLUMN address_encrypted VARBINARY(255);

-- Use prepared statements (Laravel Eloquent handles this)
-- Never use raw SQL with user input
```

### GDPR Compliance

#### Data Subject Rights
1. **Right to Access**: Users can download their data
2. **Right to Rectification**: Users can update their information
3. **Right to Erasure**: Users can request account deletion
4. **Right to Portability**: Data export in machine-readable format
5. **Right to Object**: Users can opt-out of processing

#### Implementation
```php
// app/Http/Controllers/DataExportController.php
class DataExportController extends Controller
{
    public function export(Request $request)
    {
        $user = $request->user();
        
        $userData = [
            'profile' => $user->only(['name', 'email', 'created_at']),
            'contributions' => $user->contributions()->with('project')->get(),
            'transactions' => $user->transactions()->get(),
        ];
        
        return response()->json($userData)
            ->header('Content-Disposition', 'attachment; filename="user-data.json"');
    }
}
```

---

## Input Validation & Sanitization

### Laravel Form Requests
```php
// app/Http/Requests/CreateProjectRequest.php
class CreateProjectRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9\s\-_]+$/'],
            'description' => ['required', 'string', 'max:1000'],
            'monthly_amount' => ['required', 'numeric', 'min:1', 'max:1000000'],
            'start_date' => ['required', 'date', 'after:today'],
            'end_date' => ['required', 'date', 'after:start_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'Project name contains invalid characters.',
            'monthly_amount.max' => 'Monthly amount cannot exceed ₦1,000,000.',
        ];
    }
}
```

### XSS Prevention
```php
// Automatic escaping in Blade templates
{{ $user->name }} // Automatically escaped

// Manual escaping when needed
{!! e($htmlContent) !!}

// React/Inertia.js - automatic escaping
<div>{user.name}</div> // Automatically escaped by React
```

### SQL Injection Prevention
```php
// Always use Eloquent ORM or Query Builder
User::where('email', $email)->first(); // Safe

// Never use raw SQL with user input
DB::raw("SELECT * FROM users WHERE email = '$email'"); // DANGEROUS
```

---

## Payment Security

### PCI DSS Compliance

#### Paystack Integration
- **No Card Data Storage**: All payment data handled by Paystack
- **Tokenization**: Use Paystack tokens for recurring payments
- **Webhook Security**: Verify webhook signatures

```php
// app/Services/PaystackService.php
class PaystackService
{
    public function verifyWebhook(Request $request): bool
    {
        $signature = $request->header('X-Paystack-Signature');
        $payload = $request->getContent();
        
        $expectedSignature = hash_hmac('sha512', $payload, config('services.paystack.webhook_secret'));
        
        return hash_equals($signature, $expectedSignature);
    }
}
```

#### Transaction Security
```php
// app/Models/Transaction.php
class Transaction extends Model
{
    protected $fillable = [
        'contribution_id',
        'user_id',
        'paystack_reference',
        'amount',
        'type',
        'status',
    ];

    // Never store sensitive payment data
    protected $hidden = [
        'paystack_response', // Only store necessary metadata
    ];

    protected $casts = [
        'paystack_response' => 'encrypted:array', // Encrypt if needed
    ];
}
```

---

## Infrastructure Security

### HTTPS Configuration
```nginx
# Nginx SSL configuration
server {
    listen 443 ssl http2;
    server_name sannu-sannu.com;
    
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;
    
    # Security headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "DENY" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    
    # CSP header
    add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' js.paystack.co; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; connect-src 'self' api.paystack.co;" always;
}
```

### Environment Security
```bash
# .env file permissions
chmod 600 .env

# Environment variables
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:generated-key-here

# Database credentials
DB_PASSWORD=strong-random-password

# API keys
PAYSTACK_SECRET_KEY=sk_live_xxxxx
PAYSTACK_PUBLIC_KEY=pk_live_xxxxx
```

### File Upload Security
```php
// app/Http/Controllers/FileUploadController.php
class FileUploadController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:2048', // 2MB max
            ],
        ]);

        $file = $request->file('file');
        
        // Generate secure filename
        $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
        
        // Store in secure location
        $path = $file->storeAs('uploads', $filename, 'private');
        
        return response()->json(['path' => $path]);
    }
}
```

---

## Monitoring & Logging

### Security Event Logging
```php
// app/Services/SecurityLogger.php
class SecurityLogger
{
    public static function logFailedLogin(string $email, string $ip): void
    {
        Log::channel('security')->warning('Failed login attempt', [
            'email' => $email,
            'ip' => $ip,
            'user_agent' => request()->userAgent(),
            'timestamp' => now(),
        ]);
    }

    public static function logSuspiciousActivity(User $user, string $activity): void
    {
        Log::channel('security')->alert('Suspicious activity detected', [
            'user_id' => $user->id,
            'activity' => $activity,
            'ip' => request()->ip(),
            'timestamp' => now(),
        ]);
    }
}
```

### Rate Limiting
```php
// app/Http/Kernel.php
protected $middlewareGroups = [
    'web' => [
        // ... other middleware
        'throttle:web',
    ],
    'api' => [
        'throttle:api',
    ],
];

// config/cache.php - Rate limiting configuration
'throttle' => [
    'web' => '60,1', // 60 requests per minute
    'api' => '100,1', // 100 requests per minute
    'login' => '5,1', // 5 login attempts per minute
    'payment' => '10,1', // 10 payment requests per minute
],
```

---

## Incident Response

### Security Incident Classification
1. **Low**: Minor configuration issues, failed login attempts
2. **Medium**: Suspicious user activity, potential data exposure
3. **High**: Confirmed data breach, system compromise
4. **Critical**: Large-scale data breach, payment system compromise

### Response Procedures
```php
// app/Services/IncidentResponse.php
class IncidentResponse
{
    public function handleSecurityIncident(string $type, array $details): void
    {
        // Log incident
        Log::channel('security')->critical('Security incident', [
            'type' => $type,
            'details' => $details,
            'timestamp' => now(),
        ]);

        // Notify administrators
        $admins = User::where('role', UserRole::Admin)->get();
        Notification::send($admins, new SecurityIncidentNotification($type, $details));

        // Auto-response for critical incidents
        if ($type === 'critical') {
            $this->enableMaintenanceMode();
            $this->notifySecurityTeam();
        }
    }
}
```

### Backup & Recovery
```bash
#!/bin/bash
# Daily encrypted backup script
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/secure/backups"
DB_NAME="sannu_sannu"

# Create encrypted database backup
mysqldump --single-transaction $DB_NAME | \
gpg --cipher-algo AES256 --compress-algo 1 --symmetric \
--output $BACKUP_DIR/db_backup_$DATE.sql.gpg

# Create encrypted file backup
tar -czf - /var/www/sannu-sannu/storage | \
gpg --cipher-algo AES256 --compress-algo 1 --symmetric \
--output $BACKUP_DIR/files_backup_$DATE.tar.gz.gpg

# Remove backups older than 30 days
find $BACKUP_DIR -name "*.gpg" -mtime +30 -delete
```

---

## Security Testing

### Automated Security Scanning
```yaml
# .github/workflows/security.yml
name: Security Scan
on: [push, pull_request]

jobs:
  security:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      
      - name: Run PHP Security Checker
        run: |
          composer install
          ./vendor/bin/security-checker security:check composer.lock
      
      - name: Run SAST Analysis
        uses: github/super-linter@v4
        env:
          DEFAULT_BRANCH: main
          GITHUB_TOKEN: ${{ secrets.GITHUB_TOKEN }}
```

### Manual Security Checklist
- [ ] All user inputs validated and sanitized
- [ ] SQL injection prevention implemented
- [ ] XSS protection in place
- [ ] CSRF tokens on all forms
- [ ] Secure headers configured
- [ ] HTTPS enforced
- [ ] File upload restrictions implemented
- [ ] Rate limiting configured
- [ ] Logging and monitoring active
- [ ] Backup and recovery tested
- [ ] Security incident response plan documented

This comprehensive security framework ensures the Sannu-Sannu platform maintains the highest standards of data protection and user privacy while complying with relevant regulations and industry best practices.