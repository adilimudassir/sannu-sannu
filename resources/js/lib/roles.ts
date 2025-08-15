import { Role, ROLE_PERMISSIONS, UserRole } from '@/enums';
import type { User } from '@/types';

/**
 * Check if user has a specific role
 */
export function hasRole(user: User, role: Role | UserRole): boolean {
    switch (role) {
        case Role.SYSTEM_ADMIN:
        case UserRole.SYSTEM_ADMIN:
            return Boolean(user.is_system_admin);
        case Role.TENANT_ADMIN:
        case UserRole.TENANT_ADMIN:
            return Boolean(user.is_tenant_admin);
        case Role.CONTRIBUTOR:
        case UserRole.CONTRIBUTOR:
            return Boolean(user.is_contributor);
        case Role.PROJECT_MANAGER:
            // This would need to be checked against user's tenant roles
            return Boolean(user.role === Role.PROJECT_MANAGER);
        default:
            return false;
    }
}

/**
 * Check if user has any of the specified roles
 */
export function hasAnyRole(user: User, roles: (Role | UserRole)[]): boolean {
    return roles.some((role) => hasRole(user, role));
}

/**
 * Check if user has all of the specified roles
 */
export function hasAllRoles(user: User, roles: (Role | UserRole)[]): boolean {
    return roles.every((role) => hasRole(user, role));
}

/**
 * Get user's primary role for display purposes
 */
export function getPrimaryRole(user: User): UserRole {
    if (user.is_system_admin) return UserRole.SYSTEM_ADMIN;
    if (user.is_tenant_admin) return UserRole.TENANT_ADMIN;
    return UserRole.CONTRIBUTOR;
}

/**
 * Get user's role display name
 */
export function getRoleDisplayName(role: Role | UserRole): string {
    switch (role) {
        case Role.SYSTEM_ADMIN:
        case UserRole.SYSTEM_ADMIN:
            return 'System Administrator';
        case Role.TENANT_ADMIN:
        case UserRole.TENANT_ADMIN:
            return 'Organization Admin';
        case Role.PROJECT_MANAGER:
            return 'Project Manager';
        case Role.CONTRIBUTOR:
        case UserRole.CONTRIBUTOR:
            return 'Contributor';
        default:
            return 'User';
    }
}

/**
 * Check if user has specific permission
 */
export function hasPermission(user: User, permission: keyof (typeof ROLE_PERMISSIONS)[Role]): boolean {
    const userRole = getPrimaryRole(user);
    const roleKey = userRole as keyof typeof ROLE_PERMISSIONS;

    if (roleKey in ROLE_PERMISSIONS) {
        const permissions = ROLE_PERMISSIONS[roleKey as Role];
        return Boolean(permissions[permission as keyof typeof permissions]);
    }

    return false;
}

/**
 * Get dashboard route based on user role and tenant context
 */
export function getDashboardRoute(user: User, tenantSlug?: string): string {
    if (hasRole(user, UserRole.SYSTEM_ADMIN)) {
        return '/admin/dashboard';
    }
    if (hasRole(user, UserRole.TENANT_ADMIN)) {
        if (tenantSlug) {
            return `/${tenantSlug}/dashboard`;
        }
        return '/select-tenant';
    }
    return '/dashboard';
}

/**
 * Check if user can access admin routes
 */
export function canAccessAdminRoutes(user: User): boolean {
    return hasRole(user, UserRole.SYSTEM_ADMIN);
}

/**
 * Check if user can access tenant routes
 */
export function canAccessTenantRoutes(user: User): boolean {
    return hasAnyRole(user, [UserRole.SYSTEM_ADMIN, UserRole.TENANT_ADMIN]);
}

/**
 * Check if user can manage projects
 */
export function canManageProjects(user: User): boolean {
    return hasAnyRole(user, [UserRole.SYSTEM_ADMIN, UserRole.TENANT_ADMIN]);
}
