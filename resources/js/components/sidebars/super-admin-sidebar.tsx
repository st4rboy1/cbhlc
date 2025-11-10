import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { logout } from '@/routes';
import { type NavItem } from '@/types';
import { Link, router } from '@inertiajs/react';
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
    LogOut,
    Receipt,
    ReceiptText,
    Settings,
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
        title: 'Receipts',
        href: '/super-admin/receipts',
        icon: ReceiptText,
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
        title: 'Profile Settings',
        href: '/settings/profile',
        icon: Settings,
    },
];

export function SuperAdminSidebar() {
    const handleLogout = () => {
        router.flushAll();
    };

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
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton asChild tooltip="Log out">
                            <Link href={logout()} method="post" as="button" onClick={handleLogout}>
                                <LogOut />
                                <span>Log out</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
