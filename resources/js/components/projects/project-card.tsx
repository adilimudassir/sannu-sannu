import { Link } from '@inertiajs/react';
import { Calendar, DollarSign, Users, Eye, Edit, Trash2 } from 'lucide-react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Progress } from '@/components/ui/progress';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import ProjectStatusBadge from './status-badge';
import { formatCurrency, formatDateShort } from '@/lib/formatters';

interface Tenant {
  id: number;
  name: string;
  slug: string;
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
  statistics?: ProjectStatistics;
}

interface ProjectCardProps {
  project: Project;
  showTenant?: boolean;
  showActions?: boolean;
  isSystemAdmin?: boolean;
  tenantSlug?: string;
  onDelete?: (project: Project) => void;
}

export default function ProjectCard({ 
  project, 
  showTenant = false, 
  showActions = false,
  isSystemAdmin = false,
  tenantSlug,
  onDelete 
}: ProjectCardProps) {
  const stats = project.statistics;

  const getViewUrl = () => {
    if (isSystemAdmin) {
      return route('admin.projects.show', project.id);
    }
    return route('tenant.projects.show', [tenantSlug, project.id]);
  };

  const getEditUrl = () => {
    if (isSystemAdmin) {
      return route('admin.projects.edit', project.id);
    }
    return route('tenant.projects.edit', [tenantSlug, project.id]);
  };

  const handleDelete = () => {
    if (onDelete) {
      onDelete(project);
    }
  };

  return (
    <Card className="hover:shadow-lg transition-shadow">
      <CardHeader className="pb-3">
        <div className="flex items-start justify-between">
          <div className="flex-1 min-w-0">
            <CardTitle className="text-lg line-clamp-2">
              <Link
                href={getViewUrl()}
                className="hover:text-primary transition-colors"
              >
                {project.name}
              </Link>
            </CardTitle>
            {showTenant && project.tenant && (
              <CardDescription className="mt-1">
                by {project.tenant.name}
              </CardDescription>
            )}
          </div>
          <div className="flex items-center gap-2 ml-2">
            <ProjectStatusBadge status={project.status} />
            {showActions && (
              <DropdownMenu>
                <DropdownMenuTrigger asChild>
                  <Button variant="ghost" size="sm" className="h-8 w-8 p-0">
                    <span className="sr-only">Open menu</span>
                    <Eye className="h-4 w-4" />
                  </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                  <DropdownMenuLabel>Actions</DropdownMenuLabel>
                  <DropdownMenuItem asChild>
                    <Link href={getViewUrl()}>
                      <Eye className="mr-2 h-4 w-4" />
                      View Details
                    </Link>
                  </DropdownMenuItem>
                  <DropdownMenuItem asChild>
                    <Link href={getEditUrl()}>
                      <Edit className="mr-2 h-4 w-4" />
                      Edit Project
                    </Link>
                  </DropdownMenuItem>
                  <DropdownMenuSeparator />
                  <DropdownMenuItem
                    className="text-destructive"
                    onClick={handleDelete}
                  >
                    <Trash2 className="mr-2 h-4 w-4" />
                    Delete Project
                  </DropdownMenuItem>
                </DropdownMenuContent>
              </DropdownMenu>
            )}
          </div>
        </div>
      </CardHeader>

      <CardContent className="space-y-4">
        {project.description && (
          <p className="text-sm text-muted-foreground line-clamp-3">
            {project.description}
          </p>
        )}

        {/* Progress */}
        {stats && (
          <div className="space-y-2">
            <div className="flex justify-between text-sm">
              <span className="text-muted-foreground">Progress</span>
              <span className="font-medium">
                {stats.completion_percentage}%
              </span>
            </div>
            <Progress value={stats.completion_percentage} />
            <div className="flex justify-between text-sm text-muted-foreground">
              <span>
                {formatCurrency(stats.total_raised)} raised
              </span>
              <span>
                of {formatCurrency(project.total_amount)}
              </span>
            </div>
          </div>
        )}

        {/* Stats */}
        <div className="grid grid-cols-3 gap-4 text-center text-sm">
          <div>
            <div className="flex items-center justify-center text-muted-foreground mb-1">
              <Users className="h-4 w-4" />
            </div>
            <div className="font-medium">
              {stats?.total_contributors || 0}
            </div>
            <div className="text-muted-foreground text-xs">
              Contributors
            </div>
          </div>
          <div>
            <div className="flex items-center justify-center text-muted-foreground mb-1">
              <DollarSign className="h-4 w-4" />
            </div>
            <div className="font-medium text-xs">
              {formatCurrency(project.total_amount)}
            </div>
            <div className="text-muted-foreground text-xs">
              Goal
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
          <Link href={getViewUrl()}>
            View Project
          </Link>
        </Button>
      </CardContent>
    </Card>
  );
}