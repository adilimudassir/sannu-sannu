import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import { Building2, ChevronRight } from 'lucide-react';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

interface Tenant {
    id: number;
    slug: string;
    name: string;
    logo_url?: string;
    role: string;
}

interface SelectTenantProps {
    tenants: Tenant[];
}

export default function SelectTenant({ tenants }: SelectTenantProps) {
    const { post, processing } = useForm();

    const selectTenant = (tenantId: number) => {
        post(route('tenant.select.store'), {
            data: { tenant_id: tenantId },
        });
    };

    const getRoleBadgeVariant = (role: string) => {
        switch (role) {
            case 'tenant_admin':
                return 'default';
            case 'project_manager':
                return 'secondary';
            default:
                return 'outline';
        }
    };

    const getRoleLabel = (role: string) => {
        switch (role) {
            case 'tenant_admin':
                return 'Tenant Admin';
            case 'project_manager':
                return 'Project Manager';
            default:
                return role;
        }
    };

    return (
        <>
            <Head title="Select Organization" />
            
            <div className="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10">
                <div className="w-full max-w-2xl">
                    <div className="text-center mb-8">
                        <h1 className="text-2xl font-semibold tracking-tight">Select Organization</h1>
                        <p className="text-muted-foreground mt-2">
                            Choose which organization you'd like to manage
                        </p>
                    </div>

                    <div className="grid gap-4">
                        {tenants.map((tenant) => (
                            <Card 
                                key={tenant.id} 
                                className="cursor-pointer transition-all hover:shadow-md hover:border-primary/50"
                                onClick={() => selectTenant(tenant.id)}
                            >
                                <CardContent className="p-6">
                                    <div className="flex items-center justify-between">
                                        <div className="flex items-center gap-4">
                                            <div className="flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10">
                                                {tenant.logo_url ? (
                                                    <img 
                                                        src={tenant.logo_url} 
                                                        alt={tenant.name}
                                                        className="h-8 w-8 rounded object-cover"
                                                    />
                                                ) : (
                                                    <Building2 className="h-6 w-6 text-primary" />
                                                )}
                                            </div>
                                            <div>
                                                <h3 className="font-semibold">{tenant.name}</h3>
                                                <p className="text-sm text-muted-foreground">@{tenant.slug}</p>
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-3">
                                            <Badge variant={getRoleBadgeVariant(tenant.role)}>
                                                {getRoleLabel(tenant.role)}
                                            </Badge>
                                            <ChevronRight className="h-4 w-4 text-muted-foreground" />
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>

                    {tenants.length === 0 && (
                        <Card>
                            <CardContent className="p-8 text-center">
                                <Building2 className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                                <h3 className="font-semibold mb-2">No Organizations Found</h3>
                                <p className="text-muted-foreground mb-4">
                                    You don't have administrative access to any organizations yet.
                                </p>
                                <Button 
                                    variant="outline" 
                                    onClick={() => window.location.href = route('global.dashboard')}
                                >
                                    Go to Dashboard
                                </Button>
                            </CardContent>
                        </Card>
                    )}
                </div>
            </div>
        </>
    );
}