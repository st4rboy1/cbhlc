import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import {
    Banknote,
    Calendar,
    CalendarDays,
    DollarSign,
    FileCheck,
    FileText,
    GraduationCap,
    History,
    LayoutGrid,
    ReceiptText,
    School,
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
        href: '/admin/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Enrollments',
        href: '/admin/enrollments',
        icon: GraduationCap,
    },
    {
        title: 'Enrollment Periods',
        href: '/admin/enrollment-periods',
        icon: Calendar,
    },
    {
        title: 'School Years',
        href: '/admin/school-years',
        icon: CalendarDays,
    },
    {
        title: 'Students',
        href: '/admin/students',
        icon: Users,
    },
    {
        title: 'Guardians',
        href: '/admin/guardians',
        icon: ShieldCheck,
    },
    {
        title: 'Grade Level Fees',
        href: '/admin/grade-level-fees',
        icon: DollarSign,
    },
    {
        title: 'Users',
        href: '/admin/users',
        icon: UserCog,
    },
    {
        title: 'Documents',
        href: '/admin/documents',
        icon: FileText,
    },
    {
        title: 'Invoices',
        href: '/admin/invoices',
        icon: FileCheck,
    },
    {
        title: 'Payments',
        href: '/admin/payments',
        icon: Banknote,
    },
    {
        title: 'Receipts',
        href: '/admin/receipts',
        icon: ReceiptText,
    },
    {
        title: 'School Information',
        href: '/admin/school-information',
        icon: School,
    },
    {
        title: 'Audit Logs',
        href: '/admin/audit-logs',
        icon: History,
    },
    {
        title: 'System Settings',
        href: '/admin/settings',
        icon: Settings2,
    },
    {
        title: 'Profile Settings',
        href: '/settings/profile',
        icon: Settings,
    },
];

export function AdminSidebar() {
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
                <NavMain items={mainNavItems} label="Platform" />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
