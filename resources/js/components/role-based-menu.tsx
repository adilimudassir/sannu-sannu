import { usePage } from '@inertiajs/react';
import { type SharedData, type NavItem } from '@/types';
import { getNavigationConfig } from '@/config/navigation';
import { NavMain } from '@/components/nav-main';

interface RoleBasedMenuProps {
    className?: string;
    variant?: 'sidebar' | 'dropdown' | 'horizontal';
    section?: 'main' | 'settings' | 'quick';
}

export default function RoleBasedMenu({ 
    className, 
    variant = 'sidebar', 
    section = 'main' 
}: RoleBasedMenuProps) {
    const { auth, ziggy, tenant } = usePage<SharedData>().props;
    const { user } = auth;
    
    // Extract tenant slug from current URL if available
    const currentUrl = ziggy.location;
    const tenantSlugMatch = currentUrl.match(/\/tenant\/([^\/]+)/);
    const tenantSlug = tenantSlugMatch ? tenantSlugMatch[1] : tenant?.slug;
    
    const navigationConfig = getNavigationConfig(user, tenantSlug);
    const items = navigationConfig[section] || [];
    
    if (items.length === 0) {
        return null;
    }
    
    switch (variant) {
        case 'sidebar':
            return <NavMain items={items} className={className} />;
        
        case 'dropdown':
            return (
                <div className={className}>
                    {items.map((item) => (
                        <a
                            key={item.href}
                            href={item.href}
                            className="flex items-center gap-2 px-3 py-2 text-sm hover:bg-accent hover:text-accent-foreground rounded-md"
                        >
                            {item.icon && <item.icon className="h-4 w-4" />}
                            {item.title}
                            {item.badge && (
                                <span className="ml-auto text-xs bg-primary text-primary-foreground px-1.5 py-0.5 rounded-full">
                                    {item.badge}
                                </span>
                            )}
                        </a>
                    ))}
                </div>
            );
        
        case 'horizontal':
            return (
                <nav className={`flex items-center space-x-4 ${className}`}>
                    {items.map((item) => (
                        <a
                            key={item.href}
                            href={item.href}
                            className="flex items-center gap-2 px-3 py-2 text-sm font-medium hover:text-primary transition-colors"
                        >
                            {item.icon && <item.icon className="h-4 w-4" />}
                            {item.title}
                            {item.badge && (
                                <span className="ml-1 text-xs bg-primary text-primary-foreground px-1.5 py-0.5 rounded-full">
                                    {item.badge}
                                </span>
                            )}
                        </a>
                    ))}
                </nav>
            );
        
        default:
            return <NavMain items={items} className={className} />;
    }
}