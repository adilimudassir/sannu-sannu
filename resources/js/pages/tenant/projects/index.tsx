import { Head, Link, router } from '@inertiajs/react';
import {
    ColumnDef,
    ColumnFiltersState,
    SortingState,
    VisibilityState,
    flexRender,
    getCoreRowModel,
    getFilteredRowModel,
    getPaginationRowModel,
    getSortedRowModel,
    useReactTable,
} from '@tanstack/react-table';
import {
    ArrowUpDown,
    Calendar,
    ChevronDown,
    DollarSign,
    Edit,
    Eye,
    Filter,
    Globe,
    Lock,
    MoreHorizontal,
    Plus,
    Search,
    Trash2,
    UserPlus,
    Users,
    X,
} from 'lucide-react';
import { useState } from 'react';

import { ProjectStatusBadge } from '@/components/projects';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { formatCurrency, formatDateShort } from '@/lib/formatters';

interface Tenant {
    id: number;
    name: string;
    slug: string;
}

interface User {
    id: number;
    name: string;
    email: string;
}

interface ProjectStatistics {
    total_contributors: number;
    total_raised: number;
    completion_percentage: number;
    days_remaining: number;
    average_contribution: number;
}

interface Project {
    id: number;
    name: string;
    slug: string;
    description?: string;
    status: 'draft' | 'active' | 'paused' | 'completed' | 'cancelled';
    visibility: 'public' | 'private' | 'invite_only';
    total_amount: number;
    start_date: string;
    end_date: string;
    created_at: string;
    updated_at: string;
    tenant?: Tenant;
    creator?: User;
    statistics?: ProjectStatistics;
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
    status?: string;
    visibility?: string;
    sort_by?: string;
    sort_direction?: string;
}

interface Props {
    projects: PaginatedData<Project>;
    tenant: Tenant;
    filters: ProjectFilters;
}

const visibilityConfig = {
    public: { label: 'Public', icon: Globe, color: 'text-green-600' },
    private: { label: 'Private', icon: Lock, color: 'text-yellow-600' },
    invite_only: { label: 'Invite Only', icon: UserPlus, color: 'text-blue-600' },
};

