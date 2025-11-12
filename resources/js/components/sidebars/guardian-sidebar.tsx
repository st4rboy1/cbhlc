import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { logout } from '@/routes';
import { type NavItem } from '@/types';
import { Link, router } from '@inertiajs/react';
import { CreditCard, FileCheck, FileText, GraduationCap, LayoutGrid, LogOut, Settings, Users } from 'lucide-react';
import AppLogo from '../app-logo';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/guardian/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'My Children',
        href: '/guardian/students',
        icon: Users,
    },
    {
        title: 'Enrollments',
        href: '/guardian/enrollments',
        icon: GraduationCap,
    },
];

const billingNavItems: NavItem[] = [
    {
        title: 'Enrollment Billing',
        href: '/guardian/billing',
        icon: FileCheck,
    },
    {
        title: 'Invoices',
        href: '/guardian/invoices',
        icon: FileText,
    },
    {
        title: 'Payments',
        href: '/guardian/payments',
        icon: FileText,
    },
    {
        title: 'Receipts',
        href: '/guardian/receipts',
        icon: FileText,
    },
];

const secondaryNavItems: NavItem[] = [
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

export function GuardianSidebar() {
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
                <NavMain items={billingNavItems} label="Billing" />
                <NavMain items={secondaryNavItems} label="Other" />
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
