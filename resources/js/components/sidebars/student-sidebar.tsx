import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/react';
import { Award, BookOpen, Calendar, FileText, HelpCircle, Home, LayoutGrid, Receipt } from 'lucide-react';
import AppLogo from '../app-logo';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/student/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'My Enrollment',
        href: '/enrollment',
        icon: FileText,
    },
    {
        title: 'Tuition',
        href: '/tuition',
        icon: Receipt,
    },
    {
        title: 'My Report',
        href: '/studentreport',
        icon: Award,
    },
    {
        title: 'School Calendar',
        href: '/calendar',
        icon: Calendar,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'School Website',
        href: 'https://cbhlc.edu.ph',
        icon: Home,
    },
    {
        title: 'Student Resources',
        href: '/resources',
        icon: BookOpen,
    },
    {
        title: 'Help',
        href: '/help',
        icon: HelpCircle,
    },
];

export function StudentSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href="/student/dashboard" prefetch>
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
