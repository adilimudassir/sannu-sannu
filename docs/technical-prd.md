# Technical Product Requirements Document (PRD)
## Sannu-Sannu SaaS Platform

### Technical Overview

Sannu-Sannu is built as a multi-tenant SaaS platform with a focus on security, scalability, and maintainability. The platform uses single database multi-tenancy with complete data isolation, allowing companies to register and manage their own workspace with public/private projects and flexible payment options.

### Technology Stack

#### Backend
- **Language**: PHP 8.4+ (Latest stable)
- **Framework**: Laravel 12.x (Latest LTS)
- **Database**: MySQL 8.4+ with multi-tenant architecture
- **Multi-Tenancy**: Single database with tenant isolation using Laravel Tenancy
- **Authentication**: Laravel Sanctum 4.x with session-based auth
- **Payment Processing**: Paystack API v2 integration
- **Email Service**: Laravel Mail 12.x with queue support
- **Queue System**: Redis 7.x/Database queues for background jobs
- **Cache**: Redis 7.x with Laravel Cache 12.x

#### Frontend
- **Framework**: React 19.x with TypeScript 5.x
- **Bridge**: Inertia.js 2.x for seamless SPA experience
- **Styling**: Tailwind CSS 4.x with shadcn/ui components
- **UI Components**: shadcn/ui latest with Radix UI primitives
- **Theming**: CSS custom properties with theme switching
- **State Management**: React Context API / Zustand 5.x
- **Form Handling**: React Hook Form 7.x with Inertia form helpers
- **Payment UI**: Paystack Inline v2 with React 19 components
- **Icons**: Lucide React (latest) for consistent iconography

#### Development Tools
- **Build Tool**: Vite 6.x for ultra-fast development and building
- **Package Manager**: pnpm 9.x (fastest package manager)
- **Code Quality**: ESLint 9.x, Prettier 3.x, PHP CS Fixer 3.x
- **Testing**: PHPUnit 11.x (backend), Vitest + React Testing Library (frontend)
- **Type Checking**: TypeScript 5.x with strict mode

#### Infrastructure
- **Web Server**: Apache/Nginx
- **SSL/TLS**: Let's Encrypt or commercial certificate
- **Caching**: Redis for sessions, cache, and queues
- **File Storage**: Laravel Storage with local/S3 drivers

### System Architecture

#### High-Level Architecture
```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   React SPA     │    │   Laravel App   │    │   MySQL DB      │
│   (Inertia.js)  │◄──►│   (Controllers) │◄──►│   (Eloquent)    │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         │                       │                       │
         ▼                       ▼                       ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Vite Build    │    │   Queue Jobs    │    │   Redis Cache   │
│   (Assets)      │    │   (Background)  │    │   (Sessions)    │
└─────────────────┘    └─────────────────┘    └─────────────────┘
                                │
                                ▼
                    ┌─────────────────┐    ┌─────────────────┐
                    │   Paystack API  │    │   Email Service │
                    │   (Payments)    │    │   (Laravel Mail)│
                    └─────────────────┘    └─────────────────┘
```

#### Application Layers
1. **Presentation Layer**: React components with Inertia.js bridge
2. **Controller Layer**: Laravel controllers handling HTTP requests
3. **Service Layer**: Business logic and external integrations
4. **Repository Layer**: Data access through Eloquent ORM
5. **Infrastructure Layer**: Queue jobs, events, and notifications

### Database Design

#### Core Entities
1. **Users**: User accounts and authentication
2. **Projects**: Project definitions and lifecycle management
3. **Contributions**: User contributions and payment tracking
4. **Transactions**: Payment transaction records
5. **Products**: Product definitions within projects

#### Key Relationships
- Users have many Contributions
- Projects have many Contributions
- Projects have many Products
- Contributions belong to Users and Projects
- Transactions belong to Contributions

### Route Design (Laravel + Inertia)

#### Authentication Routes
```php
// Web routes with Inertia responses
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
```

#### User Routes
```php
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});
```

