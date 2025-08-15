import AppLayout from '@/layouts/app-layout';
import { Head, } from '@inertiajs/react';
import ProjectDetails from '@/components/projects/project-details';
import ProjectSidebar from '@/components/projects/project-sidebar';
import type { Project, ProjectStatistics } from '@/types';

interface Props {
    project: Project;
    statistics: ProjectStatistics;
}

export default function ProjectShow({ project, statistics }: Props) {
    return (
        <AppLayout>
            <Head>
                <title>{project.name}</title>
            </Head>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    {/* Main Content */}
                    <ProjectDetails project={project} />

                    {/* Sidebar */}
                    <ProjectSidebar project={project} statistics={statistics} />
                </div>
        </AppLayout>
    );
}
