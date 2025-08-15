import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Edit, ArrowLeft } from 'lucide-react';

export default function AdminProjectEdit() {
    return (
        <AppLayout>
            <Head title="Edit Project - Admin" />
            
            <div className="space-y-6">
                <div className="flex items-center gap-4">
                    <Button variant="outline" size="sm" onClick={() => window.history.back()}>
                        <ArrowLeft className="h-4 w-4 mr-2" />
                        Back
                    </Button>
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Edit Project</h1>
                        <p className="text-muted-foreground">Modify project settings and configuration</p>
                    </div>
                </div>

                <Card className="max-w-2xl mx-auto">
                    <CardHeader className="text-center">
                        <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-muted">
                            <Edit className="h-8 w-8 text-muted-foreground" />
                        </div>
                        <CardTitle className="text-2xl">Feature Under Construction</CardTitle>
                        <CardDescription className="text-base">
                            The Project editing feature is currently being developed.
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="text-center space-y-4">
                        <p className="text-muted-foreground">
                            This page will allow system administrators to:
                        </p>
                        <ul className="text-left space-y-2 max-w-md mx-auto">
                            <li className="flex items-center gap-2">
                                <div className="h-1.5 w-1.5 rounded-full bg-primary" />
                                Edit project details and settings
                            </li>
                            <li className="flex items-center gap-2">
                                <div className="h-1.5 w-1.5 rounded-full bg-primary" />
                                Update project goals and timelines
                            </li>
                            <li className="flex items-center gap-2">
                                <div className="h-1.5 w-1.5 rounded-full bg-primary" />
                                Modify project visibility and access
                            </li>
                            <li className="flex items-center gap-2">
                                <div className="h-1.5 w-1.5 rounded-full bg-primary" />
                                Transfer projects between organizations
                            </li>
                        </ul>
                        <div className="pt-4">
                            <Button variant="outline" onClick={() => window.history.back()}>
                                Return to Projects
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}