#### Project Routes
```php
Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');

Route::middleware(['auth'])->group(function () {
    Route::post('/projects/{project}/join', [ContributionController::class, 'store'])->name('contributions.store');
    Route::get('/my-contributions', [ContributionController::class, 'index'])->name('contributions.index');
});
```

#### Payment Routes
```php
Route::middleware(['auth'])->group(function () {
    Route::post('/payments/initialize', [PaymentController::class, 'initialize'])->name('payments.initialize');
    Route::get('/payments/callback', [PaymentController::class, 'callback'])->name('payments.callback');
    Route::get('/payments/history', [PaymentController::class, 'history'])->name('payments.history');
});

// Webhook route (no auth middleware)
Route::post('/webhooks/paystack', [WebhookController::class, 'paystack'])->name('webhooks.paystack');
```

#### Admin Routes
```php
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::resource('projects', AdminProjectController::class);
    Route::resource('users', AdminUserController::class);
    Route::get('/projects/{project}/contributors', [AdminProjectController::class, 'contributors'])->name('projects.contributors');
    Route::get('/export/{project}', [AdminExportController::class, 'export'])->name('export');
});
```

### Security Requirements

#### Authentication & Authorization
- Session-based authentication with secure session management
- Password hashing using bcrypt with minimum 12 rounds
- Role-based access control (RBAC) for admin functions
- CSRF protection on all state-changing operations
- Session timeout and automatic logout

#### Data Protection
- Input validation and sanitization on all user inputs
- SQL injection prevention using prepared statements
- XSS protection through output encoding
- Secure file upload handling with type validation
- Personal data encryption for sensitive information

#### Payment Security
- PCI DSS compliance through Paystack integration
- No storage of sensitive payment information
- Secure webhook handling with signature verification
- Transaction logging and audit trails
- Fraud detection and prevention measures

#### Infrastructure Security
- HTTPS enforcement across all endpoints
- Security headers implementation (HSTS, CSP, etc.)
- Regular security updates and patches
- Database access restrictions and encryption
- Backup encryption and secure storage

### Performance Requirements

#### Response Time
- Page load time: < 3 seconds
- API response time: < 500ms
- Payment processing: < 10 seconds
- Database queries: < 100ms average

#### Scalability
- Support for 10,000+ concurrent users
- Database optimization for large datasets
- Caching strategy for frequently accessed data
- CDN integration for static assets
- Horizontal scaling capability

#### Availability
- 99.9% uptime requirement
- Automated backup and recovery procedures
- Health monitoring and alerting
- Graceful error handling and fallbacks
- Maintenance window scheduling

### Integration Specifications

#### Laravel Service Integration

##### Paystack Service
```php
// app/Services/PaystackService.php
class PaystackService
{
    public function initializePayment(array $data): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.paystack.secret_key'),
            'Content-Type' => 'application/json',
        ])->post('https://api.paystack.co/transaction/initialize', [
            'email' => $data['email'],
            'amount' => $data['amount'] * 100, // Convert to kobo
            'metadata' => $data['metadata'],
            'callback_url' => route('payments.callback'),
        ]);

        return $response->json();
    }
}
```

##### Email Notifications (Laravel Mail)
```php
// app/Mail/PaymentConfirmation.php
class PaymentConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public Project $project,
        public Transaction $transaction
    ) {}

    public function build()
    {
        return $this->subject('Payment Confirmation')
                    ->view('emails.payment-confirmation')
                    ->with([
                        'user' => $this->user,
                        'project' => $this->project,
                        'transaction' => $this->transaction,
                    ]);
    }
}

// Usage in controller
Mail::to($user->email)->queue(new PaymentConfirmation($user, $project, $transaction));
```

##### Queue Jobs for Background Processing
```php
// app/Jobs/ProcessPaymentJob.php
class ProcessPaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $paystackReference
    ) {}

    public function handle(PaystackService $paystack)
    {
        $paymentData = $paystack->verifyPayment($this->paystackReference);
        
        // Process payment logic
        DB::transaction(function () use ($paymentData) {
            // Update transaction status
            // Update contribution balance
            // Send notifications
        });
    }
}
```

