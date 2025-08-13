# Design Document

## Overview

The Project Management System is a comprehensive feature that enables tenant administrators and system administrators to create, manage, and control contribution-based projects within the Sannu-Sannu platform. The system integrates with the existing global authentication architecture and multi-tenant structure, providing role-based access control and cross-tenant management capabilities.

## Architecture

### System Integration

The Project Management System integrates with existing platform components:

- **Authentication System**: Leverages existing global authentication with role-based access
- **Multi-Tenant Architecture**: Works within the tenant context while allowing cross-tenant operations for system admins
- **Authorization System**: Uses existing policies and middleware for access control
- **File Storage**: Integrates with Laravel Storage for product image management
- **Audit System**: Leverages existing AuditLogService for operation tracking

### Route Structure

Following the established routing patterns:

```
Global Routes (System Admin):
- GET /admin/projects - List all projects across tenants
- GET /admin/projects/create - Create project form
- POST /admin/projects - Store new project
- GET /admin/projects/{project}/edit - Edit project form
- PUT /admin/projects/{project} - Update project
- DELETE /admin/projects/{project} - Delete project

Tenant Routes (Tenant Admin):
- GET /{tenant}/projects - List tenant projects
- GET /{tenant}/projects/create - Create project form
- POST /{tenant}/projects - Store new project
- GET /{tenant}/projects/{project} - View project details
- GET /{tenant}/projects/{project}/edit - Edit project form
- PUT /{tenant}/projects/{project} - Update project
- DELETE /{tenant}/projects/{project} - Delete project

Public Routes (Contributors):
- GET /projects - Browse public projects
- GET /projects/{project} - View public project details
- GET /projects/search - Search projects
```

## Components and Interfaces

### Backend Components

#### Models

**Project Model**
```php
class Project extends Model
{
    use BelongsToTenant;
    
    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'description',
        'visibility',
        'requires_approval',
        'max_contributors',
        'total_amount',
        'minimum_contribution',
        'payment_options',
        'installment_frequency',
        'custom_installment_months',
        'start_date',
        'end_date',
        'registration_deadline',
        'created_by',
        'managed_by',
        'status',
        'settings'
    ];
    
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'registration_deadline' => 'date',
        'status' => ProjectStatus::class,
        'visibility' => ProjectVisibility::class,
        'requires_approval' => 'boolean',
        'total_amount' => 'decimal:2',
        'minimum_contribution' => 'decimal:2',
        'payment_options' => 'array',
        'managed_by' => 'array',
        'settings' => 'array'
    ];
    
    // Relationships
    public function tenant(): BelongsTo
    public function creator(): BelongsTo
    public function products(): HasMany
    public function contributions(): HasMany
    public function invitations(): HasMany
    public function transactions(): HasManyThrough
}
```

**Product Model**
```php
class Product extends Model
{
    protected $fillable = [
        'tenant_id',
        'project_id',
        'name',
        'description',
        'price',
        'image_url',
        'sort_order'
    ];
    
    protected $casts = [
        'price' => 'decimal:2',
        'sort_order' => 'integer'
    ];
    
    // Relationships
    public function tenant(): BelongsTo
    public function project(): BelongsTo
}
```

#### Enums

**ProjectStatus Enum**
```php
enum ProjectStatus: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case PAUSED = 'paused';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
}
```

**ProjectVisibility Enum**
```php
enum ProjectVisibility: string
{
    case PUBLIC = 'public';
    case PRIVATE = 'private';
    case INVITE_ONLY = 'invite_only';
}
```

#### Services

**ProjectService**
```php
class ProjectService
{
    public function createProject(array $data, Tenant $tenant): Project
    public function updateProject(Project $project, array $data): Project
    public function deleteProject(Project $project): bool
    public function activateProject(Project $project): Project
    public function pauseProject(Project $project): Project
    public function completeProject(Project $project): Project
    public function calculateProjectTotal(Project $project): float
    public function getProjectStatistics(Project $project): array
    public function getPublicProjects(array $filters = []): Collection
    public function searchProjects(string $query, array $filters = []): Collection
}
```

