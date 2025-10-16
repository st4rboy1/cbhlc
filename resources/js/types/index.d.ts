import { InertiaLinkProps } from '@inertiajs/react';
import { LucideIcon } from 'lucide-react';

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
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon | null;
    isActive?: boolean;
}

export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;
    [key: string]: unknown;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    dashboard_route?: string;
    roles?: Array<{ id: number; name: string }>;
    role?: string; // Added for the single role
    student_id?: number; // Student profile ID if user is a student
    created_at: string;
    updated_at: string;
}

export interface Student {
    id: number;
    user_id: number;
    full_name: string;
    first_name: string;
    last_name: string;
    middle_name?: string;
    grade_level: {
        label: string;
        value: string;
    };
    email: string;
    birthdate: string;
    gender: string;
    address: string;
    contact_number: string;
    student_id: string;
    section?: string;
    guardian_id?: number;
}

export interface Enrollment {
    id: number;
    student: Student;
    grade_level: string;
    status: 'pending' | 'approved' | 'rejected' | 'enrolled' | 'completed';
    school_year: string;
    created_at: string;
}

export interface Paginated<T> {
    current_page: number;
    data: T[];
    first_page_url: string;
    from: number;
    last_page: number;
    last_page_url: string;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number;
    total: number;
}
