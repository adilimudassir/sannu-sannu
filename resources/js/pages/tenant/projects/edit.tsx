import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { ArrowLeft } from 'lucide-react';
import ProjectForm from '@/components/projects/form';
import type { Project, Tenant } from '@/types';

interface Props {
    project: Project;
    tenant: Tenant;
    errors?: Record<string, string>;
}

export default function TenantProjectEdit({ project, tenant, errors = {} }: Props) {
    const handleSubmit = (data: any) => {
        router.put(route('tenant.projects.update', [tenant.slug, project.id]), data);
    };

    const breadcrumbs = [
        { title: 'Projects', href: route('tenant.projects.index', tenant.slug) },
        { title: project.name, href: route('tenant.projects.show', [tenant.slug, project.id]) },
        { title: 'Edit', href: '#' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit ${project.name} - ${tenant.name}`} />
            
            <div className="space-y-6">
                <div className="flex items-center gap-4">
                    {/* Mobile back button */}
                    <Button variant="outline" size="sm" asChild className="md:hidden">
                        <Link href={route('tenant.projects.show', [tenant.slug, project.id])}>
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Back
                        </Link>
                    </Button>
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Edit Project</h1>
                        <p className="text-muted-foreground">Update {project.name} details</p>
                    </div>
                </div>

                <ProjectForm
                    project={project}
                    tenant={tenant}
                    onSubmit={handleSubmit}
                    errors={errors}
                />
            </div>
        </AppLayout>
    );
}