**ProductService**
```php
class ProductService
{
    public function addProduct(Project $project, array $data): Product
    public function updateProduct(Product $product, array $data): Product
    public function deleteProduct(Product $product): bool
    public function reorderProducts(Project $project, array $order): void
    public function uploadProductImage(UploadedFile $file): string
    public function deleteProductImage(string $path): bool
}
```

#### Controllers

**ProjectController** (Tenant Routes)
```php
class ProjectController extends Controller
{
    public function __construct(
        private ProjectService $projectService,
        private ProductService $productService
    ) {}
    
    public function index(Request $request): Response
    public function create(): Response
    public function store(StoreProjectRequest $request): RedirectResponse
    public function show(Project $project): Response
    public function edit(Project $project): Response
    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    public function destroy(Project $project): RedirectResponse
}
```

**Admin\ProjectController** (System Admin Routes)
```php
class Admin\ProjectController extends Controller
{
    public function index(Request $request): Response
    public function create(): Response
    public function store(StoreProjectRequest $request): RedirectResponse
    public function show(Project $project): Response
    public function edit(Project $project): Response
    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    public function destroy(Project $project): RedirectResponse
}
```

**PublicProjectController** (Public Routes)
```php
class PublicProjectController extends Controller
{
    public function index(Request $request): Response
    public function show(Project $project): Response
    public function search(SearchProjectsRequest $request): Response
}
```

#### Request Classes

**StoreProjectRequest**
```php
class StoreProjectRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date|after:today',
            'end_date' => 'required|date|after:start_date',
            'visibility' => 'required|in:public,private,invite_only',
            'max_contributors' => 'nullable|integer|min:1',
            'products' => 'required|array|min:1',
            'products.*.name' => 'required|string|max:255',
            'products.*.description' => 'required|string',
            'products.*.price' => 'required|numeric|min:0',
            'products.*.image' => 'nullable|image|max:2048'
        ];
    }
}
```

#### Policies

**ProjectPolicy**
```php
class ProjectPolicy
{
    public function viewAny(User $user): bool
    public function view(User $user, Project $project): bool
    public function create(User $user): bool
    public function update(User $user, Project $project): bool
    public function delete(User $user, Project $project): bool
    public function activate(User $user, Project $project): bool
    public function pause(User $user, Project $project): bool
}
```

### Frontend Components

#### Pages

**Project List Page** (`resources/js/pages/projects/index.tsx`)
```tsx
interface ProjectListProps {
    projects: PaginatedData<Project>;
    filters: ProjectFilters;
    tenant?: Tenant;
}

export default function ProjectList({ projects, filters, tenant }: ProjectListProps) {
    // Project listing with filters, search, and pagination
    // Different views for tenant admin vs system admin
}
```

**Project Create/Edit Page** (`resources/js/pages/projects/form.tsx`)
```tsx
interface ProjectFormProps {
    project?: Project;
    tenant?: Tenant;
    tenants?: Tenant[]; // For system admin
}

export default function ProjectForm({ project, tenant, tenants }: ProjectFormProps) {
    // Project creation/editing form with product management
    // Dynamic product addition/removal
    // Image upload handling
}
```

**Project Details Page** (`resources/js/pages/projects/show.tsx`)
```tsx
interface ProjectDetailsProps {
    project: Project;
    statistics: ProjectStatistics;
    canEdit: boolean;
}

export default function ProjectDetails({ project, statistics, canEdit }: ProjectDetailsProps) {
    // Project information display
    // Statistics and analytics
    // Action buttons based on permissions
}
```

**Public Projects Page** (`resources/js/pages/public/projects.tsx`)
```tsx
interface PublicProjectsProps {
    projects: PaginatedData<Project>;
    filters: ProjectFilters;
}

export default function PublicProjects({ projects, filters }: PublicProjectsProps) {
    // Public project browsing
    // Search and filtering
    // Project cards with key information
}
```

