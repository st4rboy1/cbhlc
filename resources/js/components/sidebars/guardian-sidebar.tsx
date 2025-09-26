import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/react';
import { BookOpen, ClipboardList, CreditCard, FileCheck, GraduationCap, HelpCircle, Home, LayoutGrid, Settings, Users } from 'lucide-react';
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
    {
        title: 'Billing',
        href: '/guardian/billing',
        icon: FileCheck,
    },
    {
        title: 'Invoices',
        href: '/invoices',
        icon: FileCheck,
    },
    {
        title: 'Tuition Fees',
        href: '/tuition',
        icon: CreditCard,
    },
    {
        title: 'Student Reports',
        href: '/guardian/students',
        icon: ClipboardList,
    },
    {
        title: 'Profile Settings',
        href: '/settings/profile',
        icon: Settings,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'School Website',
        href: 'https://cbhlc.edu.ph',
        icon: Home,
    },
    {
        title: 'Parent Guide',
        href: '/parent-guide',
        icon: BookOpen,
    },
    {
        title: 'Support',
        href: '/support',
        icon: HelpCircle,
    },
];

export function GuardianSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href="/guardian/dashboard" prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
