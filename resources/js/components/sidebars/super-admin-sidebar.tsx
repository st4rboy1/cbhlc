import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import {
    BadgeDollarSign,
    Building2,
    Calendar,
    CalendarDays,
    ClipboardList,
    FileCheck,
    FileText,
    GraduationCap,
    LayoutGrid,
    Receipt,
    Settings,
    Settings2,
    ShieldCheck,
    UserCog,
    Users,
} from 'lucide-react';
import AppLogo from '../app-logo';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/super-admin/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Enrollments',
        href: '/super-admin/enrollments',
        icon: GraduationCap,
    },
    {
        title: 'Enrollment Periods',
        href: '/super-admin/enrollment-periods',
        icon: Calendar,
    },
    {
        title: 'School Years',
        href: '/super-admin/school-years',
        icon: CalendarDays,
    },
    {
        title: 'Students',
        href: '/super-admin/students',
        icon: Users,
    },
    {
        title: 'Guardians',
        href: '/super-admin/guardians',
        icon: ShieldCheck,
    },
    {
        title: 'Grade Level Fees',
        href: '/super-admin/grade-level-fees',
        icon: BadgeDollarSign,
    },
    {
        title: 'Users',
        href: '/super-admin/users',
        icon: UserCog,
    },
    {
        title: 'Documents',
        href: '/super-admin/documents',
        icon: FileText,
    },
    {
        title: 'Invoices',
        href: '/super-admin/invoices',
        icon: FileCheck,
    },
    {
        title: 'Payments',
        href: '/super-admin/payments',
        icon: Receipt,
    },
    {
        title: 'School Information',
        href: '/super-admin/school-information',
        icon: Building2,
    },
    {
        title: 'Audit Logs',
        href: '/super-admin/audit-logs',
        icon: ClipboardList,
    },
    {
        title: 'System Settings',
        href: '/super-admin/settings',
        icon: Settings2,
    },
    {
        title: 'Profile Settings',
        href: '/settings/profile',
        icon: Settings,
    },
];

export function SuperAdminSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg">
                            <AppLogo />
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
