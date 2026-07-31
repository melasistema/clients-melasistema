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

// Currency + locale for the Intl money formatter, shared from config/money.php
// so the whole frontend formats money one way (see composables/useFormatters).
export interface MoneyConfig {
    currency: string;
    locale: string;
}

// The one running task (if any), shared on every request by HandleInertiaRequests
// so the persistent LiveTimer renders in the app chrome on every page — not just
// the dashboard. Null when nothing is running. This global shape is what a future
// NativePHP menu-bar timer reads too.
export interface ActiveTimer {
    client_id: number;
    project_id: number;
    task_id: number;
    task_description: string;
    project_name: string;
    client_name: string;
    timer_started_at: string;
}

// The last stopped task (from the `last_timer` cookie), shared so the timer bar
// keeps showing what you were working on — clickable + resumable — after a stop,
// until dismissed. Shown only when no timer is running. `total_seconds` is the
// task's banked cumulative time (static, not ticking).
export interface LastTimer {
    client_id: number;
    project_id: number;
    task_id: number;
    task_description: string;
    project_name: string;
    total_seconds: number;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    registrationEnabled: boolean;
    money: MoneyConfig;
    // The running timer (or null), shared on every page — see LiveTimer.vue.
    activeTimer: ActiveTimer | null;
    // The last stopped task (or null) — the persistent, dismissible timer bar.
    lastTimer: LastTimer | null;
    // Active UI language + its lang/{locale}/*.php messages (see useTranslations).
    locale: string;
    translations: Record<string, unknown>;
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

// A project's billing mode is derived on the backend from agreed_fee + hourly_rate.
export type BillingMode = 'fixed' | 'hourly' | 'non_billable';

export interface Payment {
    id: number;
    project_id: number;
    // Serialized from a decimal:2 cast — arrives as a string ("1500.00").
    amount: string;
    paid_at: string;
    note: string | null;
    created_at?: string;
    updated_at?: string;
}

export interface Project {
    id: number;
    client_id: number;
    name: string;
    description: string | null;
    // Serialized from a decimal:2 cast, so it arrives as a string ("85.00").
    // The number <input> and Intl formatter both coerce it fine.
    hourly_rate: string;
    // The fixed quote, or null for hourly / non-billable projects (string when set).
    agreed_fee: string | null;
    completed_at: string | null;
    // Derived accessors (see app/Models/Project.php) — read, never recomputed here.
    billing_mode: BillingMode;
    total_earnings: number; // what is owed: fee, or time x rate, or 0
    total_tracked_seconds: number;
    amount_paid: number;
    outstanding: number;
    is_completed: boolean;
    is_fully_paid: boolean;
    tasks?: Task[];
    payments?: Payment[];
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
    completed_at: string | null;
    this_task_total_entry: number;
    is_completed: boolean;
    created_at?: string;
    updated_at?: string;
}

export type BreadcrumbItemType = BreadcrumbItem;
