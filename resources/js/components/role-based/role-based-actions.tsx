import { usePage, Link } from '@inertiajs/react';
import { type SharedData } from '@/types';
import { UserRole } from '@/enums';
import { hasRole, canManageProjects } from '@/lib/roles';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Plus, Settings, Users, Building2, BarChart3, MoreVertical } from 'lucide-react';

interface RoleBasedActionsProps {
    context?: 'header' | 'page' | 'card';
    resource?: 'project' | 'tenant' | 'user';
    resourceId?: number | string;
    className?: string;
}

export default function RoleBasedActions({ 
    context = 'header', 
    resource, 
    resourceId,
    className 
}: RoleBasedActionsProps) {
    const { auth, tenant } = usePage<SharedData>().props;
    const { user } = auth;
    
    const isSystemAdmin = hasRole(user, UserRole.SYSTEM_ADMIN);
    const isTenantAdmin = hasRole(user, UserRole.TENANT_ADMIN);
    const canManage = canManageProjects(user);
    
    // Get actions based on user role and context
    const getActions = () => {
        const actions = [];
        
        if (isSystemAdmin) {
            switch (context) {
                case 'header':
                    actions.push(
                        { title: 'New Organization', href: '/admin/tenants/create', icon: Building2 },
                        { title: 'Platform Settings', href: '/admin/settings', icon: Settings },
                        { title: 'System Analytics', href: '/admin/analytics', icon: BarChart3 }
                    );
                    break;
                case 'page':
                    if (resource === 'project') {
                        actions.push(
                            { title: 'Create Project', href: '/admin/projects/create', icon: Plus },
                            { title: 'Manage Users', href: '/admin/users', icon: Users }
                        );
                    }
                    break;
            }
        } else if (isTenantAdmin) {
            const baseUrl = tenant?.slug ? `/tenant/${tenant.slug}` : '/tenant';
            
            switch (context) {
                case 'header':
                    actions.push(
                        { title: 'New Project', href: `${baseUrl}/projects/create`, icon: Plus },
                        { title: 'Organization Settings', href: `${baseUrl}/settings`, icon: Settings },
                        { title: 'Analytics', href: `${baseUrl}/analytics`, icon: BarChart3 }
                    );
                    break;
                case 'page':
                    if (resource === 'project') {
                        actions.push(
                            { title: 'Create Project', href: `${baseUrl}/projects/create`, icon: Plus },
                            { title: 'Invite Users', href: `${baseUrl}/invitations/create`, icon: Users }
                        );
                    }
                    break;
            }
        } else {
            // Contributor actions
            switch (context) {
                case 'header':
                    actions.push(
                        { title: 'Browse Projects', href: '/projects', icon: Plus },
                        { title: 'My Contributions', href: '/contributions', icon: BarChart3 }
                    );
                    break;
            }
        }
        
        return actions;
    };
    
    const actions = getActions();
    
    if (actions.length === 0) {
        return null;
    }
    
    // For single action, show as button
    if (actions.length === 1) {
        const action = actions[0];
        return (
            <Button asChild className={className}>
                <Link href={action.href}>
                    <action.icon className="mr-2 h-4 w-4" />
                    {action.title}
                </Link>
            </Button>
        );
    }
    
    // For multiple actions, show as dropdown
    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button variant="outline" className={className}>
                    <MoreVertical className="h-4 w-4" />
                    <span className="sr-only">Actions</span>
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
                <DropdownMenuLabel>Quick Actions</DropdownMenuLabel>
                <DropdownMenuSeparator />
                {actions.map((action, index) => (
                    <DropdownMenuItem key={index} asChild>
                        <Link href={action.href}>
                            <action.icon className="mr-2 h-4 w-4" />
                            {action.title}
                        </Link>
                    </DropdownMenuItem>
                ))}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}