
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { LayoutGrid, FolderOpen, Users, Settings, Globe, Building2, Shield } from 'lucide-react';
import AppLogo from './app-logo';

export function AppSidebar() {
    const { auth } = usePage<SharedData>().props;
    const user = auth.user;

    // Base navigation items for all authenticated users
    const baseNavItems: NavItem[] = [
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
    ];

    // Additional navigation items based on user roles
    const getNavItems = (): NavItem[] => {
        const items = [...baseNavItems];

        // Check if user has system admin role
        if (user.is_system_admin) {
            items.push(
                {
                    title: 'System Admin',
                    href: '/admin',
                    icon: Shield,
                },
                {
                    title: 'All Projects',
                    href: '/admin/projects',
                    icon: FolderOpen,
                },
                {
                    title: 'Tenants',
                    href: '/admin/tenants',
                    icon: Building2,
                },
                {
                    title: 'Users',
                    href: '/admin/users',
                    icon: Users,
                }
            );
        }

        // Check if user has tenant admin role
        if (user.is_tenant_admin) {
            items.push(
                {
                    title: 'My Projects',
                    href: '/tenant/projects',
                    icon: FolderOpen,
                },
                {
                    title: 'Tenant Settings',
                    href: '/tenant/settings',
                    icon: Settings,
                }
            );
        }

        return items;
    };

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href="/dashboard" prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={getNavItems()} />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
