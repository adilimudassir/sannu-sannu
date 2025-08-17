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
    Filter,
    MoreHorizontal,
    Plus,
    Search,
    X,
    Building2,
    Mail,
    User,
    FileCheck,
    Clock,
    CheckCircle2,
    XCircle,
    Eye,
} from 'lucide-react';
import { useState } from 'react';

import { Badge } from '@/components/ui/badge';
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
import { formatDateShort } from '@/lib/formatters';

interface TenantApplication {
    id: number;
    reference_number: string;
    organization_name: string;
    contact_person_name: string;
    contact_person_email: string;
    business_registration_number: string;
    status: 'pending' | 'approved' | 'rejected';
    submitted_at: string;
    created_at: string;
    updated_at: string;
}

interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: Array<{
        url?: string | null;
        label: string;
        active: boolean;
    }>;
}

interface ApplicationFilters {
    search?: string;
    status?: string;
    sort?: string;
    direction?: string;
}

interface Props {
    applications: PaginatedData<TenantApplication>;
    filters: ApplicationFilters;
}

const statusConfig = {
    pending: { label: 'Pending', icon: Clock, color: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800/20 dark:text-yellow-400' },
    approved: { label: 'Approved', icon: CheckCircle2, color: 'bg-green-100 text-green-800 dark:bg-green-800/20 dark:text-green-400' },
    rejected: { label: 'Rejected', icon: XCircle, color: 'bg-red-100 text-red-800 dark:bg-red-800/20 dark:text-red-400' },
};

export default function TenantApplicationsList({ applications, filters }: Props) {
    const [sorting, setSorting] = useState<SortingState>([]);
    const [columnFilters, setColumnFilters] = useState<ColumnFiltersState>([]);
    const [columnVisibility, setColumnVisibility] = useState<VisibilityState>({});
    const [rowSelection, setRowSelection] = useState({});
    const [searchTerm, setSearchTerm] = useState(filters.search || '');
    const [showFilters, setShowFilters] = useState(false);

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(
            route('admin.tenant-applications.index'),
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
            route('admin.tenant-applications.index'),
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
            route('admin.tenant-applications.index'),
            {},
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    const columns: ColumnDef<TenantApplication>[] = [
        {
            accessorKey: 'reference_number',
            header: ({ column }) => {
                return (
                    <Button
                        variant="ghost"
                        onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                        className="-ml-4 h-auto p-0 pl-4 font-medium"
                    >
                        Reference #
                        <ArrowUpDown className="ml-2 h-4 w-4 opacity-50" />
                    </Button>
                );
            },
            cell: ({ row }) => {
                const app = row.original;
                return <div className="font-mono">{app.reference_number}</div>;
            },
        },
        {
            accessorKey: 'organization_name',
            header: ({ column }) => {
                return (
                    <Button
                        variant="ghost"
                        onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                        className="-ml-4 h-auto p-0 pl-4 font-medium"
                    >
                        Organization
                        <ArrowUpDown className="ml-2 h-4 w-4 opacity-50" />
                    </Button>
                );
            },
            cell: ({ row }) => {
                const app = row.original;
                return (
                    <div className="flex items-center gap-2">
                        <Building2 className="h-4 w-4 text-muted-foreground" />
                        <div className="font-medium">{app.organization_name}</div>
                    </div>
                );
            },
        },
        {
            accessorKey: 'contact',
            header: 'Contact',
            cell: ({ row }) => {
                const app = row.original;
                return (
                    <div className="space-y-1">
                        <div className="flex items-center gap-2">
                            <User className="h-4 w-4 text-muted-foreground" />
                            <span>{app.contact_person_name}</span>
                        </div>
                        <div className="flex items-center gap-2 text-sm text-muted-foreground">
                            <Mail className="h-3 w-3" />
                            <span>{app.contact_person_email}</span>
                        </div>
                    </div>
                );
            },
        },
        {
            accessorKey: 'status',
            header: ({ column }) => {
                return (
                    <Button
                        variant="ghost"
                        onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                        className="-ml-4 h-auto p-0 pl-4 font-medium"
                    >
                        Status
                        <ArrowUpDown className="ml-2 h-4 w-4 opacity-50" />
                    </Button>
                );
            },
            cell: ({ row }) => {
                const status = row.getValue('status') as keyof typeof statusConfig;
                const config = statusConfig[status];
                const Icon = config.icon;
                return (
                    <div className="flex items-center gap-2">
                        <Badge className={config.color}>
                            <Icon className="mr-1 h-3 w-3" />
                            {config.label}
                        </Badge>
                    </div>
                );
            },
        },
        {
            accessorKey: 'submitted_at',
            header: ({ column }) => {
                return (
                    <Button
                        variant="ghost"
                        onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                        className="-ml-4 h-auto p-0 pl-4 font-medium"
                    >
                        <Calendar className="mr-2 h-4 w-4 opacity-50" />
                        Submitted
                        <ArrowUpDown className="ml-2 h-4 w-4 opacity-50" />
                    </Button>
                );
            },
            cell: ({ row }) => {
                const date = row.getValue('submitted_at') as string;
                return <div className="text-sm">{formatDateShort(date)}</div>;
            },
        },
        {
            id: 'actions',
            enableHiding: false,
            cell: ({ row }) => {
                const app = row.original;

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
                                <Link href={route('admin.tenant-applications.show', app.id)} className="flex items-center">
                                    <Eye className="mr-2 h-4 w-4" />
                                    Review Application
                                </Link>
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                );
            },
        },
    ];
    
    const table = useReactTable({
        data: applications.data,
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
            <Head title="Tenant Applications - Admin" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Tenant Applications</h1>
                        <p className="text-muted-foreground">Review and manage tenant applications</p>
                    </div>
                </div>

                {/* Filters and Search */}
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div>
                                <CardTitle className="text-lg">Search & Filter</CardTitle>
                                <CardDescription>Find applications using search and filters</CardDescription>
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
                                    placeholder="Search by organization, email, reference..."
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
                            <div className="grid grid-cols-1 gap-4 rounded-lg bg-muted/30 p-4 md:grid-cols-3">
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
                                            <SelectItem value="pending">⏳ Pending</SelectItem>
                                            <SelectItem value="approved">✅ Approved</SelectItem>
                                            <SelectItem value="rejected">❌ Rejected</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div>
                                    <label className="mb-1 block text-sm font-medium">Sort By</label>
                                    <Select
                                        value={filters.sort || 'submitted_at'}
                                        onValueChange={(value) => handleFilterChange('sort', value)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="submitted_at">Submission Date</SelectItem>
                                            <SelectItem value="organization_name">Organization</SelectItem>
                                            <SelectItem value="reference_number">Reference #</SelectItem>
                                            <SelectItem value="status">Status</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div>
                                    <label className="mb-1 block text-sm font-medium">Sort Direction</label>
                                    <Select
                                        value={filters.direction || 'desc'}
                                        onValueChange={(value) => handleFilterChange('direction', value)}
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
                                <CardTitle>Applications</CardTitle>
                                <CardDescription>
                                    Showing {applications.data.length} of {applications.total} applications
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
                                                    <TableCell key={cell.id}>
                                                        {flexRender(cell.column.columnDef.cell, cell.getContext())}
                                                    </TableCell>
                                                ))}
                                            </TableRow>
                                        ))
                                    ) : (
                                        <TableRow>
                                            <TableCell colSpan={columns.length} className="h-24 text-center">
                                                <div className="flex flex-col items-center justify-center space-y-2">
                                                    <div className="text-4xl text-muted-foreground">📋</div>
                                                    <div className="text-lg font-medium">No applications found</div>
                                                    <div className="text-muted-foreground">
                                                        Adjust your filters to find what you're looking for
                                                    </div>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    )}
                                </TableBody>
                            </Table>
                        </div>

                        {/* Pagination */}
                        {applications.last_page > 1 && (
                            <div className="flex items-center justify-between space-x-2 py-4">
                                <div className="text-sm text-muted-foreground">
                                    Page {applications.current_page} of {applications.last_page}
                                </div>
                                <div className="flex space-x-1">
                                    {applications.links.map((link, index) => (
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
