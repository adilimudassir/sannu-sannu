import { Head, Link } from '@inertiajs/react';
import { Search, Filter, Heart, Clock, Users, Globe, TrendingUp, Eye } from 'lucide-react';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import { formatCurrency, formatDateShort } from '@/lib/formatters';

interface GlobalDashboardProps {
    publicProjects?: Array<{
        id: number;
        name: string;
        slug: string;
        description: string;
        tenant: { name: string; slug: string };
        total_amount: number;
        current_amount: number;
        contributors_count: number;
        days_remaining: number;
        status: string;
        statistics: {
            completion_percentage: number;
        };
    }>;
    myContributions?: Array<{
        id: number;
        project_title: string;
        tenant_name: string;
        amount_contributed: number;
        next_payment_due: string;
        status: string;
    }>;
    stats?: {
        total_projects: number;
        total_raised: number;
        total_contributors: number;
    };
}

export default function GlobalDashboard({ 
    publicProjects = [], 
    myContributions = [], 
    stats 
}: GlobalDashboardProps) {

    return (
        <>
            <Head title="Dashboard" />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold">Welcome Back!</h1>
                        <p className="text-muted-foreground">
                            Discover amazing projects and make a difference
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <Button asChild>
                            <Link href="/projects">
                                <Globe className="h-4 w-4 mr-2" />
                                Browse All Projects
                            </Link>
                        </Button>
                        {myContributions.length > 0 && (
                            <Button variant="outline">
                                <Heart className="h-4 w-4 mr-2" />
                                My Contributions
                            </Button>
                        )}
                    </div>
                </div>

                {/* Platform Stats */}
                {stats && (
                    <div className="grid gap-4 md:grid-cols-3">
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Active Projects</CardTitle>
                                <Globe className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">{stats.total_projects}</div>
                                <p className="text-xs text-muted-foreground">
                                    Projects seeking funding
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Total Raised</CardTitle>
                                <TrendingUp className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">{formatCurrency(stats.total_raised)}</div>
                                <p className="text-xs text-muted-foreground">
                                    Community contributions
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
                                    People making a difference
                                </p>
                            </CardContent>
                        </Card>
                    </div>
                )}

                <div className="space-y-8">
                    {/* My Contributions Section */}
                    {myContributions.length > 0 && (
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Heart className="h-5 w-5" />
                                    My Active Contributions
                                </CardTitle>
                                <CardDescription>
                                    Projects you're currently supporting
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    {myContributions.map((contribution) => (
                                        <div key={contribution.id} className="flex items-center justify-between p-4 border rounded-lg">
                                            <div>
                                                <h3 className="font-semibold">{contribution.project_title}</h3>
                                                <p className="text-sm text-muted-foreground">
                                                    by {contribution.tenant_name}
                                                </p>
                                                <p className="text-sm mt-1">
                                                    Contributed: {formatCurrency(contribution.amount_contributed)}
                                                </p>
                                            </div>
                                            <div className="text-right">
                                                <Badge variant="outline">
                                                    Next payment: {formatDateShort(contribution.next_payment_due)}
                                                </Badge>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    )}

                    {/* Featured Projects Section */}
                    <Card>
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <div>
                                    <CardTitle className="flex items-center gap-2">
                                        <Globe className="h-5 w-5" />
                                        Featured Projects
                                    </CardTitle>
                                    <CardDescription>
                                        Discover amazing projects from the community
                                    </CardDescription>
                                </div>
                                <Button variant="outline" asChild>
                                    <Link href="/projects">
                                        <Eye className="h-4 w-4 mr-2" />
                                        View All
                                    </Link>
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent>
                            {publicProjects.length === 0 ? (
                                <div className="text-center py-8">
                                    <Globe className="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                                    <h3 className="text-lg font-medium mb-2">No projects available</h3>
                                    <p className="text-muted-foreground mb-4">
                                        Check back later for new projects to support
                                    </p>
                                    <Button asChild>
                                        <Link href="/projects">Browse All Projects</Link>
                                    </Button>
                                </div>
                            ) : (
                                <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                                    {publicProjects.slice(0, 6).map((project) => (
                                        <Card key={project.id} className="cursor-pointer hover:shadow-md transition-shadow">
                                            <CardHeader>
                                                <div className="flex items-start justify-between">
                                                    <div>
                                                        <CardTitle className="text-lg line-clamp-2">
                                                            <Link 
                                                                href={`/projects/${project.slug}`}
                                                                className="hover:text-primary transition-colors"
                                                            >
                                                                {project.name}
                                                            </Link>
                                                        </CardTitle>
                                                        <CardDescription className="text-sm text-muted-foreground">
                                                            by {project.tenant.name}
                                                        </CardDescription>
                                                    </div>
                                                    <Badge variant="secondary">{project.status}</Badge>
                                                </div>
                                            </CardHeader>
                                            <CardContent>
                                                <p className="text-sm text-muted-foreground mb-4 line-clamp-3">
                                                    {project.description}
                                                </p>
                                                
                                                {/* Progress Bar */}
                                                <div className="mb-4">
                                                    <div className="flex justify-between text-sm mb-2">
                                                        <span>{formatCurrency(project.current_amount)}</span>
                                                        <span>{project.statistics.completion_percentage}%</span>
                                                    </div>
                                                    <Progress value={project.statistics.completion_percentage} />
                                                    <div className="text-xs text-muted-foreground mt-1">
                                                        of {formatCurrency(project.total_amount)} target
                                                    </div>
                                                </div>

                                                {/* Stats */}
                                                <div className="flex items-center justify-between text-sm text-muted-foreground mb-4">
                                                    <div className="flex items-center gap-1">
                                                        <Users className="h-4 w-4" />
                                                        {project.contributors_count} contributors
                                                    </div>
                                                    <div className="flex items-center gap-1">
                                                        <Clock className="h-4 w-4" />
                                                        {project.days_remaining} days left
                                                    </div>
                                                </div>

                                                <Button className="w-full" asChild>
                                                    <Link href={`/projects/${project.slug}`}>
                                                        View Project
                                                    </Link>
                                                </Button>
                                            </CardContent>
                                        </Card>
                                    ))}
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}