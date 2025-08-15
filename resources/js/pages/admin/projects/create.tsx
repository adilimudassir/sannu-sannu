import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import ProjectForm from '@/pages/projects/form';
import type { Tenant } from '@/types';

interface Props {
    tenants: Tenant[];
    errors?: Record<string, string>;
}

export default function AdminProjectCreate({ tenants, errors = {} }: Props) {
    const handleSubmit = (data: any) => {
        router.post(route('admin.projects.store'), data);
    };

    const breadcrumbs = [
        { title: 'Projects', href: route('admin.projects.index') },
        { title: 'Create Project', href: '#' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Project - Admin" />

            <div className="space-y-6">
                <div className="flex items-center gap-4">
                    {/* Mobile back button */}
                    <Button variant="outline" size="sm" asChild className="md:hidden">
                        <Link href={route('admin.projects.index')}>
                            <ArrowLeft className="mr-2 h-4 w-4" />
                            Back
                        </Link>
                    </Button>
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Create Project</h1>
                        <p className="text-muted-foreground">Create a new project for any organization</p>
                    </div>
                </div>

                <ProjectForm
                    tenants={tenants}
                    isAdmin={true}
                    onSubmit={handleSubmit}
                    errors={errors}
                />
            </div>
        </AppLayout>
    );
}
