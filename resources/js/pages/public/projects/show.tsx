import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import { Separator } from '@/components/ui/separator';
import { 
    Calendar, 
    DollarSign, 
    Users, 
    Clock, 
    MapPin, 
    ArrowLeft,
    Share2,
    Heart
} from 'lucide-react';
import PublicLayout from '@/layouts/public-layout';
import { formatCurrency, formatDate } from '@/lib/formatters';

interface Project {
    id: number;
    name: string;
    slug: string;
    description?: string;
    total_amount: number;
    start_date: string;
    end_date: string;
    status: string;
    tenant: {
        id: number;
        name: string;
        slug: string;
    };
    creator: {
        id: number;
        name: string;
    };
    products: Array<{
        id: number;
        name: string;
        description?: string;
        price: number;
        image_url?: string;
    }>;
}

interface ProjectStatistics {
    total_contributors: number;
    total_raised: number;
    completion_percentage: number;
    days_remaining: number;
    average_contribution: number;
}

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
            } catch (err) {
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
                        <div className="lg:col-span-2 space-y-6">
                            {/* Project Header */}
                            <Card>
                                <CardHeader>
                                    <div className="flex items-start justify-between">
                                        <div className="flex-1">
                                            <CardTitle className="text-2xl mb-2">
                                                {project.name}
                                            </CardTitle>
                                            <CardDescription className="text-base">
                                                by <Link 
                                                    href="#" 
                                                    className="font-medium text-blue-600 hover:text-blue-800"
                                                >
                                                    {project.tenant.name}
                                                </Link>
                                            </CardDescription>
                                        </div>
                                        <Badge variant="secondary" className="ml-4">
                                            {project.status}
                                        </Badge>
                                    </div>
                                </CardHeader>

                                <CardContent>
                                    {project.description && (
                                        <div className="prose max-w-none">
                                            <p className="text-muted-foreground leading-relaxed">
                                                {project.description}
                                            </p>
                                        </div>
                                    )}
                                </CardContent>
                            </Card>

                            {/* Products */}
                            {project.products.length > 0 && (
                                <Card>
                                    <CardHeader>
                                        <CardTitle>What You're Contributing To</CardTitle>
                                    </CardHeader>
                                    <CardContent>
                                        <div className="space-y-6">
                                            {project.products.map((product, index) => (
                                                <div key={product.id}>
                                                    {index > 0 && <Separator className="my-6" />}
                                                    <div className="flex gap-4">
                                                        {product.image_url && (
                                                            <div className="flex-shrink-0">
                                                                <img
                                                                    src={product.image_url}
                                                                    alt={product.name}
                                                                    className="w-24 h-24 object-cover rounded-lg"
                                                                />
                                                            </div>
                                                        )}
                                                        <div className="flex-1">
                                                            <h3 className="font-semibold text-lg mb-1">
                                                                {product.name}
                                                            </h3>
                                                            <p className="text-2xl font-bold text-green-600 mb-2">
                                                                {formatCurrency(product.price)}
                                                            </p>
                                                            {product.description && (
                                                                <p className="text-muted-foreground">
                                                                    {product.description}
                                                                </p>
                                                            )}
                                                        </div>
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    </CardContent>
                                </Card>
                            )}

                            {/* Project Timeline */}
                            <Card>
                                <CardHeader>
                                    <CardTitle>Project Timeline</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-4">
                                        <div className="flex items-center gap-3">
                                            <Calendar className="h-5 w-5 text-muted-foreground" />
                                            <div>
                                                <div className="font-medium">Start Date</div>
                                                <div className="text-muted-foreground">
                                                    {formatDate(project.start_date)}
                                                </div>
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-3">
                                            <Calendar className="h-5 w-5 text-muted-foreground" />
                                            <div>
                                                <div className="font-medium">End Date</div>
                                                <div className="text-muted-foreground">
                                                    {formatDate(project.end_date)}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        {/* Sidebar */}
                        <div className="space-y-6">
                            {/* Progress Card */}
                            <Card>
                                <CardHeader>
                                    <CardTitle>Project Progress</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="text-center">
                                        <div className="text-3xl font-bold text-green-600 mb-1">
                                            {statistics.completion_percentage}%
                                        </div>
                                        <div className="text-muted-foreground">funded</div>
                                    </div>

                                    <Progress value={statistics.completion_percentage} className="h-3" />

                                    <div className="space-y-3">
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Raised</span>
                                            <span className="font-semibold">
                                                {formatCurrency(statistics.total_raised)}
                                            </span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Goal</span>
                                            <span className="font-semibold">
                                                {formatCurrency(project.total_amount)}
                                            </span>
                                        </div>
                                        <Separator />
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Contributors</span>
                                            <span className="font-semibold">
                                                {statistics.total_contributors}
                                            </span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Days remaining</span>
                                            <span className="font-semibold">
                                                {statistics.days_remaining}
                                            </span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Average contribution</span>
                                            <span className="font-semibold">
                                                {formatCurrency(statistics.average_contribution)}
                                            </span>
                                        </div>
                                    </div>

                                    <Button className="w-full" size="lg">
                                        Contribute Now
                                    </Button>
                                </CardContent>
                            </Card>

                            {/* Organization Info */}
                            <Card>
                                <CardHeader>
                                    <CardTitle>About the Organization</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-3">
                                        <div>
                                            <div className="font-medium">{project.tenant.name}</div>
                                            <div className="text-sm text-muted-foreground">Organization</div>
                                        </div>
                                        <div>
                                            <div className="font-medium">{project.creator.name}</div>
                                            <div className="text-sm text-muted-foreground">Project Creator</div>
                                        </div>
                                    </div>
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
                        </div>
                    </div>
                </div>
        </PublicLayout>
    );
}