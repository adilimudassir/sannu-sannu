import { 
    LayoutGrid, 
    FolderOpen, 
    Users, 
    Settings, 
    Globe, 
    Building2, 
    Shield,
    TrendingUp,
    UserPlus,
    Heart,
    Search,
    Bell,
    CreditCard,
    FileText,
    BarChart3,
    Database,
    Lock,
    Zap,
    Plus
} from 'lucide-react';
import type { NavItem, User } from '@/types';
import { UserRole } from '@/enums';
import { hasRole } from '@/lib/roles';

export interface NavigationConfig {
    main: NavItem[];
    settings: NavItem[];
    quick?: NavItem[];
}

/**
 * Get navigation configuration based on user role
 */
export function getNavigationConfig(user: User, tenantSlug?: string): NavigationConfig {
    if (hasRole(user, UserRole.SYSTEM_ADMIN)) {
        return getSystemAdminNavigation();
    }
    
    if (hasRole(user, UserRole.TENANT_ADMIN)) {
        return getTenantAdminNavigation(tenantSlug);
    }
    
    return getContributorNavigation();
}

/**
 * System Admin Navigation
 */
function getSystemAdminNavigation(): NavigationConfig {
    return {
        main: [
            {
                title: 'Dashboard',
                href: '/admin/dashboard',
                icon: Shield,
            },
            {
                title: 'Organizations',
                href: '/admin/tenants',
                icon: Building2,
            },
            {
                title: 'Projects',
                href: '/admin/projects',
                icon: FolderOpen,
            },
            {
                title: 'Users',
                href: '/admin/users',
                icon: Users,
            },
            {
                title: 'Analytics',
                href: '/admin/analytics',
                icon: BarChart3,
            },
            {
                title: 'Platform Fees',
                href: '/admin/platform-fees',
                icon: CreditCard,
            },
        ],
        settings: [
            {
                title: 'Platform Settings',
                href: '/admin/settings',
                icon: Settings,
            },
            {
                title: 'System Logs',
                href: '/admin/logs',
                icon: FileText,
            },
            {
                title: 'Database',
                href: '/admin/database',
                icon: Database,
            },
            {
                title: 'Security',
                href: '/admin/security',
                icon: Lock,
            },
        ],
        quick: [
            {
                title: 'Create Organization',
                href: '/admin/tenants/create',
                icon: Plus,
            },
            {
                title: 'System Health',
                href: '/admin/health',
                icon: Zap,
            },
        ],
    };
}

/**
 * Tenant Admin Navigation
 */
function getTenantAdminNavigation(tenantSlug?: string): NavigationConfig {
    // If no tenant slug, return empty navigation (user needs to select tenant)
    if (!tenantSlug) {
        return {
            main: [
                {
                    title: 'Select Organization',
                    href: '/select-tenant',
                    icon: Building2,
                },
            ],
            settings: [],
        };
    }
    
    // Use the tenant slug directly as per the route structure
    const baseUrl = `/${tenantSlug}`;
    
    return {
        main: [
            {
                title: 'Dashboard',
                href: `${baseUrl}/dashboard`,
                icon: LayoutGrid,
            },
            {
                title: 'Projects',
                href: `${baseUrl}/projects`,
                icon: FolderOpen,
            },
            {
                title: 'Contributors',
                href: `${baseUrl}/contributors`,
                icon: Users,
            },
            {
                title: 'Analytics',
                href: `${baseUrl}/analytics`,
                icon: TrendingUp,
            },
            {
                title: 'Invitations',
                href: `${baseUrl}/invitations`,
                icon: UserPlus,
            },
            {
                title: 'Transactions',
                href: `${baseUrl}/transactions`,
                icon: CreditCard,
            },
        ],
        settings: [
            {
                title: 'Organization Settings',
                href: `${baseUrl}/settings`,
                icon: Settings,
            },
            {
                title: 'Team Management',
                href: `${baseUrl}/team`,
                icon: Users,
            },
            {
                title: 'Billing',
                href: `${baseUrl}/billing`,
                icon: CreditCard,
            },
            {
                title: 'Notifications',
                href: `${baseUrl}/notifications`,
                icon: Bell,
            },
        ],
        quick: [
            {
                title: 'Create Project',
                href: `${baseUrl}/projects/create`,
                icon: Plus,
            },
            {
                title: 'Invite Users',
                href: `${baseUrl}/invitations/create`,
                icon: UserPlus,
            },
        ],
    };
}

/**
 * Contributor Navigation
 */
function getContributorNavigation(): NavigationConfig {
    return {
        main: [
            {
                title: 'Dashboard',
                href: '/dashboard',
                icon: LayoutGrid,
            },
            {
                title: 'Projects',
                href: '/projects',
                icon: Globe,
            },
            {
                title: 'My Contributions',
                href: '/contributions',
                icon: Heart,
            },
            {
                title: 'Search',
                href: '/projects/search',
                icon: Search,
            },
        ],
        settings: [
            {
                title: 'Profile',
                href: '/settings/profile',
                icon: Settings,
            },
            {
                title: 'Payment Methods',
                href: '/settings/payments',
                icon: CreditCard,
            },
            {
                title: 'Notifications',
                href: '/settings/notifications',
                icon: Bell,
            },
            {
                title: 'Privacy',
                href: '/settings/privacy',
                icon: Lock,
            },
        ],
        quick: [
            {
                title: 'Browse Projects',
                href: '/projects',
                icon: Globe,
            },
            {
                title: 'View Contributions',
                href: '/contributions',
                icon: Heart,
            },
        ],
    };
}

/**
 * Get breadcrumbs for a given route
 */
export function getBreadcrumbs(pathname: string, user: User, tenantSlug?: string): Array<{ title: string; href: string }> {
    const segments = pathname.split('/').filter(Boolean);
    const breadcrumbs: Array<{ title: string; href: string }> = [];
    
    // Add dashboard as root
    if (hasRole(user, UserRole.SYSTEM_ADMIN)) {
        breadcrumbs.push({ title: 'Admin Dashboard', href: '/admin/dashboard' });
    } else if (hasRole(user, UserRole.TENANT_ADMIN)) {
        const dashboardHref = tenantSlug ? `/${tenantSlug}/dashboard` : '/select-tenant';
        breadcrumbs.push({ title: 'Dashboard', href: dashboardHref });
    } else {
        breadcrumbs.push({ title: 'Dashboard', href: '/dashboard' });
    }
    
    // Build breadcrumbs based on segments
    let currentPath = '';
    for (let i = 0; i < segments.length; i++) {
        const segment = segments[i];
        currentPath += `/${segment}`;
        
        // Skip certain segments
        if (['admin', 'tenant'].includes(segment)) continue;
        
        // Add meaningful breadcrumb titles
        const title = getBreadcrumbTitle(segment);
        if (title) {
            breadcrumbs.push({ title, href: currentPath });
        }
    }
    
    return breadcrumbs;
}

function getBreadcrumbTitle(segment: string): string | null {
    const titleMap: Record<string, string> = {
        'projects': 'Projects',
        'users': 'Users',
        'tenants': 'Organizations',
        'settings': 'Settings',
        'analytics': 'Analytics',
        'contributions': 'Contributions',
        'invitations': 'Invitations',
        'transactions': 'Transactions',
        'create': 'Create',
        'edit': 'Edit',
        'show': 'Details',
    };
    
    return titleMap[segment] || null;
}