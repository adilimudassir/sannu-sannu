import AppLayout from '@/layouts/app-layout';
import SystemAdminDashboard from '@/pages/dashboard/system-admin';

interface AdminDashboardProps {
    stats?: {
        total_tenants: number;
        total_users: number;
        total_projects: number;
        active_projects: number;
        total_contributions: number;
        platform_revenue: number;
    };
    recent_tenants?: Array<{
        id: number;
        name: string;
        slug: string;
        created_at: string;
        projects_count: number;
        users_count: number;
    }>;
    recent_projects?: Array<{
        id: number;
        name: string;
        tenant_name: string;
        status: string;
        total_amount: number;
        current_amount: number;
        created_at: string;
    }>;
    alerts?: Array<{
        id: number;
        type: 'warning' | 'error' | 'info';
        message: string;
        created_at: string;
    }>;
}

export default function AdminDashboard(props: AdminDashboardProps) {
    return (
        <AppLayout>
            <SystemAdminDashboard
                stats={props.stats || {
                    total_tenants: 0,
                    total_users: 0,
                    total_projects: 0,
                    active_projects: 0,
                    total_contributions: 0,
                    platform_revenue: 0,
                }}
                recent_tenants={props.recent_tenants || []}
                recent_projects={props.recent_projects || []}
                alerts={props.alerts || []}
            />
        </AppLayout>
    );
}
