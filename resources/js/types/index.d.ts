import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

// Domain models (User -> Client -> Project -> Task). `total_earnings` and
// `this_task_total_entry` are appended Eloquent accessors computed on the
// backend (see app/Models/*), not columns — the frontend only reads them.
export interface Client {
    id: number;
    company_name: string;
    contact_name: string;
    contact_email: string;
    contact_phone: string;
    address: string;
    vat_number: string;
    unique_code: string;
    total_earnings: number;
    created_at?: string;
    updated_at?: string;
}

export interface Project {
    id: number;
    client_id: number;
    name: string;
    description: string | null;
    hourly_rate: number;
    paid_at: string | null;
    total_earnings: number;
    created_at?: string;
    updated_at?: string;
}

export interface Task {
    id: number;
    project_id: number;
    description: string;
    total_seconds: number;
    is_running: boolean;
    timer_started_at: string | null;
    this_task_total_entry: number;
    created_at?: string;
    updated_at?: string;
}

export type BreadcrumbItemType = BreadcrumbItem;
