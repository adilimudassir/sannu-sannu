import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import ProjectForm from '@/components/projects/form';
import type { Tenant } from '@/types';

interface Props {
    tenant: Tenant;
    errors?: Record<string, string>;
}

export default function TenantProjectCreate({ tenant, errors = {} }: Props) {
    const handleSubmit = (data: any) => {
        router.post(route('tenant.projects.store', tenant.slug), data);
    };

    const breadcrumbs = [
        { title: 'Projects', href: route('tenant.projects.index', tenant.slug) },
        { title: 'Create Project', href: '#' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Create Project - ${tenant.name}`} />

            <div className="space-y-6">
                <div className="flex items-center gap-4">
                    {/* Mobile back button */}
                    <Button variant="outline" size="sm" asChild className="md:hidden">
                        <Link href={route('tenant.projects.index', tenant.slug)}>
                            <ArrowLeft className="mr-2 h-4 w-4" />
                            Back
                        </Link>
                    </Button>
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Create Project</h1>
                        <p className="text-muted-foreground">Create a new project for {tenant.name}</p>
                    </div>
                </div>

                <ProjectForm
                    tenant={tenant}
                    onSubmit={handleSubmit}
                    errors={errors}
                />
            </div>
        </AppLayout>
    );
}