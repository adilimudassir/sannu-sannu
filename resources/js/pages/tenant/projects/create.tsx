import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Plus } from 'lucide-react';

interface Tenant {
    id: number;
    name: string;
    slug: string;
}

interface Props {
    tenant: Tenant;
}

export default function TenantProjectCreate({ tenant }: Props) {
    return (
        <AppLayout>
            <Head title={`Create Project - ${tenant.name}`} />

            <div className="space-y-6">
                <div className="flex items-center gap-4">
                    <Button variant="outline" size="sm" asChild>
                        <Link href={route('tenant.projects.index', tenant.slug)}>
                            <ArrowLeft className="mr-2 h-4 w-4" />
                            Back to Projects
                        </Link>
                    </Button>
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Create Project</h1>
                        <p className="text-muted-foreground">Create a new project for {tenant.name}</p>
                    </div>
                </div>

                <Card className="mx-auto max-w-2xl">
                    <CardHeader className="text-center">
                        <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-muted">
                            <Plus className="h-8 w-8 text-muted-foreground" />
                        </div>
                        <CardTitle className="text-2xl">Feature Under Construction</CardTitle>
                        <CardDescription className="text-base">The Project creation feature is currently being developed.</CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-4 text-center">
                        <p className="text-muted-foreground">This page will allow you to:</p>
                        <ul className="mx-auto max-w-md space-y-2 text-left">
                            <li className="flex items-center gap-2">
                                <div className="h-1.5 w-1.5 rounded-full bg-primary" />
                                Create contribution-based projects
                            </li>
                            <li className="flex items-center gap-2">
                                <div className="h-1.5 w-1.5 rounded-full bg-primary" />
                                Set funding goals and timelines
                            </li>
                            <li className="flex items-center gap-2">
                                <div className="h-1.5 w-1.5 rounded-full bg-primary" />
                                Configure project visibility
                            </li>
                            <li className="flex items-center gap-2">
                                <div className="h-1.5 w-1.5 rounded-full bg-primary" />
                                Add products and rewards
                            </li>
                        </ul>
                        <div className="pt-4">
                            <Button variant="outline" asChild>
                                <Link href={route('tenant.projects.index', tenant.slug)}>
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