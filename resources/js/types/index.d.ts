import { LucideIcon } from 'lucide-react';
import type { Config } from 'ziggy-js';

import { Role, UserRole } from '@/enums';

export interface Auth {
    user: User;
    roles?: Role[];
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
    badge?: string | number;
    children?: NavItem[];
}

export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
    tenant?: {
        id: number;
        name: string;
        slug: string;
        role?: string;
        context_set_at?: string;
    };
    availableTenants?: Array<Tenant & { role: string }>;
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
    primary_role?: UserRole;
    tenant_roles?: Array<{
        tenant_id: number;
        role: Role;
        is_active: boolean;
    }>;
    permissions?: string[];
    [key: string]: unknown; // This allows for additional properties...
}

export interface Tenant {
    id: number;
    name: string;
    slug: string;
    description?: string;
    logo?: string;
    created_at: string;
    updated_at: string;
}

export interface Project {
    id: number;
    name: string;
    slug: string;
    description?: string;
    status: 'draft' | 'active' | 'paused' | 'completed' | 'cancelled';
    visibility: 'public' | 'private' | 'invite_only';
    requires_approval?: boolean;
    max_contributors?: number;
    total_amount: number;
    minimum_contribution?: number;
    payment_options?: string[];
    installment_frequency?: 'monthly' | 'quarterly' | 'custom';
    custom_installment_months?: number;
    start_date: string;
    end_date: string;
    registration_deadline?: string;
    created_by?: number;
    managed_by?: number[];
    settings?: Record<string, any>;
    created_at: string;
    updated_at: string;
    tenant?: Tenant;
    creator?: User;
    products?: Product[];
    statistics?: {
        total_contributors: number;
        total_raised: number;
        completion_percentage: number;
        days_remaining: number;
        average_contribution: number;
    };
}

export interface Product {
    id?: number;
    tenant_id?: number;
    project_id?: number;
    name: string;
    description?: string;
    price: number;
    image_url?: string;
    image?: File;
    sort_order: number;
    created_at?: string;
    updated_at?: string;
    tenant?: Tenant;
    project?: Project;
}

export interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: Array<{
        url?: string;
        label: string;
        active: boolean;
    }>;
}

export interface ProjectFilters {
    search?: string;
    min_amount?: string;
    max_amount?: string;
    sort_by?: string;
    sort_direction?: string;
}

export interface ProjectStatistics {
    total_contributors: number;
    total_raised: number;
    completion_percentage: number;
    days_remaining: number;
    average_contribution: number;
}
