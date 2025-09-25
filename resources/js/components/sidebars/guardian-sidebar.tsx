import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/react';
import { BookOpen, FileText, GraduationCap, HelpCircle, Home, LayoutGrid, Receipt, Users } from 'lucide-react';
import AppLogo from '../app-logo';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/guardian/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'My Children',
        href: '/students',
        icon: Users,
    },
    {
        title: 'Enrollments',
        href: '/enrollments',
        icon: GraduationCap,
    },
    {
        title: 'Tuition',
        href: '/tuition',
        icon: Receipt,
    },
    {
        title: 'Student Reports',
        href: '/studentreport',
        icon: FileText,
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
