import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { FolderOpen, ArrowLeft } from 'lucide-react';

interface Tenant {
    id: number;
    name: string;
    slug: string;
}

interface Project {
    id: number;
    name: string;
    slug: string;
    description?: string;
    status: string;
    tenant: Tenant;
}

interface Props {
    project: Project;
    statistics?: any;
    canEdit: boolean;
    canDelete: boolean;
    canActivate: boolean;
    canPause: boolean;
}

export default function TenantProjectShow({ project }: Props) {
    return (
        <AppLayout>
            <Head title={`${project.name} - ${project.tenant.name}`} />
            
            <div className="space-y-6">
                <div className="flex items-center gap-4">
                    <Button variant="outline" size="sm" asChild>
                        <Link href={route('tenant.projects.index', project.tenant.slug)}>
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Back to Projects
                        </Link>
                    </Button>
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">{project.name}</h1>
                        <p className="text-muted-foreground">Project details and management</p>
                    </div>
                </div>

                <Card className="max-w-2xl mx-auto">
                    <CardHeader className="text-center">
                        <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-muted">
                            <FolderOpen className="h-8 w-8 text-muted-foreground" />
                        </div>
                        <CardTitle className="text-2xl">Feature Under Construction</CardTitle>
                        <CardDescription className="text-base">
                            The Project details view is currently being developed.
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="text-center space-y-4">
                        <p className="text-muted-foreground">
                            This page will display:
                        </p>
                        <ul className="text-left space-y-2 max-w-md mx-auto">
                            <li className="flex items-center gap-2">
                                <div className="h-1.5 w-1.5 rounded-full bg-primary" />
                                Detailed project information
                            </li>
                            <li className="flex items-center gap-2">
                                <div className="h-1.5 w-1.5 rounded-full bg-primary" />
                                Project progress and statistics
                            </li>
                            <li className="flex items-center gap-2">
                                <div className="h-1.5 w-1.5 rounded-full bg-primary" />
                                Contributor activity and contributions
                            </li>
                            <li className="flex items-center gap-2">
                                <div className="h-1.5 w-1.5 rounded-full bg-primary" />
                                Project management actions
                            </li>
                        </ul>
                        <div className="pt-4">
                            <Button variant="outline" asChild>
                                <Link href={route('tenant.projects.index', project.tenant.slug)}>
                                    Return to Projects
                                </Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}