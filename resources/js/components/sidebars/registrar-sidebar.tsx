import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/react';
import { BookOpen, Calendar, FileText, Folder, GraduationCap, LayoutGrid, Receipt, Users } from 'lucide-react';
import AppLogo from '../app-logo';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/registrar/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Enrollments',
        href: '/enrollments',
        icon: GraduationCap,
    },
    {
        title: 'Students',
        href: '/students',
        icon: Users,
    },
    {
        title: 'Reports',
        href: '/reports',
        icon: FileText,
    },
    {
        title: 'Tuition',
        href: '/tuition',
        icon: Receipt,
    },
    {
        title: 'School Events',
        href: '/registrar',
        icon: Calendar,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'School Website',
        href: 'https://cbhlc.edu.ph',
        icon: Folder,
    },
    {
        title: 'Help',
        href: '/help',
        icon: BookOpen,
    },
];

export function RegistrarSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href="/registrar/dashboard" prefetch>
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
