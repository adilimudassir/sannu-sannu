import PublicLayout from '@/layouts/public-layout';
import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { ArrowLeft, Share2 } from 'lucide-react';
import ProjectDetails from '@/components/projects/project-details';
import ProjectSidebar from '@/components/projects/project-sidebar';
import type { Project, ProjectStatistics } from '@/types';

interface Props {
    project: Project;
    statistics: ProjectStatistics;
    meta: {
        title: string;
        description: string;
        keywords: string;
        'og:title': string;
        'og:description': string;
        'og:type': string;
        'og:url': string;
        'og:image'?: string;
        canonical: string;
    };
}

export default function PublicProjectShow({ project, statistics, meta }: Props) {
    const handleShare = async () => {
        if (navigator.share) {
            try {
                await navigator.share({
                    title: project.name,
                    text: project.description,
                    url: window.location.href,
                });
            } catch (error) {
                // User cancelled sharing
            }
        } else {
            // Fallback to copying URL
            navigator.clipboard.writeText(window.location.href);
            // You could show a toast notification here
        }
    };

    return (
        <PublicLayout>
            <Head>
                <title>{meta.title}</title>
                <meta name="description" content={meta.description} />
                <meta name="keywords" content={meta.keywords} />
                <meta property="og:title" content={meta['og:title']} />
                <meta property="og:description" content={meta['og:description']} />
                <meta property="og:type" content={meta['og:type']} />
                <meta property="og:url" content={meta['og:url']} />
                {meta['og:image'] && <meta property="og:image" content={meta['og:image']} />}
                <link rel="canonical" href={meta.canonical} />
            </Head>

            {/* Breadcrumb Header */}
            <div className="bg-card shadow-sm border-b border-border">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                    <div className="flex items-center justify-between">
                        <Button variant="ghost" asChild>
                            <Link href={route('public.projects.index')}>
                                <ArrowLeft className="h-4 w-4 mr-2" />
                                Back to Projects
                            </Link>
                        </Button>
                        <div className="flex space-x-2">
                            <Button variant="outline" size="sm" onClick={handleShare}>
                                <Share2 className="h-4 w-4 mr-2" />
                                Share
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    {/* Main Content */}
                    <ProjectDetails project={project} />

                    {/* Sidebar */}
                    <ProjectSidebar project={project} statistics={statistics} />
                </div>
            </div>
        </PublicLayout>
    );
}
