import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import {
    Banknote,
    CalendarDays,
    CreditCard,
    DollarSign,
    FileCheck,
    FileClock,
    FilePieChart,
    GraduationCap,
    History,
    LayoutGrid,
    School,
    Settings,
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
        title: 'Students',
        href: '/admin/students',
        icon: Users,
    },
    {
        title: 'Users',
        href: '/admin/users',
        icon: UserCog,
    },
    {
        title: 'Invoices',
        href: '/invoices',
        icon: FileCheck,
    },
    {
        title: 'Payments',
        href: '/admin/payments',
        icon: Banknote,
    },
    {
        title: 'Grade Level Fees',
        href: '/admin/grade-level-fees',
        icon: DollarSign,
    },
    {
        title: 'Enrollment Periods',
        href: '/admin/enrollment-periods',
        icon: CalendarDays,
    },
    {
        title: 'Pending Documents',
        href: '/admin/documents/pending',
        icon: FileClock,
    },
    {
        title: 'Reports',
        href: '/admin/reports',
        icon: FilePieChart,
    },
    {
        title: 'Audit Logs',
        href: '/admin/audit-logs',
        icon: History,
    },
    {
        title: 'School Information',
        href: '/admin/school-information',
        icon: School,
    },
    {
        title: 'Tuition Fees',
        href: '/tuition',
        icon: CreditCard,
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
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
