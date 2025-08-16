import PublicLayout from '@/layouts/public-layout';
import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';
import { 
  ArrowLeft, 
  Share2, 
  Calendar, 
  DollarSign, 
  Users, 
  Clock,
  Globe,
  Lock,
  UserCheck,
  TrendingUp,
  Target,
  Image as ImageIcon,
  Heart,
  ExternalLink
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { ProjectStatusBadge } from '@/components/projects';
import { formatCurrency, formatDate, formatTimeRemaining, formatPercentage } from '@/lib/formatters';
import type { Project, ProjectStatistics, Product } from '@/types';

interface Props {
    project: Project;
    statistics: ProjectStatistics;
    meta?: {
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
    const [shareSuccess, setShareSuccess] = useState(false);

    // Early return if project data is not available
    if (!project || !statistics) {
        return (
            <PublicLayout>
                <Head title="Loading...">
                </Head>
                <div className="flex items-center justify-center min-h-screen">
                    <div className="text-center">
                        <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-primary"></div>
                        <p className="mt-4 text-muted-foreground">Loading project...</p>
                    </div>
                </div>
            </PublicLayout>
        );
    }

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
            try {
                await navigator.clipboard.writeText(window.location.href);
                setShareSuccess(true);
                setTimeout(() => setShareSuccess(false), 2000);
            } catch (error) {
                // Handle clipboard error
            }
        }
    };

    const getVisibilityIcon = () => {
        switch (project.visibility) {
            case 'public':
                return <Globe className="h-4 w-4" />;
            case 'private':
                return <Lock className="h-4 w-4" />;
            case 'invite_only':
                return <UserCheck className="h-4 w-4" />;
            default:
                return <Globe className="h-4 w-4" />;
        }
    };

    const getVisibilityLabel = () => {
        switch (project.visibility) {
            case 'public':
                return 'Public Project';
            case 'private':
                return 'Private Project';
            case 'invite_only':
                return 'Invite Only';
            default:
                return 'Public Project';
        }
    };

    return (
        <PublicLayout>
            <Head title={meta?.title || `${project?.name || 'Project'} - Project Details`}>
                {meta?.description && <meta name="description" content={meta.description} />}
                {meta?.keywords && <meta name="keywords" content={meta.keywords} />}
                {meta && meta['og:title'] && <meta property="og:title" content={meta['og:title']} />}
                {meta && meta['og:description'] && <meta property="og:description" content={meta['og:description']} />}
                {meta && meta['og:type'] && <meta property="og:type" content={meta['og:type']} />}
                {meta && meta['og:url'] && <meta property="og:url" content={meta['og:url']} />}
                {meta && meta['og:image'] && <meta property="og:image" content={meta['og:image']} />}
                {meta?.canonical && <link rel="canonical" href={meta.canonical} />}
            </Head>

            {/* Header */}
            <div className="bg-card shadow-sm border-b border-border">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-4">
                            <Button variant="ghost" asChild>
                                <Link href={route('public.projects.index')}>
                                    <ArrowLeft className="h-4 w-4 mr-2" />
                                    Back to Projects
                                </Link>
                            </Button>
                            <div>
                                <h1 className="text-3xl font-bold tracking-tight">{project.name}</h1>
                                <div className="flex items-center gap-2 mt-1">
                                    <ProjectStatusBadge status={project.status} />
                                    <Badge variant="outline" className="gap-1">
                                        {getVisibilityIcon()}
                                        {getVisibilityLabel()}
                                    </Badge>
                                    {project.tenant && (
                                        <Badge variant="secondary">
                                            by {project.tenant.name}
                                        </Badge>
                                    )}
                                </div>
                            </div>
                        </div>
                        
                        <div className="flex items-center gap-2">
                            <Button variant="outline" size="sm" onClick={handleShare}>
                                <Share2 className="h-4 w-4 mr-2" />
                                {shareSuccess ? 'Copied!' : 'Share'}
                            </Button>
                            <Button size="sm">
                                <Heart className="h-4 w-4 mr-2" />
                                Save Project
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    {/* Main Content */}
                    <div className="lg:col-span-2 space-y-6">
                        {/* Project Overview */}
                        <Card>
                            <CardHeader>
                                <CardTitle>About This Project</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                {project.description && (
                                    <div>
                                        <p className="text-muted-foreground leading-relaxed">
                                            {project.description}
                                        </p>
                                    </div>
                                )}
                                
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <div className="flex items-center gap-2 text-sm">
                                            <Calendar className="h-4 w-4 text-muted-foreground" />
                                            <span className="font-medium">Start Date:</span>
                                            <span>{formatDate(project.start_date)}</span>
                                        </div>
                                        <div className="flex items-center gap-2 text-sm">
                                            <Calendar className="h-4 w-4 text-muted-foreground" />
                                            <span className="font-medium">End Date:</span>
                                            <span>{formatDate(project.end_date)}</span>
                                        </div>
                                        {project.registration_deadline && (
                                            <div className="flex items-center gap-2 text-sm">
                                                <Clock className="h-4 w-4 text-muted-foreground" />
                                                <span className="font-medium">Registration Deadline:</span>
                                                <span>{formatDate(project.registration_deadline)}</span>
                                            </div>
                                        )}
                                    </div>
                                    
                                    <div className="space-y-2">
                                        <div className="flex items-center gap-2 text-sm">
                                            <Target className="h-4 w-4 text-muted-foreground" />
                                            <span className="font-medium">Goal Amount:</span>
                                            <span className="font-semibold text-primary">
                                                {formatCurrency(project.total_amount)}
                                            </span>
                                        </div>
                                        {project.minimum_contribution && (
                                            <div className="flex items-center gap-2 text-sm">
                                                <DollarSign className="h-4 w-4 text-muted-foreground" />
                                                <span className="font-medium">Minimum Contribution:</span>
                                                <span>{formatCurrency(project.minimum_contribution)}</span>
                                            </div>
                                        )}
                                        {project.max_contributors && (
                                            <div className="flex items-center gap-2 text-sm">
                                                <Users className="h-4 w-4 text-muted-foreground" />
                                                <span className="font-medium">Max Contributors:</span>
                                                <span>{project.max_contributors}</span>
                                            </div>
                                        )}
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Products Gallery */}
                        {project.products && project.products.length > 0 && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>What You're Contributing To</CardTitle>
                                    <CardDescription>
                                        Items and services included in this project
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        {project.products.map((product: Product) => (
                                            <div key={product.id} className="border rounded-lg p-4 space-y-3 hover:shadow-md transition-shadow">
                                                {product.image_url && (
                                                    <div className="aspect-video bg-muted rounded-md overflow-hidden">
                                                        <img 
                                                            src={product.image_url} 
                                                            alt={product.name}
                                                            className="w-full h-full object-cover hover:scale-105 transition-transform"
                                                        />
                                                    </div>
                                                )}
                                                {!product.image_url && (
                                                    <div className="aspect-video bg-muted rounded-md flex items-center justify-center">
                                                        <ImageIcon className="h-8 w-8 text-muted-foreground" />
                                                    </div>
                                                )}
                                                <div>
                                                    <h4 className="font-semibold text-lg">{product.name}</h4>
                                                    {product.description && (
                                                        <p className="text-sm text-muted-foreground mt-1">
                                                            {product.description}
                                                        </p>
                                                    )}
                                                    <div className="flex items-center justify-between mt-3">
                                                        <span className="text-2xl font-bold text-primary">
                                                            {formatCurrency(product.price)}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>
                        )}

                        {/* Organization Info */}
                        {project.tenant && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>About the Organization</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="flex items-start gap-4">
                                        <div className="flex-1">
                                            <h4 className="font-semibold text-lg">{project.tenant.name}</h4>
                                            {project.tenant.description && (
                                                <p className="text-muted-foreground mt-1">
                                                    {project.tenant.description}
                                                </p>
                                            )}
                                            {project.creator && (
                                                <div className="mt-3">
                                                    <span className="text-sm text-muted-foreground">Project created by </span>
                                                    <span className="font-medium">{project.creator.name}</span>
                                                </div>
                                            )}
                                        </div>
                                        <Button variant="outline" size="sm">
                                            <ExternalLink className="h-4 w-4 mr-2" />
                                            View Profile
                                        </Button>
                                    </div>
                                </CardContent>
                            </Card>
                        )}
                    </div>

                    {/* Sidebar */}
                    <div className="space-y-6">
                        {/* Progress Card */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <TrendingUp className="h-5 w-5" />
                                    Project Progress
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-6">
                                {/* Progress */}
                                <div className="space-y-2">
                                    <div className="flex justify-between text-sm">
                                        <span className="text-muted-foreground">Progress</span>
                                        <span className="font-medium">
                                            {formatPercentage(statistics.completion_percentage)}
                                        </span>
                                    </div>
                                    <Progress value={statistics.completion_percentage} className="h-3" />
                                    <div className="flex justify-between text-sm text-muted-foreground">
                                        <span>{formatCurrency(statistics.total_raised)} raised</span>
                                        <span>of {formatCurrency(project.total_amount)}</span>
                                    </div>
                                </div>

                                <Separator />

                                {/* Key Metrics */}
                                <div className="space-y-4">
                                    <div className="flex items-center justify-between">
                                        <div className="flex items-center gap-2">
                                            <Users className="h-4 w-4 text-muted-foreground" />
                                            <span className="text-sm">Contributors</span>
                                        </div>
                                        <span className="font-semibold">{statistics.total_contributors}</span>
                                    </div>

                                    <div className="flex items-center justify-between">
                                        <div className="flex items-center gap-2">
                                            <DollarSign className="h-4 w-4 text-muted-foreground" />
                                            <span className="text-sm">Average Contribution</span>
                                        </div>
                                        <span className="font-semibold">
                                            {formatCurrency(statistics.average_contribution)}
                                        </span>
                                    </div>

                                    <div className="flex items-center justify-between">
                                        <div className="flex items-center gap-2">
                                            <Clock className="h-4 w-4 text-muted-foreground" />
                                            <span className="text-sm">Time Remaining</span>
                                        </div>
                                        <span className="font-semibold">
                                            {formatTimeRemaining(project.end_date)}
                                        </span>
                                    </div>
                                </div>

                                <Separator />

                                {/* Contribute Button */}
                                {project.status === 'active' && (
                                    <Button className="w-full" size="lg">
                                        <Heart className="h-4 w-4 mr-2" />
                                        Contribute Now
                                    </Button>
                                )}

                                {project.status !== 'active' && (
                                    <div className="text-center text-sm text-muted-foreground">
                                        {project.status === 'draft' && 'Project not yet active'}
                                        {project.status === 'paused' && 'Project is currently paused'}
                                        {project.status === 'completed' && 'Project has been completed'}
                                        {project.status === 'cancelled' && 'Project has been cancelled'}
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {/* Quick Stats */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Quick Stats</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="grid grid-cols-2 gap-4 text-center">
                                    <div>
                                        <div className="text-2xl font-bold text-blue-600">
                                            {statistics.total_contributors}
                                        </div>
                                        <div className="text-sm text-muted-foreground">Contributors</div>
                                    </div>
                                    <div>
                                        <div className="text-2xl font-bold text-green-600">
                                            {statistics.days_remaining}
                                        </div>
                                        <div className="text-sm text-muted-foreground">Days Left</div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Project Timeline */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Timeline</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="space-y-3">
                                    <div className="flex items-center gap-3">
                                        <div className="w-2 h-2 rounded-full bg-primary" />
                                        <div className="flex-1">
                                            <div className="text-sm font-medium">Project Created</div>
                                            <div className="text-xs text-muted-foreground">
                                                {formatDate(project.created_at)}
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div className="flex items-center gap-3">
                                        <div className={`w-2 h-2 rounded-full ${
                                            new Date(project.start_date) <= new Date() ? 'bg-primary' : 'bg-muted'
                                        }`} />
                                        <div className="flex-1">
                                            <div className="text-sm font-medium">Project Starts</div>
                                            <div className="text-xs text-muted-foreground">
                                                {formatDate(project.start_date)}
                                            </div>
                                        </div>
                                    </div>

                                    {project.registration_deadline && (
                                        <div className="flex items-center gap-3">
                                            <div className={`w-2 h-2 rounded-full ${
                                                new Date(project.registration_deadline) <= new Date() ? 'bg-primary' : 'bg-muted'
                                            }`} />
                                            <div className="flex-1">
                                                <div className="text-sm font-medium">Registration Deadline</div>
                                                <div className="text-xs text-muted-foreground">
                                                    {formatDate(project.registration_deadline)}
                                                </div>
                                            </div>
                                        </div>
                                    )}
                                    
                                    <div className="flex items-center gap-3">
                                        <div className={`w-2 h-2 rounded-full ${
                                            new Date(project.end_date) <= new Date() ? 'bg-primary' : 'bg-muted'
                                        }`} />
                                        <div className="flex-1">
                                            <div className="text-sm font-medium">Project Ends</div>
                                            <div className="text-xs text-muted-foreground">
                                                {formatDate(project.end_date)}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </PublicLayout>
    );
}
