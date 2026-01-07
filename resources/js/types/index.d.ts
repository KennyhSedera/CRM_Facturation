import { LucideIcon } from 'lucide-react';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
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
    roles?: string[];
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
    email_verified_at?: string;
    user_role: string;
    company_id?: number;
    company?: Company;
    avatar?: string;
    [key: string]: unknown;
}

export interface Company {
    company_id: number;
    company_name: string;
    company_email: string;
    company_logo?: string;
    company_logo_url?: string;
    company_phone?: string;
    company_website?: string;
    company_address?: string;
    company_city?: string;
    company_postal_code?: string;
    company_country?: string;
    company_registration_number?: string;
    company_tax_number?: string;
    company_description?: string;
    company_currency?: string;
    company_timezone?: string;
    plan_status: 'free' | 'premium' | 'entreprise' | 'basic';
    plan_start_date?: string;
    plan_end_date?: string;
    is_active: boolean;
    created_at?: string;
    updated_at?: string;
    users?: User;
    client_count?:number;
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: {
        user: User;
    };
    name: string;
    quote: {
        message: string;
        author: string;
    };
    ziggy: {
        location: string;
        query?: Record<string, string>;
        [key: string]: any;
    };
    sidebarOpen: boolean;
    flash?: {
        success?: string;
        error?: string;
        warning?: string;
        info?: string;
    };
};

export interface Plan {
    title: string;
    price: string;
    period?: string;
    description: string;
    features: string[];
    buttonText: string;
    buttonVariant?: 'default' | 'primary';
    badge?: string;
    popular?: boolean;
    onButtonClick?: () => void;
}

export interface Feature {
    icon: LucideIcon;
    title: string;
    description: string;
    iconColor: IconColor;
}
