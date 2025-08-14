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
import { ArrowUpDown, Calendar, ChevronDown, DollarSign, Eye, Globe, Lock, MoreHorizontal, Plus, UserPlus, Users } from 'lucide-react';
import { useState } from 'react';

import { ProjectFilters as ProjectFiltersComponent, ProjectStatusBadge } from '@/components/projects';
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
    tenant_id?: string;
    sort_by?: string;
    sort_direction?: string;
}

interface Props {
    projects: PaginatedData<Project>;
    filters: ProjectFilters;
    auth: {
        user: User & {
            is_system_admin?: boolean;
            is_tenant_admin?: boolean;
        };
    };
    tenant?: Tenant;
    tenants?: Tenant[];
}

const visibilityConfig = {
    public: { label: 'Public', icon: Globe, color: 'text-green-600' },
    private: { label: 'Private', icon: Lock, color: 'text-yellow-600' },
    invite_only: { label: 'Invite Only', icon: UserPlus, color: 'text-blue-600' },
};

export default function ProjectIndex({ projects, filters, auth, tenant, tenants }: Props) {
    const [sorting, setSorting] = useState<SortingState>([]);
    const [columnFilters, setColumnFilters] = useState<ColumnFiltersState>([]);
    const [columnVisibility, setColumnVisibility] = useState<VisibilityState>({});
    const [rowSelection, setRowSelection] = useState({});
    const [globalFilter, setGlobalFilter] = useState(filters.search || '');

    const isSystemAdmin = auth.user.is_system_admin;
    const isTenantAdmin = auth.user.is_tenant_admin;

    const columns: ColumnDef<Project>[] = [
        {
            accessorKey: 'name',
            header: ({ column }) => {
                return (
                    <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')} className="h-auto p-0 font-medium">
                        Project Name
                        <ArrowUpDown className="ml-2 h-4 w-4" />
                    </Button>
                );
            },
            cell: ({ row }) => {
                const project = row.original;
                return (
                    <div className="space-y-1">
                        <Link
                            href={route(
                                isSystemAdmin ? 'admin.projects.show' : 'tenant.projects.show',
                                isSystemAdmin ? project.id : [tenant?.slug || '', project.id],
                            )}
                            className="font-medium text-primary hover:underline"
                        >
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
                    <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')} className="h-auto p-0 font-medium">
                        <DollarSign className="mr-2 h-4 w-4" />
                        Total Amount
                        <ArrowUpDown className="ml-2 h-4 w-4" />
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
                    <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')} className="h-auto p-0 font-medium">
                        <Calendar className="mr-2 h-4 w-4" />
                        End Date
                        <ArrowUpDown className="ml-2 h-4 w-4" />
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
        // Show tenant column only for system admin
        ...(isSystemAdmin
            ? [
                  {
                      accessorKey: 'tenant',
                      header: 'Organization',
                      cell: ({ row }: { row: any }) => {
                          const tenant = row.original.tenant;
                          return tenant ? (
                              <div className="text-sm">
                                  <div className="font-medium">{tenant.name}</div>
                                  <div className="text-muted-foreground">@{tenant.slug}</div>
                              </div>
                          ) : (
                              <span className="text-muted-foreground">-</span>
                          );
                      },
                  },
              ]
            : []),
        {
            id: 'actions',
            enableHiding: false,
            cell: ({ row }) => {
                const project = row.original;

                return (
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <Button variant="ghost" className="h-8 w-8 p-0">
                                <span className="sr-only">Open menu</span>
                                <MoreHorizontal className="h-4 w-4" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            <DropdownMenuLabel>Actions</DropdownMenuLabel>
                            <DropdownMenuItem asChild>
                                <Link
                                    href={route(
                                        isSystemAdmin ? 'admin.projects.show' : 'tenant.projects.show',
                                        isSystemAdmin ? project.id : [tenant?.slug || '', project.id],
                                    )}
                                >
                                    <Eye className="mr-2 h-4 w-4" />
                                    View Details
                                </Link>
                            </DropdownMenuItem>
                            <DropdownMenuItem asChild>
                                <Link
                                    href={route(
                                        isSystemAdmin ? 'admin.projects.edit' : 'tenant.projects.edit',
                                        isSystemAdmin ? project.id : [tenant?.slug || '', project.id],
                                    )}
                                >
                                    Edit Project
                                </Link>
                            </DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem
                                className="text-destructive"
                                onClick={() => {
                                    if (confirm('Are you sure you want to delete this project?')) {
                                        router.delete(
                                            route(
                                                isSystemAdmin ? 'admin.projects.destroy' : 'tenant.projects.destroy',
                                                isSystemAdmin ? project.id : [tenant?.slug || '', project.id],
                                            ),
                                        );
                                    }
                                }}
                            >
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
        onGlobalFilterChange: setGlobalFilter,
        globalFilterFn: 'includesString',
        state: {
            sorting,
            columnFilters,
            columnVisibility,
            rowSelection,
            globalFilter,
        },
    });

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(
            route(isSystemAdmin ? 'admin.projects.index' : 'tenant.projects.index', isSystemAdmin ? {} : tenant?.slug),
            {
                ...filters,
                search: globalFilter,
            },
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    const handleFilterChange = (key: string, value: string) => {
        router.get(
            route(isSystemAdmin ? 'admin.projects.index' : 'tenant.projects.index', isSystemAdmin ? {} : tenant?.slug),
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
        router.get(
            route(isSystemAdmin ? 'admin.projects.index' : 'tenant.projects.index', isSystemAdmin ? {} : tenant?.slug),
            {},
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    return (
        <AppLayout>
            <Head title={isSystemAdmin ? 'All Projects' : 'Projects'} />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">{isSystemAdmin ? 'All Projects' : 'Projects'}</h1>
                        <p className="text-muted-foreground">
                            {isSystemAdmin ? 'Manage projects across all organizations' : "Manage your organization's contribution projects"}
                        </p>
                    </div>
                    <Button asChild>
                        <Link href={route(isSystemAdmin ? 'admin.projects.create' : 'tenant.projects.create', isSystemAdmin ? {} : tenant?.slug)}>
                            <Plus className="mr-2 h-4 w-4" />
                            New Project
                        </Link>
                    </Button>
                </div>

                {/* Filters and Search */}
                <ProjectFiltersComponent
                    filters={filters}
                    onSearch={(search) => {
                        router.get(
                            route(isSystemAdmin ? 'admin.projects.index' : 'tenant.projects.index', isSystemAdmin ? {} : tenant?.slug),
                            {
                                ...filters,
                                search,
                            },
                            {
                                preserveState: true,
                                replace: true,
                            },
                        );
                    }}
                    onFilterChange={handleFilterChange}
                    onClearFilters={clearFilters}
                    showTenantFilter={isSystemAdmin}
                    tenants={tenants}
                    searchValue={globalFilter}
                    onSearchValueChange={setGlobalFilter}
                />

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
                                                    <div className="text-muted-foreground">
                                                        {globalFilter || Object.values(filters).some((v) => v)
                                                            ? 'Try adjusting your search or filters'
                                                            : 'Get started by creating your first project'}
                                                    </div>
                                                    {!globalFilter && !Object.values(filters).some((v) => v) && (
                                                        <Button asChild className="mt-4">
                                                            <Link
                                                                href={route(
                                                                    isSystemAdmin ? 'admin.projects.create' : 'tenant.projects.create',
                                                                    isSystemAdmin ? {} : tenant?.slug,
                                                                )}
                                                            >
                                                                <Plus className="mr-2 h-4 w-4" />
                                                                Create Project
                                                            </Link>
                                                        </Button>
                                                    )}
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
