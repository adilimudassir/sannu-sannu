<?php

namespace App\Enums;

enum Role: string
{
    // Global roles (stored in users.role)
    case SYSTEM_ADMIN = 'system_admin';
    case CONTRIBUTOR = 'contributor';
    
    // Tenant-specific roles (stored in user_tenant_roles.role)
    case TENANT_ADMIN = 'tenant_admin';
    case PROJECT_MANAGER = 'project_manager';
    
    /**
     * Get all global roles
     */
    public static function globalRoles(): array
    {
        return [
            self::SYSTEM_ADMIN,
            self::CONTRIBUTOR,
        ];
    }
    
    /**
     * Get all tenant-specific roles
     */
    public static function tenantRoles(): array
    {
        return [
            self::TENANT_ADMIN,
            self::PROJECT_MANAGER,
        ];
    }
    
    /**
     * Check if this role is a global role
     */
    public function isGlobalRole(): bool
    {
        return in_array($this, self::globalRoles());
    }
    
    /**
     * Check if this role is a tenant-specific role
     */
    public function isTenantRole(): bool
    {
        return in_array($this, self::tenantRoles());
    }
}
