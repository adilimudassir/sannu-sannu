import { usePage } from '@inertiajs/react';
import { type SharedData } from '@/types';
import { UserRole } from '@/enums';
import { hasRole } from '@/lib/roles';
import SystemAdminDashboard from '@/pages/dashboard/system-admin';
import TenantAdminDashboard from '@/pages/dashboard/tenant-admin';
import GlobalDashboard from '@/pages/dashboard/global';

interface DashboardRouterProps {
    // Props that might be passed from the controller
    stats?: any;
    recent_projects?: any[];
    recent_tenants?: any[];
    top_contributors?: any[];
    alerts?: any[];
    publicProjects?: any[];
    myContributions?: any[];
    tenant?: any;
}

export default function DashboardRouter(props: DashboardRouterProps) {
    const { auth } = usePage<SharedData>().props;
    const { user } = auth;
    
    // Route to appropriate dashboard based on user role
    if (hasRole(user, UserRole.SYSTEM_ADMIN)) {
        return (
            <SystemAdminDashboard
                stats={props.stats}
                recent_tenants={props.recent_tenants || []}
                recent_projects={props.recent_projects || []}
                alerts={props.alerts || []}
            />
        );
    }
    
    if (hasRole(user, UserRole.TENANT_ADMIN)) {
        return (
            <TenantAdminDashboard
                tenant={props.tenant}
                stats={props.stats}
                recent_projects={props.recent_projects || []}
                top_contributors={props.top_contributors || []}
            />
        );
    }
    
    // Default to contributor dashboard
    return (
        <GlobalDashboard
            publicProjects={props.publicProjects || []}
            myContributions={props.myContributions || []}
            stats={props.stats}
        />
    );
}