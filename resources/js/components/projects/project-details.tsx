import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { formatCurrency, formatDate } from '@/lib/formatters';
import { Link } from '@inertiajs/react';
import { Calendar } from 'lucide-react';
import { Project } from '@/types';

interface Props {
    project: Project;
}

export default function ProjectDetails({ project }: Props) {
    return (
        <div className="lg:col-span-2 space-y-6">
            {/* Project Header */}
            <Card>
                <CardHeader>
                    <div className="flex items-start justify-between">
                        <div className="flex-1">
                            <CardTitle className="text-2xl mb-2">
                                {project.name}
                            </CardTitle>
                                            <CardDescription className="text-base">
                                                by {project.tenant && (
                                                    <Link 
                                                        href="#" 
                                                        className="font-medium text-blue-600 hover:text-blue-800"
                                                    >
                                                        {project.tenant.name}
                                                    </Link>
                                                )}
                                            </CardDescription>
                        </div>
                        <Badge variant="secondary" className="ml-4">
                            {project.status}
                        </Badge>
                    </div>
                </CardHeader>

                <CardContent>
                    {project.description && (
                        <div className="prose max-w-none">
                            <p className="text-muted-foreground leading-relaxed">
                                {project.description}
                            </p>
                        </div>
                    )}
                </CardContent>
            </Card>

            {/* Products */}
            {project.products && project.products.length > 0 && (
                <Card>
                    <CardHeader>
                        <CardTitle>What You're Contributing To</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-6">
                            {project.products.map((product, index) => (
                                <div key={product.id}>
                                    {index > 0 && <Separator className="my-6" />}
                                    <div className="flex gap-4">
                                        {product.image_url && (
                                            <div className="flex-shrink-0">
                                                <img
                                                    src={product.image_url}
                                                    alt={product.name}
                                                    className="w-24 h-24 object-cover rounded-lg"
                                                />
                                            </div>
                                        )}
                                        <div className="flex-1">
                                            <h3 className="font-semibold text-lg mb-1">
                                                {product.name}
                                            </h3>
                                            <p className="text-2xl font-bold text-green-600 mb-2">
                                                {formatCurrency(product.price)}
                                            </p>
                                            {product.description && (
                                                <p className="text-muted-foreground">
                                                    {product.description}
                                                </p>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>
            )}

            {/* Project Timeline */}
            <Card>
                <CardHeader>
                    <CardTitle>Project Timeline</CardTitle>
                </CardHeader>
                <CardContent>
                    <div className="space-y-4">
                        <div className="flex items-center gap-3">
                            <Calendar className="h-5 w-5 text-muted-foreground" />
                            <div>
                                <div className="font-medium">Start Date</div>
                                <div className="text-muted-foreground">
                                    {formatDate(project.start_date)}
                                </div>
                            </div>
                        </div>
                        <div className="flex items-center gap-3">
                            <Calendar className="h-5 w-5 text-muted-foreground" />
                            <div>
                                <div className="font-medium">End Date</div>
                                <div className="text-muted-foreground">
                                    {formatDate(project.end_date)}
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    );
}
