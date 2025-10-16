import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { CreditCard, DollarSign, FileCheck, FileClock, GraduationCap, LayoutGrid, Settings, Users } from 'lucide-react';
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
        title: 'Grade Level Fees',
        href: '/registrar/grade-level-fees',
        icon: DollarSign,
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
        title: 'Pending Documents',
        href: '/registrar/documents/pending',
        icon: FileClock,
    },
    {
        title: 'Profile Settings',
        href: '/settings/profile',
        icon: Settings,
    },
];

export function RegistrarSidebar() {
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
