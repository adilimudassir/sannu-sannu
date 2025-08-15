import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import { 
  ArrowLeft, 
  Calendar, 
  DollarSign, 
  Users, 
  Eye, 
  Edit, 
  Trash2, 
  Play, 
  Pause, 
  CheckCircle,
  Clock,
  Globe,
  Lock,
  UserCheck,
  TrendingUp,
  Target,
  Image as ImageIcon
} from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Progress } from '@/components/ui/progress';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { 
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';
import { ProjectStatusBadge } from '@/components/projects';
import { formatCurrency, formatDate, formatTimeRemaining, formatPercentage } from '@/lib/formatters';
import { Project, Product, Tenant } from '@/types';

interface ProjectDetailsProps {
  project: Project;
  statistics?: {
    total_contributors: number;
    total_raised: number;
    completion_percentage: number;
    days_remaining: number;
    average_contribution: number;
  };
  canEdit: boolean;
  canDelete: boolean;
  canActivate: boolean;
  canPause: boolean;
  canComplete: boolean;
}

export default function AdminProjectShow({ 
  project, 
  statistics, 
  canEdit, 
  canDelete, 
  canActivate, 
  canPause, 
  canComplete
}: ProjectDetailsProps) {
  const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);
  const stats = statistics || project.statistics;
  const isSystemAdmin = true;

  const getBackUrl = () => {
    return route('admin.projects.index');
  };

  const getEditUrl = () => {
    return route('admin.projects.edit', project.id);
  };

  const handleDelete = () => {
    const deleteRoute = route('admin.projects.destroy', project.id);
    
    router.delete(deleteRoute, {
      onSuccess: () => {
        setDeleteDialogOpen(false);
        router.visit(getBackUrl());
      }
    });
  };

  const handleStatusChange = (action: 'activate' | 'pause' | 'complete') => {
    const actionRoute = route(`admin.projects.${action}`, project.id);
    
    router.patch(actionRoute);
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
        return 'Public';
      case 'private':
        return 'Private';
      case 'invite_only':
        return 'Invite Only';
      default:
        return 'Public';
    }
  };

  return (
    <AppLayout>
      <Head title={`${project.name} - Project Details`} />
      
      <div className="space-y-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-4">
            <Button variant="outline" size="sm" asChild>
              <Link href={getBackUrl()}>
                <ArrowLeft className="h-4 w-4 mr-2" />
                Back to Projects
              </Link>
            </Button>
            <div>
              <h1 className="text-3xl font-bold tracking-tight">{project.name}</h1>
              <p className="text-muted-foreground">
                System Admin - Project details and management
              </p>
            </div>
          </div>
          
          {/* Action Buttons */}
          <div className="flex items-center gap-2">
            {canEdit && (
              <Button variant="outline" asChild>
                <Link href={getEditUrl()}>
                  <Edit className="h-4 w-4 mr-2" />
                  Edit
                </Link>
              </Button>
            )}
            
            {canActivate && project.status === 'draft' && (
              <Button 
                onClick={() => handleStatusChange('activate')}
                className="bg-green-600 hover:bg-green-700"
              >
                <Play className="h-4 w-4 mr-2" />
                Activate
              </Button>
            )}
            
            {canPause && project.status === 'active' && (
              <Button 
                variant="outline"
                onClick={() => handleStatusChange('pause')}
              >
                <Pause className="h-4 w-4 mr-2" />
                Pause
              </Button>
            )}
            
            {canComplete && (project.status === 'active' || project.status === 'paused') && (
              <Button 
                onClick={() => handleStatusChange('complete')}
                className="bg-blue-600 hover:bg-blue-700"
              >
                <CheckCircle className="h-4 w-4 mr-2" />
                Complete
              </Button>
            )}
            
            {canDelete && (
              <Dialog open={deleteDialogOpen} onOpenChange={setDeleteDialogOpen}>
                <DialogTrigger asChild>
                  <Button variant="destructive">
                    <Trash2 className="h-4 w-4 mr-2" />
                    Delete
                  </Button>
                </DialogTrigger>
                <DialogContent>
                  <DialogHeader>
                    <DialogTitle>Delete Project</DialogTitle>
                    <DialogDescription>
                      Are you sure you want to delete "{project.name}"? This action cannot be undone.
                      All project data, products, and associated records will be permanently removed.
                    </DialogDescription>
                  </DialogHeader>
                  <DialogFooter>
                    <Button variant="outline" onClick={() => setDeleteDialogOpen(false)}>
                      Cancel
                    </Button>
                    <Button onClick={handleDelete} variant="destructive">
                      Delete Project
                    </Button>
                  </DialogFooter>
                </DialogContent>
              </Dialog>
            )}
          </div>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Main Content */}
          <div className="lg:col-span-2 space-y-6">
            {/* Project Overview */}
            <Card>
              <CardHeader>
                <div className="flex items-center justify-between">
                  <CardTitle>Project Overview</CardTitle>
                  <div className="flex items-center gap-2">
                    <ProjectStatusBadge status={project.status} />
                    <Badge variant="outline" className="gap-1">
                      {getVisibilityIcon()}
                      {getVisibilityLabel()}
                    </Badge>
                  </div>
                </div>
              </CardHeader>
              <CardContent className="space-y-4">
                {project.description && (
                  <div>
                    <h4 className="font-medium mb-2">Description</h4>
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

                {isSystemAdmin && project.tenant && (
                  <>
                    <Separator />
                    <div>
                      <h4 className="font-medium mb-2">Tenant Information</h4>
                      <div className="flex items-center gap-2 text-sm">
                        <span className="font-medium">Organization:</span>
                        <Badge variant="secondary">{project.tenant.name}</Badge>
                      </div>
                    </div>
                  </>
                )}
              </CardContent>
            </Card>

            {/* Products Gallery */}
            {project.products && project.products.length > 0 && (
              <Card>
                <CardHeader>
                  <CardTitle>Products</CardTitle>
                  <CardDescription>
                    Items and services included in this project
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {project.products.map((product: Product) => (
                      <div key={product.id} className="border rounded-lg p-4 space-y-3">
                        {product.image_url && (
                          <div className="aspect-video bg-muted rounded-md overflow-hidden">
                            <img 
                              src={product.image_url} 
                              alt={product.name}
                              className="w-full h-full object-cover"
                            />
                          </div>
                        )}
                        {!product.image_url && (
                          <div className="aspect-video bg-muted rounded-md flex items-center justify-center">
                            <ImageIcon className="h-8 w-8 text-muted-foreground" />
                          </div>
                        )}
                        <div>
                          <h4 className="font-medium">{product.name}</h4>
                          {product.description && (
                            <p className="text-sm text-muted-foreground mt-1">
                              {product.description}
                            </p>
                          )}
                          <div className="flex items-center justify-between mt-2">
                            <span className="text-lg font-semibold text-primary">
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
          </div>

          {/* Sidebar */}
          <div className="space-y-6">
            {/* Project Statistics */}
            {stats && (
              <Card>
                <CardHeader>
                  <CardTitle className="flex items-center gap-2">
                    <TrendingUp className="h-5 w-5" />
                    Project Statistics
                  </CardTitle>
                </CardHeader>
                <CardContent className="space-y-6">
                  {/* Progress */}
                  <div className="space-y-2">
                    <div className="flex justify-between text-sm">
                      <span className="text-muted-foreground">Progress</span>
                      <span className="font-medium">
                        {formatPercentage(stats.completion_percentage)}
                      </span>
                    </div>
                    <Progress value={stats.completion_percentage} className="h-2" />
                    <div className="flex justify-between text-sm text-muted-foreground">
                      <span>{formatCurrency(stats.total_raised)} raised</span>
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
                      <span className="font-semibold">{stats.total_contributors}</span>
                    </div>

                    <div className="flex items-center justify-between">
                      <div className="flex items-center gap-2">
                        <DollarSign className="h-4 w-4 text-muted-foreground" />
                        <span className="text-sm">Average Contribution</span>
                      </div>
                      <span className="font-semibold">
                        {formatCurrency(stats.average_contribution)}
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
                </CardContent>
              </Card>
            )}

            {/* Quick Actions */}
            <Card>
              <CardHeader>
                <CardTitle>Quick Actions</CardTitle>
              </CardHeader>
              <CardContent className="space-y-3">
                <Button variant="outline" className="w-full justify-start" asChild>
                  <Link href={getBackUrl()}>
                    <Eye className="h-4 w-4 mr-2" />
                    View All Projects
                  </Link>
                </Button>
                
                {canEdit && (
                  <Button variant="outline" className="w-full justify-start" asChild>
                    <Link href={getEditUrl()}>
                      <Edit className="h-4 w-4 mr-2" />
                      Edit Project
                    </Link>
                  </Button>
                )}

                {project.status === 'active' && (
                  <Button variant="outline" className="w-full justify-start">
                    <Users className="h-4 w-4 mr-2" />
                    View Contributors
                  </Button>
                )}
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
    </AppLayout>
  );
}