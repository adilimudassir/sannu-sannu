import { usePage } from '@inertiajs/react';
import { type SharedData, type BreadcrumbItem } from '@/types';
import { getBreadcrumbs } from '@/config/navigation';
import {
    Breadcrumb,
    BreadcrumbItem as BreadcrumbItemComponent,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/components/ui/breadcrumb';

interface RoleBasedBreadcrumbsProps {
    customBreadcrumbs?: BreadcrumbItem[];
    className?: string;
}

export default function RoleBasedBreadcrumbs({ 
    customBreadcrumbs, 
    className 
}: RoleBasedBreadcrumbsProps) {
    const { auth, ziggy, tenant } = usePage<SharedData>().props;
    const { user } = auth;
    
    // Use custom breadcrumbs if provided, otherwise generate from route
    const breadcrumbs = customBreadcrumbs || getBreadcrumbs(
        ziggy.location, 
        user, 
        tenant?.slug
    );
    
    if (breadcrumbs.length <= 1) {
        return null;
    }
    
    return (
        <Breadcrumb className={className}>
            <BreadcrumbList>
                {breadcrumbs.map((breadcrumb, index) => (
                    <div key={breadcrumb.href} className="flex items-center">
                        <BreadcrumbItemComponent>
                            {index === breadcrumbs.length - 1 ? (
                                <BreadcrumbPage>{breadcrumb.title}</BreadcrumbPage>
                            ) : (
                                <BreadcrumbLink href={breadcrumb.href}>
                                    {breadcrumb.title}
                                </BreadcrumbLink>
                            )}
                        </BreadcrumbItemComponent>
                        {index < breadcrumbs.length - 1 && <BreadcrumbSeparator />}
                    </div>
                ))}
            </BreadcrumbList>
        </Breadcrumb>
    );
}