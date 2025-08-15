import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { ArrowLeft } from 'lucide-react';
import ProjectForm from '@/pages/projects/form';
import type { Project, Tenant } from '@/types';

interface Props {
    project: Project;
    tenants: Tenant[];
    errors?: Record<string, string>;
}

export default function AdminProjectEdit({ project, tenants, errors = {} }: Props) {
    const handleSubmit = (data: any) => {
        router.put(route('admin.projects.update', project.id), data);
    };

    const breadcrumbs = [
        { title: 'Projects', href: route('admin.projects.index') },
        { title: project.name, href: route('admin.projects.show', project.id) },
        { title: 'Edit', href: '#' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit ${project.name} - Admin`} />
            
            <div className="space-y-6">
                <div className="flex items-center gap-4">
                    {/* Mobile back button */}
                    <Button variant="outline" size="sm" asChild className="md:hidden">
                        <Link href={route('admin.projects.show', project.id)}>
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Back
                        </Link>
                    </Button>
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Edit Project</h1>
                        <p className="text-muted-foreground">Modify {project.name} settings and configuration</p>
                    </div>
                </div>

                <ProjectForm
                    project={project}
                    tenants={tenants}
                    isAdmin={true}
                    onSubmit={handleSubmit}
                    errors={errors}
                />
            </div>
        </AppLayout>
    );
}