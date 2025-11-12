import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { logout } from '@/routes';
import { type NavItem } from '@/types';
import { Link, router } from '@inertiajs/react';
import {
    BadgeDollarSign,
    Calendar,
    CalendarDays,
    FileCheck,
    GraduationCap,
    LayoutGrid,
    LogOut,
    Receipt,
    ReceiptText,
    Settings,
    ShieldCheck,
    Users,
} from 'lucide-react';
import AppLogo from '../app-logo';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/registrar/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Enrollments',
        href: '/registrar/enrollments',
        icon: GraduationCap,
    },
    {
        title: 'Students',
        href: '/registrar/students',
        icon: Users,
    },
    {
        title: 'Guardians',
        href: '/registrar/guardians',
        icon: ShieldCheck,
    },
    {
        title: 'Enrollment Periods',
        href: '/registrar/enrollment-periods',
        icon: Calendar,
    },
    {
        title: 'School Years',
        href: '/registrar/school-years',
        icon: CalendarDays,
    },
    {
        title: 'Grade Level Fees',
        href: '/registrar/grade-level-fees',
        icon: BadgeDollarSign,
    },
    {
        title: 'Documents',
        href: '/registrar/documents',
        icon: FileCheck,
    },
    {
        title: 'Invoices',
        href: '/registrar/invoices',
        icon: FileCheck,
    },
    {
        title: 'Payments',
        href: '/registrar/payments',
        icon: Receipt,
    },
    {
        title: 'Receipts',
        href: '/registrar/receipts',
        icon: ReceiptText,
    },
    {
        title: 'Profile Settings',
        href: '/settings/profile',
        icon: Settings,
    },
];

export function RegistrarSidebar() {
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
