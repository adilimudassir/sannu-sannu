import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Edit, ArrowLeft } from 'lucide-react';

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
    tenant: Tenant;
}

export default function TenantProjectEdit({ project, tenant }: Props) {
    return (
        <AppLayout>
            <Head title={`Edit ${project.name} - ${tenant.name}`} />
            
            <div className="space-y-6">
                <div className="flex items-center gap-4">
                    <Button variant="outline" size="sm" asChild>
                        <Link href={route('tenant.projects.show', [tenant.slug, project.id])}>
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Back to Project
                        </Link>
                    </Button>
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Edit Project</h1>
                        <p className="text-muted-foreground">Update {project.name} details</p>
                    </div>
                </div>

                <Card className="max-w-2xl mx-auto">
                    <CardHeader className="text-center">
                        <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-muted">
                            <Edit className="h-8 w-8 text-muted-foreground" />
                        </div>
                        <CardTitle className="text-2xl">Feature Under Construction</CardTitle>
                        <CardDescription className="text-base">
                            The Project editing feature is currently being developed.
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="text-center space-y-4">
                        <p className="text-muted-foreground">
                            This page will allow you to:
                        </p>
                        <ul className="text-left space-y-2 max-w-md mx-auto">
                            <li className="flex items-center gap-2">
                                <div className="h-1.5 w-1.5 rounded-full bg-primary" />
                                Update project information
                            </li>
                            <li className="flex items-center gap-2">
                                <div className="h-1.5 w-1.5 rounded-full bg-primary" />
                                Modify funding goals and timelines
                            </li>
                            <li className="flex items-center gap-2">
                                <div className="h-1.5 w-1.5 rounded-full bg-primary" />
                                Change project visibility settings
                            </li>
                            <li className="flex items-center gap-2">
                                <div className="h-1.5 w-1.5 rounded-full bg-primary" />
                                Manage products and rewards
                            </li>
                        </ul>
                        <div className="pt-4">
                            <Button variant="outline" asChild>
                                <Link href={route('tenant.projects.show', [tenant.slug, project.id])}>
                                    Return to Project
                                </Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}