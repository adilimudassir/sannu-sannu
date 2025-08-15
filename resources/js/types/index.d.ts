import { LucideIcon } from 'lucide-react';
import type { Config } from 'ziggy-js';

import { Role } from '@/enums';

export interface Auth {
    user: User;
    roles: Role[];
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon | null;
    isActive?: boolean;
}

export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
    [key: string]: unknown;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    is_system_admin?: boolean;
    is_tenant_admin?: boolean;
    is_contributor?: boolean;
    role?: string;
    [key: string]: unknown; // This allows for additional properties...
}
