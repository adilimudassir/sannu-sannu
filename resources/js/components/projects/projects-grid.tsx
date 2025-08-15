import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';
import { formatCurrency, formatDateShort } from '@/lib/formatters';
import { Link, router } from '@inertiajs/react';
import { Calendar, Clock, Users } from 'lucide-react';
import { PaginatedData, Project } from '@/types';

interface Props {
    projects: PaginatedData<Project>;
    routePath: string;
}

export default function ProjectsGrid({ projects, routePath }: Props) {
    return (
        <>
            {projects.data.length === 0 ? (
                <div className="py-12 text-center">
                    <div className="mb-4 text-6xl text-muted-foreground">🔍</div>
                    <h3 className="mb-2 text-lg font-medium text-foreground">No projects found</h3>
                    <p className="text-muted-foreground">Try adjusting your search criteria or filters.</p>
                </div>
            ) : (
                <>
                    <div className="mb-6 text-sm text-muted-foreground">
                        Showing {projects.data.length} of {projects.total} projects
                    </div>

                    <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                        {projects.data.map((project) => (
                            <Card key={project.id} className="transition-shadow hover:shadow-lg">
                                <CardHeader className="pb-3">
                                    <div className="flex items-start justify-between">
                                        <div className="flex-1">
                                            <CardTitle className="line-clamp-2 text-lg">
                                                <Link
                                                    href={route(routePath, project.slug)}
                                                    className="transition-colors hover:text-primary"
                                                >
                                                    {project.name}
                                                </Link>
                                            </CardTitle>
                                            {project.tenant && <CardDescription className="mt-1">by {project.tenant.name}</CardDescription>}
                                        </div>
                                        <Badge variant="secondary">{project.status}</Badge>
                                    </div>
                                </CardHeader>

                                <CardContent className="space-y-4">
                                    {project.description && <p className="line-clamp-3 text-sm text-muted-foreground">{project.description}</p>}

                                    {/* Project Image */}
                                    {project.products && project.products.length > 0 && project.products[0].image_url && (
                                        <div className="aspect-video overflow-hidden rounded-lg bg-muted">
                                            <img
                                                src={project.products[0].image_url}
                                                alt={project.products[0].name}
                                                className="h-full w-full object-cover"
                                            />
                                        </div>
                                    )}

                                    {/* Progress */}
                                    {project.statistics && (
                                        <div className="space-y-2">
                                            <div className="flex justify-between text-sm">
                                                <span className="text-muted-foreground">Progress</span>
                                                <span className="font-medium">{project.statistics.completion_percentage}%</span>
                                            </div>
                                            <Progress value={project.statistics.completion_percentage} />
                                            <div className="flex justify-between text-sm text-muted-foreground">
                                                <span>{formatCurrency(project.statistics.total_raised)} raised</span>
                                                <span>of {formatCurrency(project.total_amount)}</span>
                                            </div>
                                        </div>
                                    )}

                                    {/* Stats */}
                                    {project.statistics && (
                                        <div className="grid grid-cols-3 gap-4 text-center text-sm">
                                            <div>
                                                <div className="mb-1 flex items-center justify-center text-muted-foreground">
                                                    <Users className="h-4 w-4" />
                                                </div>
                                                <div className="font-medium">{project.statistics.total_contributors}</div>
                                                <div className="text-xs text-muted-foreground">Contributors</div>
                                            </div>
                                            <div>
                                                <div className="mb-1 flex items-center justify-center text-muted-foreground">
                                                    <Clock className="h-4 w-4" />
                                                </div>
                                                <div className="font-medium">{project.statistics.days_remaining}</div>
                                                <div className="text-xs text-muted-foreground">Days left</div>
                                            </div>
                                            <div>
                                                <div className="mb-1 flex items-center justify-center text-muted-foreground">
                                                    <Calendar className="h-4 w-4" />
                                                </div>
                                                <div className="text-xs font-medium">{formatDateShort(project.end_date)}</div>
                                                <div className="text-xs text-muted-foreground">End date</div>
                                            </div>
                                        </div>
                                    )}

                                    <Button asChild className="w-full">
                                        <Link href={route(routePath, project.slug)}>View Project</Link>
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
        </>
    );
}
