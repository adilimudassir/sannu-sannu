import ProjectFilters from '@/components/projects/project-filters';
import ProjectsGrid from '@/components/projects/projects-grid';
import type { PaginatedData, Project, ProjectFilters as ProjectFiltersType } from '@/types';

interface Props {
    projects: PaginatedData<Project>;
    filters: ProjectFiltersType;
    routePath: string;
}

export default function ProjectListings({ projects, filters, routePath }: Props) {
    return (
        <>
            <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <ProjectFilters filters={filters} routePath={routePath} />
            </div>

            <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <ProjectsGrid projects={projects} routePath={routePath} />
            </div>
        </>
    );
}