#### Reusable Components

**ProjectCard** (`resources/js/components/projects/project-card.tsx`)
```tsx
interface ProjectCardProps {
    project: Project;
    showTenant?: boolean;
    showActions?: boolean;
}

export default function ProjectCard({ project, showTenant, showActions }: ProjectCardProps) {
    // Reusable project card component
    // Used in listings and search results
}
```

**ProductManager** (`resources/js/components/projects/product-manager.tsx`)
```tsx
interface ProductManagerProps {
    products: Product[];
    onChange: (products: Product[]) => void;
    errors?: Record<string, string>;
}

export default function ProductManager({ products, onChange, errors }: ProductManagerProps) {
    // Dynamic product management component
    // Add/remove/reorder products
    // Image upload handling
}
```

**ProjectStatusBadge** (`resources/js/components/projects/status-badge.tsx`)
```tsx
interface ProjectStatusBadgeProps {
    status: ProjectStatus;
    className?: string;
}

export default function ProjectStatusBadge({ status, className }: ProjectStatusBadgeProps) {
    // Status indicator component using shadcn/ui Badge
}
```

**ProjectFilters** (`resources/js/components/projects/filters.tsx`)
```tsx
interface ProjectFiltersProps {
    filters: ProjectFilters;
    onChange: (filters: ProjectFilters) => void;
}

export default function ProjectFilters({ filters, onChange }: ProjectFiltersProps) {
    // Filter component for project listings
    // Status, visibility, date range filters
}
```

## Data Models

### Database Schema

**projects table**
```sql
CREATE TABLE projects (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    description TEXT NULL,

    -- Project visibility and access
    visibility ENUM('public', 'private', 'invite_only') NOT NULL DEFAULT 'public',
    requires_approval BOOLEAN DEFAULT FALSE,
    max_contributors INT NULL,

    -- Financial details
    total_amount DECIMAL(12,2) NOT NULL,
    minimum_contribution DECIMAL(10,2) NULL,

    -- Payment flexibility
    payment_options JSON NOT NULL, -- ['full', 'installments']
    installment_frequency ENUM('monthly', 'quarterly', 'custom') DEFAULT 'monthly',
    custom_installment_months INT NULL,

    -- Timeline
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    registration_deadline DATE NULL,

    -- Project management
    created_by INT UNSIGNED NOT NULL,
    managed_by JSON NULL, -- Array of user IDs who can manage
    status ENUM('draft', 'active', 'paused', 'completed', 'cancelled') NOT NULL DEFAULT 'draft',

    -- Settings
    settings JSON NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    UNIQUE KEY uk_projects_tenant_slug (tenant_id, slug),
    INDEX idx_projects_tenant (tenant_id),
    INDEX idx_projects_visibility (visibility),
    INDEX idx_projects_status (status),
    INDEX idx_projects_dates (start_date, end_date),
    INDEX idx_projects_tenant_status (tenant_id, status),
    INDEX idx_projects_visibility_status (visibility, status),

    CONSTRAINT chk_projects_dates CHECK (end_date > start_date),
    CONSTRAINT chk_projects_amounts CHECK (total_amount > 0)
);
```

**products table**
```sql
CREATE TABLE products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    project_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    price DECIMAL(10,2) NOT NULL,
    image_url VARCHAR(500) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    INDEX idx_products_tenant (tenant_id),
    INDEX idx_products_project_sort (project_id, sort_order),

    CONSTRAINT chk_products_price CHECK (price > 0)
);
```

### TypeScript Interfaces

