import ProjectFilters from '@/components/projects/project-filters';
import ProjectsGrid from '@/components/projects/projects-grid';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { PaginatedData, Project, ProjectFilters as ProjectFiltersType } from '@/types';
import { Head } from '@inertiajs/react';

interface Props {
    projects: PaginatedData<Project>;
    filters: ProjectFiltersType;
}

export default function ProjectsIndex({ projects, filters }: Props) {
    return (
        <AppLayout>
            <Head>
                <title>Projects</title>
            </Head>
            <div className="space-y-6">
                {/* Filters and Search */}
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div>
                                <CardTitle className="text-lg">Search & Filter</CardTitle>
                                <CardDescription>Find projects using search and filters</CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <ProjectFilters filters={filters} routePath="contributor.projects.index" />
                    </CardContent>
                </Card>

                {/* Projects Grid */}
                <Card>
                    <CardContent className="space-y-4">
                        <ProjectsGrid projects={projects} routePath="contributor.projects.show" />
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