### Laravel Eloquent Models

#### User Model
```php
// app/Models/User.php
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'role' => UserRole::class, // Enum
    ];

    public function contributions(): HasMany
    {
        return $this->hasMany(Contribution::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }
}
```

#### Project Model
```php
// app/Models/Project.php
class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'monthly_amount', 'start_date', 
        'end_date', 'total_project_value', 'status', 'created_by'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'monthly_amount' => 'decimal:2',
        'total_project_value' => 'decimal:2',
        'status' => ProjectStatus::class, // Enum
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(Contribution::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isActive(): bool
    {
        return $this->status === ProjectStatus::Active;
    }

    public function calculateArrears(Carbon $joinDate): float
    {
        if ($joinDate->lte($this->start_date)) {
            return 0;
        }

        $monthsElapsed = $this->start_date->diffInMonths($joinDate);
        return $monthsElapsed * $this->monthly_amount;
    }
}
```

#### Contribution Model
```php
// app/Models/Contribution.php
class Contribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'project_id', 'monthly_amount', 'duration_months',
        'total_committed', 'total_paid', 'arrears_amount', 'payment_type',
        'status', 'joined_date', 'next_payment_due'
    ];

    protected $casts = [
        'monthly_amount' => 'decimal:2',
        'total_committed' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'arrears_amount' => 'decimal:2',
        'joined_date' => 'date',
        'next_payment_due' => 'date',
        'payment_type' => PaymentType::class, // Enum
        'status' => ContributionStatus::class, // Enum
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function paymentSchedules(): HasMany
    {
        return $this->hasMany(PaymentSchedule::class);
    }

    public function getRemainingBalanceAttribute(): float
    {
        return $this->total_committed - $this->total_paid;
    }

    public function getProgressPercentageAttribute(): float
    {
        return ($this->total_paid / $this->total_committed) * 100;
    }
}
```

### Testing Strategy

#### Unit Testing
- Model validation and business logic
- Service layer functionality
- Utility functions and helpers
- Database query optimization

#### Integration Testing
- API endpoint functionality
- Payment gateway integration
- Email service integration
- Database transaction integrity

#### End-to-End Testing
- User registration and login flows
- Project participation workflows
- Payment processing scenarios
- Admin management functions

#### Security Testing
- Penetration testing for vulnerabilities
- Authentication and authorization testing
- Input validation and sanitization
- Payment security verification

### Deployment Architecture

#### Production Environment
```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Load Balancer │    │   Web Servers   │    │   Database      │
│   (Nginx)       │◄──►│   (Apache/PHP)  │◄──►│   (MySQL)       │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         │                       │                       │
         ▼                       ▼                       ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   CDN           │    │   Redis Cache   │    │   Backup        │
│   (Static)      │    │   (Sessions)    │    │   (Automated)   │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

#### CI/CD Pipeline
1. **Development**: Local development with Docker
2. **Testing**: Automated testing on feature branches
3. **Staging**: Pre-production environment for QA
4. **Production**: Blue-green deployment strategy

### Monitoring & Analytics

#### Application Monitoring
- Error tracking and logging
- Performance monitoring
- User activity analytics
- Payment transaction monitoring

#### Infrastructure Monitoring
- Server resource utilization
- Database performance metrics
- Network latency and throughput
- Security event monitoring

### Maintenance & Support

#### Regular Maintenance
- Security updates and patches
- Database optimization and cleanup
- Performance monitoring and tuning
- Backup verification and testing

#### Support Requirements
- 24/7 system monitoring
- Issue escalation procedures
- User support documentation
- Admin training materials

### Future Technical Considerations

#### Scalability Enhancements
- Microservices architecture migration
- Database sharding strategies
- Caching layer improvements
- API rate limiting implementation

#### Feature Extensions
- Mobile API development
- Real-time notifications (WebSockets)
- Advanced analytics dashboard
- Multi-tenant architecture support