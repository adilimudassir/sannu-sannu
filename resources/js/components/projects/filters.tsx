import { Search, Filter, X } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

interface Tenant {
  id: number;
  name: string;
  slug: string;
}

interface ProjectFilters {
  search?: string;
  status?: string;
  visibility?: string;
  tenant_id?: string;
  sort_by?: string;
  sort_direction?: string;
}

interface ProjectFiltersProps {
  filters: ProjectFilters;
  onSearch: (search: string) => void;
  onFilterChange: (key: string, value: string) => void;
  onClearFilters: () => void;
  showTenantFilter?: boolean;
  tenants?: Tenant[];
  searchValue: string;
  onSearchValueChange: (value: string) => void;
}

export default function ProjectFiltersComponent({
  filters,
  onSearch,
  onFilterChange,
  onClearFilters,
  showTenantFilter = false,
  tenants = [],
  searchValue,
  onSearchValueChange,
}: ProjectFiltersProps) {
  const handleSearchSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onSearch(searchValue);
  };

  const hasActiveFilters = Object.values(filters).some(value => value && value !== '');

  return (
    <Card>
      <CardHeader>
        <div className="flex items-center justify-between">
          <div>
            <CardTitle className="text-lg">Search & Filter</CardTitle>
            <CardDescription>
              Find projects using search and filters
            </CardDescription>
          </div>
          {hasActiveFilters && (
            <Button
              variant="outline"
              size="sm"
              onClick={onClearFilters}
              className="gap-2"
            >
              <X className="h-4 w-4" />
              Clear All
            </Button>
          )}
        </div>
      </CardHeader>
      <CardContent className="space-y-4">
        {/* Search */}
        <form onSubmit={handleSearchSubmit} className="flex gap-4">
          <div className="flex-1 relative">
            <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4" />
            <Input
              placeholder="Search projects..."
              value={searchValue}
              onChange={(e) => onSearchValueChange(e.target.value)}
              className="pl-10"
            />
          </div>
          <Button type="submit">Search</Button>
        </form>

        {/* Filters */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label className="block text-sm font-medium mb-1">Status</label>
            <Select
              value={filters.status || 'all_statuses'}
              onValueChange={(value) => onFilterChange('status', value === 'all_statuses' ? '' : value)}
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
            <label className="block text-sm font-medium mb-1">Visibility</label>
            <Select
              value={filters.visibility || 'all_visibility'}
              onValueChange={(value) => onFilterChange('visibility', value === 'all_visibility' ? '' : value)}
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

          {showTenantFilter && tenants.length > 0 && (
            <div>
              <label className="block text-sm font-medium mb-1">Organization</label>
              <Select
                value={filters.tenant_id || 'all_tenants'}
                onValueChange={(value) => onFilterChange('tenant_id', value === 'all_tenants' ? '' : value)}
              >
                <SelectTrigger>
                  <SelectValue placeholder="All organizations" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all_tenants">All organizations</SelectItem>
                  {tenants.map((tenant) => (
                    <SelectItem key={tenant.id} value={tenant.id.toString()}>
                      {tenant.name}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>
          )}

          <div>
            <label className="block text-sm font-medium mb-1">Sort By</label>
            <Select
              value={filters.sort_by || 'created_at'}
              onValueChange={(value) => onFilterChange('sort_by', value)}
            >
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
        </div>

        {/* Sort Direction */}
        <div className="flex items-center gap-4">
          <div className="flex-1">
            <label className="block text-sm font-medium mb-1">Sort Order</label>
            <Select
              value={filters.sort_direction || 'desc'}
              onValueChange={(value) => onFilterChange('sort_direction', value)}
            >
              <SelectTrigger className="w-48">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="desc">Descending</SelectItem>
                <SelectItem value="asc">Ascending</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>
      </CardContent>
    </Card>
  );
}