import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link } from '@inertiajs/react';
import { AlertCircle, Building2, Eye, FolderOpen, Plus, Settings, TrendingUp, Users } from 'lucide-react';
import { formatCurrency, formatDateShort } from '@/lib/formatters';

interface SystemAdminDashboardProps {
    stats: {
        total_tenants: number;
        total_users: number;
        total_projects: number;
        active_projects: number;
        total_contributions: number;
        platform_revenue: number;
    };
    recent_tenants: Array<{
        id: number;
        name: string;
        slug: string;
        created_at: string;
        projects_count: number;
        users_count: number;
    }>;
    recent_projects: Array<{
        id: number;
        name: string;
        tenant_name: string;
        status: string;
        total_amount: number;
        current_amount: number;
        created_at: string;
    }>;
    alerts: Array<{
        id: number;
        type: 'warning' | 'error' | 'info';
        message: string;
        created_at: string;
    }>;
}

export default function SystemAdminDashboard({ 
    stats = {
        total_tenants: 0,
        total_users: 0,
        total_projects: 0,
        active_projects: 0,
        total_contributions: 0,
        platform_revenue: 0,
    }, 
    recent_tenants = [], 
    recent_projects = [], 
    alerts = [] 
}: SystemAdminDashboardProps) {


    return (
        <>
            <Head title="System Admin Dashboard" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold">System Admin Dashboard</h1>
                        <p className="text-muted-foreground">Platform overview and management</p>
                    </div>
                    <div className="flex gap-2">
                        <Button asChild>
                            <Link href="/admin/tenants/create">
                                <Plus className="mr-2 h-4 w-4" />
                                New Tenant
                            </Link>
                        </Button>
                        <Button variant="outline" asChild>
                            <Link href="/admin/settings">
                                <Settings className="mr-2 h-4 w-4" />
                                Platform Settings
                            </Link>
                        </Button>
                    </div>
                </div>

                {/* Stats Cards */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Tenants</CardTitle>
                            <Building2 className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.total_tenants}</div>
                            <p className="text-xs text-muted-foreground">Organizations on platform</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Users</CardTitle>
                            <Users className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.total_users}</div>
                            <p className="text-xs text-muted-foreground">Registered users</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Active Projects</CardTitle>
                            <FolderOpen className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.active_projects}</div>
                            <p className="text-xs text-muted-foreground">of {stats.total_projects} total projects</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Platform Revenue</CardTitle>
                            <TrendingUp className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{formatCurrency(stats.platform_revenue)}</div>
                            <p className="text-xs text-muted-foreground">Total contributions</p>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid gap-6 md:grid-cols-2">
                    {/* Recent Tenants */}
                    <Card>
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <CardTitle>Recent Tenants</CardTitle>
                                <Button variant="outline" size="sm" asChild>
                                    <Link href="/admin/tenants">
                                        <Eye className="mr-2 h-4 w-4" />
                                        View All
                                    </Link>
                                </Button>
                            </div>
                            <CardDescription>Recently registered organizations</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {recent_tenants.map((tenant) => (
                                    <div key={tenant.id} className="flex items-center justify-between">
                                        <div>
                                            <p className="font-medium">{tenant.name}</p>
                                            <p className="text-sm text-muted-foreground">
                                                {tenant.projects_count} projects • {tenant.users_count} users
                                            </p>
                                        </div>
                                        <div className="text-right">
                                            <p className="text-sm text-muted-foreground">{formatDateShort(tenant.created_at)}</p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Recent Projects */}
                    <Card>
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <CardTitle>Recent Projects</CardTitle>
                                <Button variant="outline" size="sm" asChild>
                                    <Link href="/admin/projects">
                                        <Eye className="mr-2 h-4 w-4" />
                                        View All
                                    </Link>
                                </Button>
                            </div>
                            <CardDescription>Latest project activity</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {recent_projects.map((project) => (
                                    <div key={project.id} className="flex items-center justify-between">
                                        <div>
                                            <p className="font-medium">{project.name}</p>
                                            <p className="text-sm text-muted-foreground">by {project.tenant_name}</p>
                                        </div>
                                        <div className="text-right">
                                            <Badge variant="secondary">{project.status}</Badge>
                                            <p className="mt-1 text-sm text-muted-foreground">
                                                {formatCurrency(project.current_amount)} / {formatCurrency(project.total_amount)}
                                            </p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* System Alerts */}
                {alerts.length > 0 && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <AlertCircle className="h-5 w-5" />
                                System Alerts
                            </CardTitle>
                            <CardDescription>Important notifications and warnings</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-3">
                                {alerts.map((alert) => (
                                    <div key={alert.id} className="flex items-start gap-3 rounded-lg border p-3">
                                        <AlertCircle
                                            className={`mt-0.5 h-4 w-4 ${
                                                alert.type === 'error'
                                                    ? 'text-red-500'
                                                    : alert.type === 'warning'
                                                      ? 'text-yellow-500'
                                                      : 'text-blue-500'
                                            }`}
                                        />
                                        <div className="flex-1">
                                            <p className="text-sm">{alert.message}</p>
                                            <p className="mt-1 text-xs text-muted-foreground">{formatDateShort(alert.created_at)}</p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                )}
            </div>
        </>
    );
}
