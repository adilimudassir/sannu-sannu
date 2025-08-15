export enum Role {
    SYSTEM_ADMIN = 'system_admin',
    CONTRIBUTOR = 'contributor',
    TENANT_ADMIN = 'tenant_admin',
    PROJECT_MANAGER = 'project_manager',
}

export enum UserRole {
    SYSTEM_ADMIN = 'system_admin',
    TENANT_ADMIN = 'tenant_admin',
    CONTRIBUTOR = 'contributor',
}

export const ROLE_PERMISSIONS = {
    [Role.SYSTEM_ADMIN]: {
        canManagePlatform: true,
        canManageAllTenants: true,
        canManageAllProjects: true,
        canManageAllUsers: true,
        canViewSystemAnalytics: true,
    },
    [Role.TENANT_ADMIN]: {
        canManageTenant: true,
        canManageTenantProjects: true,
        canManageTenantUsers: true,
        canViewTenantAnalytics: true,
        canInviteUsers: true,
    },
    [Role.PROJECT_MANAGER]: {
        canManageAssignedProjects: true,
        canViewProjectAnalytics: true,
        canManageProjectContributors: true,
    },
    [Role.CONTRIBUTOR]: {
        canContributeToProjects: true,
        canViewOwnContributions: true,
        canManageOwnProfile: true,
    },
} as const;
