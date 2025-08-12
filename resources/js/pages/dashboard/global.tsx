import { Head } from '@inertiajs/react';
import { Search, Filter, Heart, Clock, Users } from 'lucide-react';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

export default function GlobalDashboard() {
    // Mock data - in real app this would come from props
    const publicProjects = [
        {
            id: 1,
            title: 'Community Garden Project',
            description: 'Building a sustainable community garden for local food production',
            tenant: { name: 'Green Initiative', slug: 'green-initiative' },
            target_amount: 500000,
            current_amount: 325000,
            contributors_count: 45,
            days_remaining: 12,
            status: 'active'
        },
        {
            id: 2,
            title: 'Tech Education Program',
            description: 'Providing coding bootcamp scholarships for underserved communities',
            tenant: { name: 'TechForAll', slug: 'tech-for-all' },
            target_amount: 1000000,
            current_amount: 750000,
            contributors_count: 89,
            days_remaining: 25,
            status: 'active'
        },
    ];

    const myContributions = [
        {
            id: 1,
            project_title: 'Community Garden Project',
            tenant_name: 'Green Initiative',
            amount_contributed: 25000,
            next_payment_due: '2025-08-15',
            status: 'active'
        },
    ];

    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('en-NG', {
            style: 'currency',
            currency: 'NGN',
        }).format(amount);
    };

    const getProgressPercentage = (current: number, target: number) => {
        return Math.round((current / target) * 100);
    };

    return (
        <>
            <Head title="Dashboard" />
            
            <div className="min-h-screen bg-background">
                {/* Header */}
                <div className="border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
                    <div className="container mx-auto px-4 py-4">
                        <div className="flex items-center justify-between">
                            <div>
                                <h1 className="text-2xl font-semibold">Dashboard</h1>
                                <p className="text-muted-foreground">Discover and contribute to projects</p>
                            </div>
                            <Button>
                                <Heart className="h-4 w-4 mr-2" />
                                My Contributions
                            </Button>
                        </div>
                    </div>
                </div>

                <div className="container mx-auto px-4 py-8">
                    <div className="grid gap-8">
                        {/* My Contributions Section */}
                        {myContributions.length > 0 && (
                            <section>
                                <h2 className="text-xl font-semibold mb-4">My Active Contributions</h2>
                                <div className="grid gap-4">
                                    {myContributions.map((contribution) => (
                                        <Card key={contribution.id}>
                                            <CardContent className="p-6">
                                                <div className="flex items-center justify-between">
                                                    <div>
                                                        <h3 className="font-semibold">{contribution.project_title}</h3>
                                                        <p className="text-sm text-muted-foreground">
                                                            by {contribution.tenant_name}
                                                        </p>
                                                        <p className="text-sm mt-1">
                                                            Contributed: {formatCurrency(contribution.amount_contributed)}
                                                        </p>
                                                    </div>
                                                    <div className="text-right">
                                                        <Badge variant="outline">
                                                            Next payment: {contribution.next_payment_due}
                                                        </Badge>
                                                    </div>
                                                </div>
                                            </CardContent>
                                        </Card>
                                    ))}
                                </div>
                            </section>
                        )}

                        {/* Public Projects Section */}
                        <section>
                            <div className="flex items-center justify-between mb-6">
                                <h2 className="text-xl font-semibold">Discover Projects</h2>
                                <div className="flex gap-2">
                                    <div className="relative">
                                        <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                        <Input 
                                            placeholder="Search projects..." 
                                            className="pl-10 w-64"
                                        />
                                    </div>
                                    <Button variant="outline" size="icon">
                                        <Filter className="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>

                            <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                                {publicProjects.map((project) => (
                                    <Card key={project.id} className="cursor-pointer hover:shadow-md transition-shadow">
                                        <CardHeader>
                                            <div className="flex items-start justify-between">
                                                <div>
                                                    <CardTitle className="text-lg">{project.title}</CardTitle>
                                                    <CardDescription className="text-sm text-muted-foreground">
                                                        by {project.tenant.name}
                                                    </CardDescription>
                                                </div>
                                                <Badge variant="secondary">Active</Badge>
                                            </div>
                                        </CardHeader>
                                        <CardContent>
                                            <p className="text-sm text-muted-foreground mb-4">
                                                {project.description}
                                            </p>
                                            
                                            {/* Progress Bar */}
                                            <div className="mb-4">
                                                <div className="flex justify-between text-sm mb-2">
                                                    <span>{formatCurrency(project.current_amount)}</span>
                                                    <span>{formatCurrency(project.target_amount)}</span>
                                                </div>
                                                <div className="w-full bg-secondary rounded-full h-2">
                                                    <div 
                                                        className="bg-primary h-2 rounded-full transition-all"
                                                        style={{ 
                                                            width: `${getProgressPercentage(project.current_amount, project.target_amount)}%` 
                                                        }}
                                                    ></div>
                                                </div>
                                                <div className="text-xs text-muted-foreground mt-1">
                                                    {getProgressPercentage(project.current_amount, project.target_amount)}% funded
                                                </div>
                                            </div>

                                            {/* Stats */}
                                            <div className="flex items-center justify-between text-sm text-muted-foreground">
                                                <div className="flex items-center gap-1">
                                                    <Users className="h-4 w-4" />
                                                    {project.contributors_count} contributors
                                                </div>
                                                <div className="flex items-center gap-1">
                                                    <Clock className="h-4 w-4" />
                                                    {project.days_remaining} days left
                                                </div>
                                            </div>

                                            <Button className="w-full mt-4">
                                                View Project
                                            </Button>
                                        </CardContent>
                                    </Card>
                                ))}
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </>
    );
}