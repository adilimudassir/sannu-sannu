
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { TenantSwitcher } from '@/components/tenant-switcher';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import AppLogo from './app-logo';
import { getNavigationConfig } from '@/config/navigation';
import { getDashboardRoute, hasRole } from '@/lib/roles';
import { UserRole } from '@/enums';

export function AppSidebar() {
    const { auth, tenant, availableTenants } = usePage<SharedData>().props;
    const { user } = auth;
    
    // Use tenant data from shared props instead of parsing URL
    const tenantSlug = tenant?.slug;
    
    const dashboardHref = getDashboardRoute(user, tenantSlug);
    const navigationConfig = getNavigationConfig(user, tenantSlug);

    // Show tenant switcher only for tenant admins (not system admins)
    const showTenantSwitcher = hasRole(user, UserRole.TENANT_ADMIN) && !hasRole(user, UserRole.SYSTEM_ADMIN);

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboardHref} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                    {showTenantSwitcher && (
                        <SidebarMenuItem>
                            <TenantSwitcher 
                                availableTenants={availableTenants || []}
                                className="w-full"
                            />
                        </SidebarMenuItem>
                    )}
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={navigationConfig.main} />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
