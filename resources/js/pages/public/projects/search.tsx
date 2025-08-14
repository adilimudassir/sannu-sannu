import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Progress } from '@/components/ui/progress';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import PublicLayout from '@/layouts/public-layout';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Filter, Search, X } from 'lucide-react';
import { useState } from 'react';
import { formatCurrency } from '@/lib/formatters';

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
    searchTerm: string;
    filters: ProjectFilters;
    meta: {
        title: string;
        description: string;
        noindex?: boolean;
    };
}

export default function PublicProjectsSearch({ projects, searchTerm, filters, meta }: Props) {
    const [currentSearchTerm, setCurrentSearchTerm] = useState(searchTerm);
    const [showFilters, setShowFilters] = useState(false);

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(
            route('public.projects.search'),
            {
                ...filters,
                search: currentSearchTerm,
            },
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    const handleFilterChange = (key: string, value: string) => {
        router.get(
            route('public.projects.search'),
            {
                ...filters,
                search: searchTerm,
                [key]: value,
            },
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    const clearFilters = () => {
        router.get(
            route('public.projects.search'),
            {
                search: searchTerm,
            },
            {
                preserveState: true,
                replace: true,
            },
        );
    };



    const hasActiveFilters =
        filters.min_amount ||
        filters.max_amount ||
        (filters.sort_by && filters.sort_by !== 'created_at') ||
        (filters.sort_direction && filters.sort_direction !== 'desc');

    return (
        <PublicLayout>
            <Head>
                <title>{meta.title}</title>
                <meta name="description" content={meta.description} />
                {meta.noindex && <meta name="robots" content="noindex" />}
            </Head>
            {/* Header */}
            <div className="border-b border-border bg-card shadow-sm">
                <div className="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
                    <div className="mb-4 flex items-center justify-between">
                        <Button variant="ghost" asChild>
                            <Link href={route('public.projects.index')}>
                                <ArrowLeft className="mr-2 h-4 w-4" />
                                Back to Projects
                            </Link>
                        </Button>
                    </div>

                    <div className="space-y-4">
                        <div>
                            <h1 className="text-2xl font-bold text-gray-900">
                                {searchTerm ? `Search Results for "${searchTerm}"` : 'Search Projects'}
                            </h1>
                            {projects.total > 0 && (
                                <p className="mt-1 text-gray-600">
                                    Found {projects.total} project{projects.total !== 1 ? 's' : ''}
                                </p>
                            )}
                        </div>

                        {/* Search Form */}
                        <form onSubmit={handleSearch} className="flex gap-4">
                            <div className="relative flex-1">
                                <Search className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 transform text-gray-400" />
                                <Input
                                    type="text"
                                    placeholder="Search projects..."
                                    value={currentSearchTerm}
                                    onChange={(e) => setCurrentSearchTerm(e.target.value)}
                                    className="pl-10"
                                />
                            </div>
                            <Button type="submit">Search</Button>
                            <Button type="button" variant="outline" onClick={() => setShowFilters(!showFilters)}>
                                <Filter className="mr-2 h-4 w-4" />
                                Filters
                                {hasActiveFilters && (
                                    <Badge variant="secondary" className="ml-2 px-1 py-0 text-xs">
                                        !
                                    </Badge>
                                )}
                            </Button>
                        </form>

                        {/* Active Filters */}
                        {hasActiveFilters && (
                            <div className="flex flex-wrap items-center gap-2">
                                <span className="text-sm text-gray-600">Active filters:</span>
                                {filters.min_amount && <Badge variant="outline">Min: {formatCurrency(parseFloat(filters.min_amount))}</Badge>}
                                {filters.max_amount && <Badge variant="outline">Max: {formatCurrency(parseFloat(filters.max_amount))}</Badge>}
                                {filters.sort_by && filters.sort_by !== 'created_at' && <Badge variant="outline">Sort: {filters.sort_by}</Badge>}
                                <Button variant="ghost" size="sm" onClick={clearFilters} className="h-6 px-2 text-xs">
                                    <X className="mr-1 h-3 w-3" />
                                    Clear all
                                </Button>
                            </div>
                        )}

                        {/* Filter Panel */}
                        {showFilters && (
                            <div className="space-y-4 rounded-lg border bg-white p-4">
                                <div className="grid grid-cols-1 gap-4 md:grid-cols-4">
                                    <div>
                                        <label className="mb-1 block text-sm font-medium text-gray-700">Min Amount</label>
                                        <Input
                                            type="number"
                                            placeholder="$0"
                                            value={filters.min_amount || ''}
                                            onChange={(e) => handleFilterChange('min_amount', e.target.value)}
                                        />
                                    </div>
                                    <div>
                                        <label className="mb-1 block text-sm font-medium text-gray-700">Max Amount</label>
                                        <Input
                                            type="number"
                                            placeholder="No limit"
                                            value={filters.max_amount || ''}
                                            onChange={(e) => handleFilterChange('max_amount', e.target.value)}
                                        />
                                    </div>
                                    <div>
                                        <label className="mb-1 block text-sm font-medium text-gray-700">Sort By</label>
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
                                        <label className="mb-1 block text-sm font-medium text-gray-700">Order</label>
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

            {/* Results */}
            <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                {projects.data.length === 0 ? (
                    <div className="py-12 text-center">
                        <div className="mb-4 text-6xl text-gray-400">🔍</div>
                        <h3 className="mb-2 text-lg font-medium text-gray-900">No projects found</h3>
                        <p className="mb-4 text-gray-600">
                            {searchTerm
                                ? `No projects match "${searchTerm}". Try different keywords or adjust your filters.`
                                : 'Try entering some search terms or adjusting your filters.'}
                        </p>
                        <Button variant="outline" asChild>
                            <Link href={route('public.projects.index')}>Browse All Projects</Link>
                        </Button>
                    </div>
                ) : (
                    <>
                        <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                            {projects.data.map((project) => (
                                <Card key={project.id} className="transition-shadow hover:shadow-lg">
                                    <CardHeader className="pb-3">
                                        <div className="flex items-start justify-between">
                                            <div className="flex-1">
                                                <CardTitle className="line-clamp-2 text-lg">
                                                    <Link
                                                        href={route('public.projects.show', project.slug)}
                                                        className="transition-colors hover:text-blue-600"
                                                    >
                                                        {project.name}
                                                    </Link>
                                                </CardTitle>
                                                <CardDescription className="mt-1">by {project.tenant.name}</CardDescription>
                                            </div>
                                            <Badge variant="secondary">{project.status}</Badge>
                                        </div>
                                    </CardHeader>

                                    <CardContent className="space-y-4">
                                        {project.description && <p className="line-clamp-3 text-sm text-gray-600">{project.description}</p>}

                                        {/* Progress */}
                                        <div className="space-y-2">
                                            <div className="flex justify-between text-sm">
                                                <span className="text-gray-600">Progress</span>
                                                <span className="font-medium">{project.statistics.completion_percentage}%</span>
                                            </div>
                                            <Progress value={project.statistics.completion_percentage} />
                                            <div className="flex justify-between text-sm text-gray-600">
                                                <span>{formatCurrency(project.statistics.total_raised)} raised</span>
                                                <span>of {formatCurrency(project.total_amount)}</span>
                                            </div>
                                        </div>

                                        <Button asChild className="w-full">
                                            <Link href={route('public.projects.show', project.slug)}>View Project</Link>
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
                                            variant={link.active ? 'default' : 'outline'}
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
