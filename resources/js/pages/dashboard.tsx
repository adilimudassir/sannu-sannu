import AppLayout from '@/layouts/app-layout';
import DashboardRouter from '@/components/role-based/dashboard-router';

interface DashboardProps {
    // Props that might be passed from different dashboard controllers
    stats?: any;
    recent_projects?: any[];
    recent_tenants?: any[];
    top_contributors?: any[];
    alerts?: any[];
    publicProjects?: any[];
    myContributions?: any[];
    tenant?: any;
}

export default function Dashboard(props: DashboardProps) {
    return (
        <AppLayout>
            <DashboardRouter {...props} />
        </AppLayout>
    );
}
