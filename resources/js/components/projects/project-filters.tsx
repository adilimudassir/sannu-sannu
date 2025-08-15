import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { ProjectFilters } from '@/types';
import { router } from '@inertiajs/react';
import { Filter, Search } from 'lucide-react';
import { useState } from 'react';

interface Props {
    filters: ProjectFilters;
    routePath: string;
}

export default function ProjectFilters({ filters, routePath }: Props) {
    const [searchTerm, setSearchTerm] = useState(filters.search || '');
    const [showFilters, setShowFilters] = useState(false);

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(
            route(routePath),
            {
                ...filters,
                search: searchTerm,
            },
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    const handleFilterChange = (key: string, value: string) => {
        router.get(
            route(routePath),
            {
                ...filters,
                [key]: value,
            },
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    return (
        <div className="mt-8 space-y-4">
            <form onSubmit={handleSearch} className="flex gap-4">
                <div className="relative flex-1">
                    <Search className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 transform text-muted-foreground" />
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
                <Button type="button" variant="outline" onClick={() => setShowFilters(!showFilters)}>
                    <Filter className="mr-2 h-4 w-4" />
                    Filters
                </Button>
            </form>

            {showFilters && (
                <div className="space-y-4 rounded-lg border border-border bg-card p-4">
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-4">
                        <div>
                            <label className="mb-1 block text-sm font-medium text-foreground">Min Amount</label>
                            <Input
                                type="number"
                                placeholder="$0"
                                value={filters.min_amount || ''}
                                onChange={(e) => handleFilterChange('min_amount', e.target.value)}
                            />
                        </div>
                        <div>
                            <label className="mb-1 block text-sm font-medium text-foreground">Max Amount</label>
                            <Input
                                type="number"
                                placeholder="No limit"
                                value={filters.max_amount || ''}
                                onChange={(e) => handleFilterChange('max_amount', e.target.value)}
                            />
                        </div>
                        <div>
                            <label className="mb-1 block text-sm font-medium text-foreground">Sort By</label>
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
                            <label className="mb-1 block text-sm font-medium text-foreground">Order</label>
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
    );
}
