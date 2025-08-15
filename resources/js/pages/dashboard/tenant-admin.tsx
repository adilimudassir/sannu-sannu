import { Head, Link } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Progress } from '@/components/ui/progress';
import { 
    FolderOpen, 
    Users, 
    TrendingUp, 
    Calendar,
    Plus,
    Eye,
    Settings,
    DollarSign
} from 'lucide-react';
import { formatCurrency } from '@/lib/formatters';

interface TenantAdminDashboardProps {
    tenant: {
        id: number;
        name: string;
        slug: string;
    };
    stats: {
        total_projects: number;
        active_projects: number;
        draft_projects: number;
        completed_projects: number;
        total_contributors: number;
        total_raised: number;
        total_target: number;
    };
    recent_projects: Array<{
        id: number;
        name: string;
        status: string;
        total_amount: number;
        current_amount: number;
        contributors_count: number;
        days_remaining: number;
        created_at: string;
    }>;
    top_contributors: Array<{
        id: number;
        name: string;
        email: string;
        total_contributed: number;
        projects_count: number;
    }>;
}

export default function TenantAdminDashboard({ 
    tenant,
    stats = {
        total_projects: 0,
        active_projects: 0,
        draft_projects: 0,
        completed_projects: 0,
        total_contributors: 0,
        total_raised: 0,
        total_target: 0,
    }, 
    recent_projects = [], 
    top_contributors = [] 
}: TenantAdminDashboardProps) {
    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        }).format(amount);
    };

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
        });
    };

    const getProgressPercentage = (current: number, target: number) => {
        return target > 0 ? Math.round((current / target) * 100) : 0;
    };

    return (
        <>
            <Head title={`${tenant.name} - Admin Dashboard`} />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold">{tenant.name}</h1>
                        <p className="text-muted-foreground">
                            Organization dashboard and management
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <Button asChild>
                            <Link href="/tenant/projects/create">
                                <Plus className="h-4 w-4 mr-2" />
                                New Project
                            </Link>
                        </Button>
                        <Button variant="outline" asChild>
                            <Link href="/tenant/settings">
                                <Settings className="h-4 w-4 mr-2" />
                                Settings
                            </Link>
                        </Button>
                    </div>
                </div>

                {/* Stats Cards */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Active Projects</CardTitle>
                            <FolderOpen className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.active_projects}</div>
                            <p className="text-xs text-muted-foreground">
                                of {stats.total_projects} total projects
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Contributors</CardTitle>
                            <Users className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.total_contributors}</div>
                            <p className="text-xs text-muted-foreground">
                                Supporting your projects
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Raised</CardTitle>
                            <DollarSign className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{formatCurrency(stats.total_raised)}</div>
                            <p className="text-xs text-muted-foreground">
                                {getProgressPercentage(stats.total_raised, stats.total_target)}% of target
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Completion Rate</CardTitle>
                            <TrendingUp className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {stats.total_projects > 0 ? Math.round((stats.completed_projects / stats.total_projects) * 100) : 0}%
                            </div>
                            <p className="text-xs text-muted-foreground">
                                {stats.completed_projects} completed projects
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Overall Progress */}
                <Card>
                    <CardHeader>
                        <CardTitle>Organization Progress</CardTitle>
                        <CardDescription>
                            Overall funding progress across all projects
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-2">
                            <div className="flex justify-between text-sm">
                                <span>Total Raised</span>
                                <span>{getProgressPercentage(stats.total_raised, stats.total_target)}%</span>
                            </div>
                            <Progress value={getProgressPercentage(stats.total_raised, stats.total_target)} />
                            <div className="flex justify-between text-sm text-muted-foreground">
                                <span>{formatCurrency(stats.total_raised)} raised</span>
                                <span>of {formatCurrency(stats.total_target)} target</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <div className="grid gap-6 md:grid-cols-2">
                    {/* Recent Projects */}
                    <Card>
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <CardTitle>Recent Projects</CardTitle>
                                <Button variant="outline" size="sm" asChild>
                                    <Link href="/tenant/projects">
                                        <Eye className="h-4 w-4 mr-2" />
                                        View All
                                    </Link>
                                </Button>
                            </div>
                            <CardDescription>
                                Latest project activity
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {recent_projects.map((project) => (
                                    <div key={project.id} className="space-y-2">
                                        <div className="flex items-center justify-between">
                                            <div>
                                                <p className="font-medium">{project.name}</p>
                                                <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                                    <Users className="h-3 w-3" />
                                                    {project.contributors_count} contributors
                                                    <Calendar className="h-3 w-3 ml-2" />
                                                    {project.days_remaining} days left
                                                </div>
                                            </div>
                                            <Badge variant="secondary">
                                                {project.status}
                                            </Badge>
                                        </div>
                                        <div className="space-y-1">
                                            <Progress 
                                                value={getProgressPercentage(project.current_amount, project.total_amount)} 
                                                className="h-2"
                                            />
                                            <div className="flex justify-between text-xs text-muted-foreground">
                                                <span>{formatCurrency(project.current_amount)}</span>
                                                <span>{formatCurrency(project.total_amount)}</span>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Top Contributors */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Top Contributors</CardTitle>
                            <CardDescription>
                                Most active supporters of your projects
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {top_contributors.map((contributor, index) => (
                                    <div key={contributor.id} className="flex items-center justify-between">
                                        <div className="flex items-center gap-3">
                                            <div className="flex h-8 w-8 items-center justify-center rounded-full bg-primary text-primary-foreground text-sm font-medium">
                                                {index + 1}
                                            </div>
                                            <div>
                                                <p className="font-medium">{contributor.name}</p>
                                                <p className="text-sm text-muted-foreground">
                                                    {contributor.projects_count} projects supported
                                                </p>
                                            </div>
                                        </div>
                                        <div className="text-right">
                                            <p className="font-medium">{formatCurrency(contributor.total_contributed)}</p>
                                            <p className="text-sm text-muted-foreground">contributed</p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Quick Actions */}
                <Card>
                    <CardHeader>
                        <CardTitle>Quick Actions</CardTitle>
                        <CardDescription>
                            Common management tasks
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 md:grid-cols-3">
                            <Button variant="outline" asChild className="h-auto p-4">
                                <Link href="/tenant/projects/create">
                                    <div className="text-center">
                                        <Plus className="h-6 w-6 mx-auto mb-2" />
                                        <p className="font-medium">Create Project</p>
                                        <p className="text-sm text-muted-foreground">Start a new funding campaign</p>
                                    </div>
                                </Link>
                            </Button>
                            
                            <Button variant="outline" asChild className="h-auto p-4">
                                <Link href="/tenant/contributors">
                                    <div className="text-center">
                                        <Users className="h-6 w-6 mx-auto mb-2" />
                                        <p className="font-medium">Manage Contributors</p>
                                        <p className="text-sm text-muted-foreground">View and engage supporters</p>
                                    </div>
                                </Link>
                            </Button>
                            
                            <Button variant="outline" asChild className="h-auto p-4">
                                <Link href="/tenant/analytics">
                                    <div className="text-center">
                                        <TrendingUp className="h-6 w-6 mx-auto mb-2" />
                                        <p className="font-medium">View Analytics</p>
                                        <p className="text-sm text-muted-foreground">Track performance metrics</p>
                                    </div>
                                </Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}