```typescript
interface Project {
    id: number;
    tenant_id: number;
    name: string;
    slug: string;
    description?: string;
    visibility: ProjectVisibility;
    requires_approval: boolean;
    max_contributors?: number;
    total_amount: number;
    minimum_contribution?: number;
    payment_options: string[];
    installment_frequency: 'monthly' | 'quarterly' | 'custom';
    custom_installment_months?: number;
    start_date: string;
    end_date: string;
    registration_deadline?: string;
    created_by: number;
    managed_by?: number[];
    status: ProjectStatus;
    settings?: Record<string, any>;
    created_at: string;
    updated_at: string;
    tenant?: Tenant;
    creator?: User;
    products?: Product[];
    statistics?: ProjectStatistics;
}

interface Product {
    id: number;
    tenant_id: number;
    project_id: number;
    name: string;
    description?: string;
    price: number;
    image_url?: string;
    sort_order: number;
    created_at: string;
    updated_at: string;
    tenant?: Tenant;
    project?: Project;
}

interface ProjectStatistics {
    total_contributors: number;
    total_raised: number;
    completion_percentage: number;
    average_contribution: number;
    days_remaining: number;
}

interface ProjectFilters {
    status?: ProjectStatus[];
    visibility?: ProjectVisibility[];
    tenant_id?: number;
    min_amount?: number;
    max_amount?: number;
    start_date?: string;
    end_date?: string;
    search?: string;
}

type ProjectStatus = 'draft' | 'active' | 'paused' | 'completed' | 'cancelled';
type ProjectVisibility = 'public' | 'private' | 'invite_only';
```

## Error Handling

### Backend Error Handling

- **Validation Errors**: Form request validation with detailed error messages
- **Authorization Errors**: Policy-based access control with appropriate HTTP status codes
- **Business Logic Errors**: Service-level validation with custom exceptions
- **File Upload Errors**: Image validation and storage error handling
- **Database Errors**: Transaction rollback and error logging

### Frontend Error Handling

- **Form Validation**: Real-time validation with error display
- **API Errors**: Centralized error handling with user-friendly messages
- **File Upload Errors**: Progress indication and error feedback
- **Network Errors**: Retry mechanisms and offline handling

## Testing Strategy

### Backend Testing

- **Unit Tests**: Service classes and model methods
- **Feature Tests**: Controller endpoints and authorization
- **Integration Tests**: Database operations and file uploads
- **Policy Tests**: Authorization logic verification

### Frontend Testing

- **Component Tests**: Individual component functionality
- **Integration Tests**: Form submission and data flow
- **Accessibility Tests**: WCAG compliance verification
- **User Flow Tests**: End-to-end project management workflows

## Security Considerations

### Access Control

- **Role-Based Authorization**: Tenant admin and system admin permissions
- **Resource-Level Security**: Project ownership and visibility checks
- **Cross-Tenant Protection**: Prevent unauthorized access to other tenant data
- **API Security**: Request validation and rate limiting

### Data Protection

- **Input Validation**: Comprehensive server-side validation
- **File Upload Security**: Image type and size validation
- **SQL Injection Prevention**: Eloquent ORM usage
- **XSS Protection**: Output escaping and CSP headers

### Audit Trail

- **Operation Logging**: All CRUD operations logged via AuditLogService
- **User Tracking**: Track which user performed which actions
- **Change History**: Maintain history of project modifications
- **System Admin Actions**: Special logging for cross-tenant operations

## Performance Considerations

### Database Optimization

- **Indexing Strategy**: Optimized indexes for common queries
- **Query Optimization**: Eager loading and query scoping
- **Pagination**: Efficient pagination for large datasets
- **Caching**: Redis caching for frequently accessed data

### Frontend Performance

- **Code Splitting**: Lazy loading of project management components
- **Image Optimization**: Responsive images and lazy loading
- **State Management**: Efficient state updates and re-renders
- **Bundle Optimization**: Tree shaking and minification

### File Storage

- **Image Processing**: Automatic resizing and optimization
- **CDN Integration**: Fast image delivery
- **Storage Cleanup**: Automatic cleanup of unused images
- **Upload Progress**: Real-time upload progress indication