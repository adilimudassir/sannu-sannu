<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'avatar_url',
        'bio',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'role' => Role::class,
        ];
    }

    public function createdProjects()
    {
        return $this->hasMany(Project::class, 'created_by');
    }

    public function contributions()
    {
        return $this->hasMany(Contribution::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function invitations()
    {
        return $this->hasMany(ProjectInvitation::class, 'invited_by');
    }

    /**
     * User's tenant roles (many-to-many through pivot)
     */
    public function tenantRoles(): HasMany
    {
        return $this->hasMany(UserTenantRole::class);
    }

    /**
     * Tenants where user has roles
     */
    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'user_tenant_roles')
            ->withPivot('role', 'is_active')
            ->withTimestamps()
            ->wherePivot('is_active', true);
    }

    /**
     * Check if the user has a specific role
     */
    public function hasRole(Role $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if the user has any of the specified roles
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    /**
     * Check if the user is a system admin
     */
    public function isSystemAdmin(): bool
    {
        return $this->hasRole(Role::SYSTEM_ADMIN);
    }

    /**
     * Check if the user is a tenant admin (in any tenant)
     */
    public function isTenantAdmin(): bool
    {
        return $this->hasAnyTenantRole([Role::TENANT_ADMIN]);
    }

    /**
     * Check if the user is a project manager (in any tenant)
     */
    public function isProjectManager(): bool
    {
        return $this->hasAnyTenantRole([Role::PROJECT_MANAGER]);
    }

    /**
     * Check if the user is a contributor
     */
    public function isContributor(): bool
    {
        return $this->hasRole(Role::CONTRIBUTOR);
    }

    /**
     * Check if the user can manage projects (system admin or has tenant admin/project manager roles)
     */
    public function canManageProjects(): bool
    {
        return $this->isSystemAdmin() || $this->hasAnyTenantRole([Role::TENANT_ADMIN, Role::PROJECT_MANAGER]);
    }

    /**
     * Check if the user can manage tenant settings (system admin or has tenant admin role)
     */
    public function canManageTenant(): bool
    {
        return $this->isSystemAdmin() || $this->hasAnyTenantRole([Role::TENANT_ADMIN]);
    }

    /**
     * Check if the user can manage the platform (system admin only)
     */
    public function canManagePlatform(): bool
    {
        return $this->isSystemAdmin();
    }

    /**
     * Check if the user has admin privileges (system admin or has tenant admin roles)
     */
    public function isAdmin(): bool
    {
        return $this->isSystemAdmin() || $this->hasAnyTenantRole([Role::TENANT_ADMIN]);
    }

    /**
     * Check if user has any tenant-specific roles
     */
    public function hasAnyTenantRole(?array $roles = null): bool
    {
        $query = $this->tenantRoles()->active();
        
        if ($roles) {
            $query->whereIn('role', $roles);
        }
        
        return $query->exists();
    }

    /**
     * Check if user has a specific role in a specific tenant
     */
    public function hasRoleInTenant(Role $role, int $tenantId): bool
    {
        return $this->tenantRoles()
            ->where('tenant_id', $tenantId)
            ->where('role', $role)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Get user's role in a specific tenant
     */
    public function getRoleInTenant(int $tenantId): ?Role
    {
        $tenantRole = $this->tenantRoles()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->first();

        return $tenantRole?->role;
    }

    /**
     * Get all tenants where user has admin roles (tenant_admin or project_manager)
     */
    public function getAdminTenants()
    {
        return $this->tenants()
            ->wherePivotIn('role', [Role::TENANT_ADMIN->value, Role::PROJECT_MANAGER->value])
            ->get();
    }

    /**
     * Check if user needs tenant selection (has admin roles in any tenant)
     */
    public function needsTenantSelection(): bool
    {
        return $this->hasAnyTenantRole([Role::TENANT_ADMIN, Role::PROJECT_MANAGER]);
    }

    /**
     * Scope query to users with specific role
     */
    public function scopeWithRole(Builder $query, Role $role): Builder
    {
        return $query->where('role', $role);
    }

    /**
     * Scope query to active users only
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope query to users with roles in a specific tenant
     */
    public function scopeForTenant(Builder $query, int $tenantId): Builder
    {
        return $query->whereHas('tenantRoles', function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId)->where('is_active', true);
        });
    }
}
