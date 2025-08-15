import ProjectFilters from '@/components/projects/project-filters';
import ProjectsGrid from '@/components/projects/projects-grid';
import PublicLayout from '@/layouts/public-layout';
import { PaginatedData, Project, ProjectFilters as ProjectFiltersType } from '@/types';
import { Head } from '@inertiajs/react';

interface Props {
    projects: PaginatedData<Project>;
    filters: ProjectFiltersType;
    meta: {
        title: string;
        description: string;
        keywords: string;
    };
}

export default function PublicProjectsIndex({ projects, filters, meta }: Props) {
    return (
        <PublicLayout>
            <Head>
                <title>{meta.title}</title>
                <meta name="description" content={meta.description} />
                <meta name="keywords" content={meta.keywords} />
            </Head>

            {/* Header */}
            <div className="border-b border-border bg-card shadow-sm">
                <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                    <div className="text-center">
                        <h1 className="text-3xl font-bold text-foreground">Discover Projects</h1>
                        <p className="mt-2 text-lg text-muted-foreground">
                            Browse contribution-based projects from organizations across the platform
                        </p>
                    </div>

                    {/* Search and Filters */}
                    <ProjectFilters filters={filters} routePath="public.projects.index" />
                </div>
            </div>

            {/* Projects Grid */}
            <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <ProjectsGrid projects={projects} routePath="public.projects.show" />
            </div>
        </PublicLayout>
    );
}