export default function TenantProjectIndex({ projects, tenant, filters }: Props) {
    const [sorting, setSorting] = useState<SortingState>([]);
    const [columnFilters, setColumnFilters] = useState<ColumnFiltersState>([]);
    const [columnVisibility, setColumnVisibility] = useState<VisibilityState>({});
    const [rowSelection, setRowSelection] = useState({});
    const [searchTerm, setSearchTerm] = useState(filters.search || '');
    const [showFilters, setShowFilters] = useState(false);

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(
            route('tenant.projects.index', tenant.slug),
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
            route('tenant.projects.index', tenant.slug),
            {
                ...filters,
                [key]: value || undefined,
            },
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    const clearFilters = () => {
        setSearchTerm('');
        router.get(
            route('tenant.projects.index', tenant.slug),
            {},
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    const columns: ColumnDef<Project>[] = [
        {
            accessorKey: 'name',
            header: ({ column }) => {
                return (
                    <Button
                        variant="ghost"
                        onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                        className="-ml-4 h-auto p-0 pl-4 font-medium hover:bg-muted/50"
                    >
                        Project Name
                        <ArrowUpDown className="ml-2 h-4 w-4 opacity-50" />
                    </Button>
                );
            },
            cell: ({ row }) => {
                const project = row.original;
                return (
                    <div className="space-y-1">
                        <Link href={route('tenant.projects.show', [tenant.slug, project.id])} className="font-medium text-primary hover:underline">
                            {project.name}
                        </Link>
                        {project.description && <p className="line-clamp-2 text-sm text-muted-foreground">{project.description}</p>}
                    </div>
                );
            },
        },
        {
            accessorKey: 'status',
            header: 'Status',
            cell: ({ row }) => {
                const status = row.getValue('status') as 'draft' | 'active' | 'paused' | 'completed' | 'cancelled';
                return <ProjectStatusBadge status={status} />;
            },
        },
        {
            accessorKey: 'visibility',
            header: 'Visibility',
            cell: ({ row }) => {
                const visibility = row.getValue('visibility') as keyof typeof visibilityConfig;
                const config = visibilityConfig[visibility];
                const Icon = config.icon;
                return (
                    <div className="flex items-center gap-2">
                        <Icon className={`h-4 w-4 ${config.color}`} />
                        <span className="text-sm">{config.label}</span>
                    </div>
                );
            },
        },
        {
            accessorKey: 'total_amount',
            header: ({ column }) => {
                return (
                    <Button
                        variant="ghost"
                        onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                        className="-ml-4 h-auto p-0 pl-4 font-medium hover:bg-muted/50"
                    >
                        <DollarSign className="mr-2 h-4 w-4 opacity-50" />
                        Total Amount
                        <ArrowUpDown className="ml-2 h-4 w-4 opacity-50" />
                    </Button>
                );
            },
            cell: ({ row }) => {
                const amount = parseFloat(row.getValue('total_amount'));
                return <div className="font-medium">{formatCurrency(amount)}</div>;
            },
        },
        {
            accessorKey: 'statistics',
            header: 'Progress',
            cell: ({ row }) => {
                const project = row.original;
                const stats = project.statistics;
                if (!stats) return <span className="text-muted-foreground">-</span>;

                return (
                    <div className="space-y-1">
                        <div className="flex items-center gap-2 text-sm">
                            <Users className="h-3 w-3" />
                            <span>{stats.total_contributors} contributors</span>
                        </div>
                        <div className="text-sm text-muted-foreground">
                            {formatCurrency(stats.total_raised)} raised ({stats.completion_percentage}%)
                        </div>
                    </div>
                );
            },
        },
        {
            accessorKey: 'end_date',
            header: ({ column }) => {
                return (
                    <Button
                        variant="ghost"
                        onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                        className="-ml-4 h-auto p-0 pl-4 font-medium hover:bg-muted/50"
                    >
                        <Calendar className="mr-2 h-4 w-4 opacity-50" />
                        End Date
                        <ArrowUpDown className="ml-2 h-4 w-4 opacity-50" />
                    </Button>
                );
            },
            cell: ({ row }) => {
                const date = row.getValue('end_date') as string;
                const stats = row.original.statistics;
                return (
                    <div className="space-y-1">
                        <div className="text-sm">{formatDateShort(date)}</div>
                        {stats && stats.days_remaining !== null && (
                            <div className="text-xs text-muted-foreground">
                                {stats.days_remaining > 0 ? `${stats.days_remaining} days left` : 'Ended'}
                            </div>
                        )}
                    </div>
                );
            },
        },
        {
            id: 'actions',
            enableHiding: false,
            cell: ({ row }) => {
                const project = row.original;

                return (
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <Button variant="ghost" className="h-8 w-8 p-0 hover:bg-muted">
                                <span className="sr-only">Open menu</span>
                                <MoreHorizontal className="h-4 w-4" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" className="w-48">
                            <DropdownMenuLabel>Actions</DropdownMenuLabel>
                            <DropdownMenuItem asChild>
                                <Link href={route('tenant.projects.show', [tenant.slug, project.id])}>
                                    <Eye className="mr-2 h-4 w-4" />
                                    View Details
                                </Link>
                            </DropdownMenuItem>
                            <DropdownMenuItem asChild>
                                <Link href={route('tenant.projects.edit', [tenant.slug, project.id])} className="flex items-center">
                                    <Edit className="mr-2 h-4 w-4" />
                                    Edit Project
                                </Link>
                            </DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem
                                className="text-destructive focus:text-destructive"
                                onClick={() => {
                                    if (confirm('Are you sure you want to delete this project?')) {
                                        router.delete(route('tenant.projects.destroy', [tenant.slug, project.id]));
                                    }
                                }}
                            >
                                <Trash2 className="mr-2 h-4 w-4" />
                                Delete Project
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                );
            },
        },
    ];

    const table = useReactTable({
        data: projects.data,
        columns,
        onSortingChange: setSorting,
        onColumnFiltersChange: setColumnFilters,
        getCoreRowModel: getCoreRowModel(),
        getPaginationRowModel: getPaginationRowModel(),
        getSortedRowModel: getSortedRowModel(),
        getFilteredRowModel: getFilteredRowModel(),
        onColumnVisibilityChange: setColumnVisibility,
        onRowSelectionChange: setRowSelection,
        state: {
            sorting,
            columnFilters,
            columnVisibility,
            rowSelection,
        },
    });

    return (
        <AppLayout>
            <Head title={`Projects - ${tenant.name}`} />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Projects</h1>
                        <p className="text-muted-foreground">Manage your organization's contribution projects</p>
                    </div>
                    <Button asChild>
                        <Link href={route('tenant.projects.create', tenant.slug)}>
                            <Plus className="mr-2 h-4 w-4" />
                            New Project
                        </Link>
                    </Button>
                </div>

                {/* Filters and Search */}
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div>
                                <CardTitle className="text-lg">Search & Filter</CardTitle>
                                <CardDescription>Find projects using search and filters</CardDescription>
                            </div>
                            {(searchTerm || Object.values(filters).some((v) => v)) && (
                                <Button variant="outline" size="sm" onClick={clearFilters} className="gap-2">
                                    <X className="h-4 w-4" />
                                    Clear All
                                </Button>
                            )}
                        </div>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        {/* Search */}
                        <form onSubmit={handleSearch} className="flex gap-4">
                            <div className="relative flex-1">
                                <Search className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 transform text-muted-foreground" />
                                <Input
                                    placeholder="Search projects..."
                                    value={searchTerm}
                                    onChange={(e) => setSearchTerm(e.target.value)}
                                    className="pl-10"
                                />
                            </div>
                            <Button type="submit">Search</Button>
                            <Button type="button" variant="outline" onClick={() => setShowFilters(!showFilters)}>
                                <Filter className="mr-2 h-4 w-4" />
                                Filters
                            </Button>
                        </form>

                        {/* Filters */}
                        {showFilters && (
                            <div className="grid grid-cols-1 gap-4 rounded-lg bg-muted/30 p-4 md:grid-cols-4">
                                <div>
                                    <label className="mb-1 block text-sm font-medium">Status</label>
                                    <Select
                                        value={filters.status || 'all_statuses'}
                                        onValueChange={(value) => handleFilterChange('status', value === 'all_statuses' ? '' : value)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="All statuses" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all_statuses">All statuses</SelectItem>
                                            <SelectItem value="draft">📝 Draft</SelectItem>
                                            <SelectItem value="active">🟢 Active</SelectItem>
                                            <SelectItem value="paused">⏸️ Paused</SelectItem>
                                            <SelectItem value="completed">✅ Completed</SelectItem>
                                            <SelectItem value="cancelled">❌ Cancelled</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div>
                                    <label className="mb-1 block text-sm font-medium">Visibility</label>
                                    <Select
                                        value={filters.visibility || 'all_visibility'}
                                        onValueChange={(value) => handleFilterChange('visibility', value === 'all_visibility' ? '' : value)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="All visibility" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all_visibility">All visibility</SelectItem>
                                            <SelectItem value="public">🌐 Public</SelectItem>
                                            <SelectItem value="private">🔒 Private</SelectItem>
                                            <SelectItem value="invite_only">👥 Invite Only</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div>
                                    <label className="mb-1 block text-sm font-medium">Sort By</label>
                                    <Select value={filters.sort_by || 'created_at'} onValueChange={(value) => handleFilterChange('sort_by', value)}>
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="created_at">Created Date</SelectItem>
                                            <SelectItem value="name">Name</SelectItem>
                                            <SelectItem value="total_amount">Amount</SelectItem>
                                            <SelectItem value="end_date">End Date</SelectItem>
                                            <SelectItem value="status">Status</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div>
                                    <label className="mb-1 block text-sm font-medium">Sort Direction</label>
                                    <Select
                                        value={filters.sort_direction || 'desc'}
                                        onValueChange={(value) => handleFilterChange('sort_direction', value)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="desc">Newest First</SelectItem>
                                            <SelectItem value="asc">Oldest First</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Table */}
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div>
                                <CardTitle>Projects</CardTitle>
                                <CardDescription>
                                    Showing {projects.data.length} of {projects.total} projects
                                </CardDescription>
                            </div>
                            <DropdownMenu>
                                <DropdownMenuTrigger asChild>
                                    <Button variant="outline" className="ml-auto">
                                        Columns <ChevronDown className="ml-2 h-4 w-4" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end">
                                    {table
                                        .getAllColumns()
                                        .filter((column) => column.getCanHide())
                                        .map((column) => {
                                            return (
                                                <DropdownMenuCheckboxItem
                                                    key={column.id}
                                                    className="capitalize"
                                                    checked={column.getIsVisible()}
                                                    onCheckedChange={(value) => column.toggleVisibility(!!value)}
                                                >
                                                    {column.id}
                                                </DropdownMenuCheckboxItem>
                                            );
                                        })}
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="rounded-md border">
                            <Table>
                                <TableHeader>
                                    {table.getHeaderGroups().map((headerGroup) => (
                                        <TableRow key={headerGroup.id}>
                                            {headerGroup.headers.map((header) => {
                                                return (
                                                    <TableHead key={header.id}>
                                                        {header.isPlaceholder
                                                            ? null
                                                            : flexRender(header.column.columnDef.header, header.getContext())}
                                                    </TableHead>
                                                );
                                            })}
                                        </TableRow>
                                    ))}
                                </TableHeader>
                                <TableBody>
                                    {table.getRowModel().rows?.length ? (
                                        table.getRowModel().rows.map((row) => (
                                            <TableRow key={row.id} data-state={row.getIsSelected() && 'selected'}>
                                                {row.getVisibleCells().map((cell) => (
                                                    <TableCell key={cell.id}>{flexRender(cell.column.columnDef.cell, cell.getContext())}</TableCell>
                                                ))}
                                            </TableRow>
                                        ))
                                    ) : (
                                        <TableRow>
                                            <TableCell colSpan={columns.length} className="h-24 text-center">
                                                <div className="flex flex-col items-center justify-center space-y-2">
                                                    <div className="text-4xl text-muted-foreground">📋</div>
                                                    <div className="text-lg font-medium">No projects found</div>
                                                    <div className="text-muted-foreground">Get started by creating your first project</div>
                                                    <Button asChild className="mt-4">
                                                        <Link href={route('tenant.projects.create', tenant.slug)}>
                                                            <Plus className="mr-2 h-4 w-4" />
                                                            Create Project
                                                        </Link>
                                                    </Button>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    )}
                                </TableBody>
                            </Table>
                        </div>

                        {/* Pagination */}
                        {projects.last_page > 1 && (
                            <div className="flex items-center justify-between space-x-2 py-4">
                                <div className="text-sm text-muted-foreground">
                                    Page {projects.current_page} of {projects.last_page}
                                </div>
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
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
