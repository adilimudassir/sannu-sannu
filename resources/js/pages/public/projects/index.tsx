import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import { Search, Filter, Calendar, DollarSign, Users, Clock } from 'lucide-react';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import PublicLayout from '@/layouts/public-layout';
import { formatCurrency, formatDateShort } from '@/lib/formatters';

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
    statistics: {
        total_contributors: number;
        total_raised: number;
        completion_percentage: number;
        days_remaining: number;
    };
    products: Array<{
        id: number;
        name: string;
        price: number;
        image_url?: string;
    }>;
}

interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: Array<{
        url?: string;
        label: string;
        active: boolean;
    }>;
}

interface ProjectFilters {
    search?: string;
    min_amount?: string;
    max_amount?: string;
    sort_by?: string;
    sort_direction?: string;
}

interface Props {
    projects: PaginatedData<Project>;
    filters: ProjectFilters;
    meta: {
        title: string;
        description: string;
        keywords: string;
    };
}

export default function PublicProjectsIndex({ projects, filters, meta }: Props) {
    const [searchTerm, setSearchTerm] = useState(filters.search || '');
    const [showFilters, setShowFilters] = useState(false);

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(route('public.projects.index'), {
            ...filters,
            search: searchTerm,
        }, {
            preserveState: true,
            replace: true,
        });
    };

    const handleFilterChange = (key: string, value: string) => {
        router.get(route('public.projects.index'), {
            ...filters,
            [key]: value,
        }, {
            preserveState: true,
            replace: true,
        });
    };



    return (
        <PublicLayout>
            <Head>
                <title>{meta.title}</title>
                <meta name="description" content={meta.description} />
                <meta name="keywords" content={meta.keywords} />
            </Head>

            {/* Header */}
            <div className="bg-card shadow-sm border-b border-border">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                        <div className="text-center">
                            <h1 className="text-3xl font-bold text-foreground">
                                Discover Projects
                            </h1>
                            <p className="mt-2 text-lg text-muted-foreground">
                                Browse contribution-based projects from organizations across the platform
                            </p>
                        </div>

                        {/* Search and Filters */}
                        <div className="mt-8 space-y-4">
                            <form onSubmit={handleSearch} className="flex gap-4">
                                <div className="flex-1 relative">
                                    <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4" />
                                    <Input
                                        type="text"
                                        placeholder="Search projects..."
                                        value={searchTerm}
                                        onChange={(e) => setSearchTerm(e.target.value)}
                                        className="pl-10"
                                        aria-label="Search projects"
                                    />
                                </div>
                                <Button type="submit">Search</Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={() => setShowFilters(!showFilters)}
                                >
                                    <Filter className="h-4 w-4 mr-2" />
                                    Filters
                                </Button>
                            </form>

                            {showFilters && (
                                <div className="bg-card p-4 rounded-lg border border-border space-y-4">
                                    <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                                        <div>
                                            <label className="block text-sm font-medium text-foreground mb-1">
                                                Min Amount
                                            </label>
                                            <Input
                                                type="number"
                                                placeholder="$0"
                                                value={filters.min_amount || ''}
                                                onChange={(e) => handleFilterChange('min_amount', e.target.value)}
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-foreground mb-1">
                                                Max Amount
                                            </label>
                                            <Input
                                                type="number"
                                                placeholder="No limit"
                                                value={filters.max_amount || ''}
                                                onChange={(e) => handleFilterChange('max_amount', e.target.value)}
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-foreground mb-1">
                                                Sort By
                                            </label>
                                            <Select
                                                value={filters.sort_by || 'created_at'}
                                                onValueChange={(value) => handleFilterChange('sort_by', value)}
                                            >
                                                <SelectTrigger>
                                                    <SelectValue />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="created_at">Newest</SelectItem>
                                                    <SelectItem value="total_amount">Amount</SelectItem>
                                                    <SelectItem value="end_date">Ending Soon</SelectItem>
                                                    <SelectItem value="name">Name</SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-foreground mb-1">
                                                Order
                                            </label>
                                            <Select
                                                value={filters.sort_direction || 'desc'}
                                                onValueChange={(value) => handleFilterChange('sort_direction', value)}
                                            >
                                                <SelectTrigger>
                                                    <SelectValue />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="desc">Descending</SelectItem>
                                                    <SelectItem value="asc">Ascending</SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </div>
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </div>

                {/* Projects Grid */}
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    {projects.data.length === 0 ? (
                        <div className="text-center py-12">
                            <div className="text-muted-foreground text-6xl mb-4">🔍</div>
                            <h3 className="text-lg font-medium text-foreground mb-2">
                                No projects found
                            </h3>
                            <p className="text-muted-foreground">
                                Try adjusting your search criteria or filters.
                            </p>
                        </div>
                    ) : (
                        <>
                            <div className="mb-6 text-sm text-muted-foreground">
                                Showing {projects.data.length} of {projects.total} projects
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                {projects.data.map((project) => (
                                    <Card key={project.id} className="hover:shadow-lg transition-shadow">
                                        <CardHeader className="pb-3">
                                            <div className="flex items-start justify-between">
                                                <div className="flex-1">
                                                    <CardTitle className="text-lg line-clamp-2">
                                                        <Link
                                                            href={route('public.projects.show', project.slug)}
                                                            className="hover:text-primary transition-colors"
                                                        >
                                                            {project.name}
                                                        </Link>
                                                    </CardTitle>
                                                    <CardDescription className="mt-1">
                                                        by {project.tenant.name}
                                                    </CardDescription>
                                                </div>
                                                <Badge variant="secondary">
                                                    {project.status}
                                                </Badge>
                                            </div>
                                        </CardHeader>

                                        <CardContent className="space-y-4">
                                            {project.description && (
                                                <p className="text-sm text-muted-foreground line-clamp-3">
                                                    {project.description}
                                                </p>
                                            )}

                                            {/* Project Image */}
                                            {project.products.length > 0 && project.products[0].image_url && (
                                                <div className="aspect-video bg-muted rounded-lg overflow-hidden">
                                                    <img
                                                        src={project.products[0].image_url}
                                                        alt={project.products[0].name}
                                                        className="w-full h-full object-cover"
                                                    />
                                                </div>
                                            )}

                                            {/* Progress */}
                                            <div className="space-y-2">
                                                <div className="flex justify-between text-sm">
                                                    <span className="text-muted-foreground">Progress</span>
                                                    <span className="font-medium">
                                                        {project.statistics.completion_percentage}%
                                                    </span>
                                                </div>
                                                <Progress value={project.statistics.completion_percentage} />
                                                <div className="flex justify-between text-sm text-muted-foreground">
                                                    <span>
                                                        {formatCurrency(project.statistics.total_raised)} raised
                                                    </span>
                                                    <span>
                                                        of {formatCurrency(project.total_amount)}
                                                    </span>
                                                </div>
                                            </div>

                                            {/* Stats */}
                                            <div className="grid grid-cols-3 gap-4 text-center text-sm">
                                                <div>
                                                    <div className="flex items-center justify-center text-muted-foreground mb-1">
                                                        <Users className="h-4 w-4" />
                                                    </div>
                                                    <div className="font-medium">
                                                        {project.statistics.total_contributors}
                                                    </div>
                                                    <div className="text-muted-foreground text-xs">
                                                        Contributors
                                                    </div>
                                                </div>
                                                <div>
                                                    <div className="flex items-center justify-center text-muted-foreground mb-1">
                                                        <Clock className="h-4 w-4" />
                                                    </div>
                                                    <div className="font-medium">
                                                        {project.statistics.days_remaining}
                                                    </div>
                                                    <div className="text-muted-foreground text-xs">
                                                        Days left
                                                    </div>
                                                </div>
                                                <div>
                                                    <div className="flex items-center justify-center text-muted-foreground mb-1">
                                                        <Calendar className="h-4 w-4" />
                                                    </div>
                                                    <div className="font-medium text-xs">
                                                        {formatDateShort(project.end_date)}
                                                    </div>
                                                    <div className="text-muted-foreground text-xs">
                                                        End date
                                                    </div>
                                                </div>
                                            </div>

                                            <Button asChild className="w-full">
                                                <Link href={route('public.projects.show', project.slug)}>
                                                    View Project
                                                </Link>
                                            </Button>
                                        </CardContent>
                                    </Card>
                                ))}
                            </div>

                            {/* Pagination */}
                            {projects.last_page > 1 && (
                                <div className="mt-8 flex justify-center">
                                    <div className="flex space-x-1">
                                        {projects.links.map((link, index) => (
                                            <Button
                                                key={index}
                                                variant={link.active ? "default" : "outline"}
                                                size="sm"
                                                disabled={!link.url}
                                                onClick={() => link.url && router.get(link.url)}
                                                dangerouslySetInnerHTML={{ __html: link.label }}
                                            />
                                        ))}
                                    </div>
                                </div>
                            )}
                        </>
                    )}
                </div>
        </PublicLayout>
    );
}