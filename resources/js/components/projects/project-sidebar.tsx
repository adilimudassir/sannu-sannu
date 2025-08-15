import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';
import { Separator } from '@/components/ui/separator';
import { formatCurrency } from '@/lib/formatters';
import { Project, ProjectStatistics } from '@/types';

interface Props {
    project: Project;
    statistics: ProjectStatistics;
}

export default function ProjectSidebar({ project, statistics }: Props) {
    return (
        <div className="space-y-6">
            {/* Progress Card */}
            <Card>
                <CardHeader>
                    <CardTitle>Project Progress</CardTitle>
                </CardHeader>
                <CardContent className="space-y-4">
                    <div className="text-center">
                        <div className="text-3xl font-bold text-green-600 mb-1">
                            {statistics.completion_percentage}%
                        </div>
                        <div className="text-muted-foreground">funded</div>
                    </div>

                    <Progress value={statistics.completion_percentage} className="h-3" />

                    <div className="space-y-3">
                        <div className="flex justify-between">
                            <span className="text-muted-foreground">Raised</span>
                            <span className="font-semibold">
                                {formatCurrency(statistics.total_raised)}
                            </span>
                        </div>
                        <div className="flex justify-between">
                            <span className="text-muted-foreground">Goal</span>
                            <span className="font-semibold">
                                {formatCurrency(project.total_amount)}
                            </span>
                        </div>
                        <Separator />
                        <div className="flex justify-between">
                            <span className="text-muted-foreground">Contributors</span>
                            <span className="font-semibold">
                                {statistics.total_contributors}
                            </span>
                        </div>
                        <div className="flex justify-between">
                            <span className="text-muted-foreground">Days remaining</span>
                            <span className="font-semibold">
                                {statistics.days_remaining}
                            </span>
                        </div>
                        <div className="flex justify-between">
                            <span className="text-muted-foreground">Average contribution</span>
                            <span className="font-semibold">
                                {formatCurrency(statistics.average_contribution)}
                            </span>
                        </div>
                    </div>

                    <Button className="w-full" size="lg">
                        Contribute Now
                    </Button>
                </CardContent>
            </Card>

            {/* Organization Info */}
            <Card>
                <CardHeader>
                    <CardTitle>About the Organization</CardTitle>
                </CardHeader>
                <CardContent>
                    <div className="space-y-3">
                        <div>
                            {project.tenant && (
                                <>
                                    <div className="font-medium">{project.tenant.name}</div>
                                    <div className="text-sm text-muted-foreground">Organization</div>
                                </>
                            )}
                        </div>
                        <div>
                            {project.creator && (
                                <>
                                    <div className="font-medium">{project.creator.name}</div>
                                    <div className="text-sm text-muted-foreground">Project Creator</div>
                                </>
                            )}
                        </div>
                    </div>
                </CardContent>
            </Card>

            {/* Quick Stats */}
            <Card>
                <CardHeader>
                    <CardTitle>Quick Stats</CardTitle>
                </CardHeader>
                <CardContent>
                    <div className="grid grid-cols-2 gap-4 text-center">
                        <div>
                            <div className="text-2xl font-bold text-blue-600">
                                {statistics.total_contributors}
                            </div>
                            <div className="text-sm text-muted-foreground">Contributors</div>
                        </div>
                        <div>
                            <div className="text-2xl font-bold text-green-600">
                                {statistics.days_remaining}
                            </div>
                            <div className="text-sm text-muted-foreground">Days Left</div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    );
}
