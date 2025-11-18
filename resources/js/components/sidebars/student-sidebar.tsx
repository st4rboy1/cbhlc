import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { logout } from '@/routes';
import { type NavItem, type SharedData } from '@/types';
import { Link, router, usePage } from '@inertiajs/react';
import { ClipboardList, CreditCard, FileCheck, LayoutGrid, LogOut, Settings } from 'lucide-react';
import AppLogo from '../app-logo';

export function StudentSidebar() {
    const { auth } = usePage<SharedData>().props;
    const studentId = auth.user?.student_id;

    const handleLogout = () => {
        router.flushAll();
    };

    const mainNavItems: NavItem[] = [
        {
            title: 'Dashboard',
            href: '/student/dashboard',
            icon: LayoutGrid,
        },
        {
            title: 'My Invoices',
            href: '/invoices',
            icon: FileCheck,
        },
        {
            title: 'Tuition Fees',
            href: '/tuition',
            icon: CreditCard,
        },
        ...(studentId
            ? [
                  {
                      title: 'My Report',
                      href: `/students/${studentId}/report`,
                      icon: ClipboardList,
                  },
              ]
            : []),
        {
            title: 'Profile Settings',
            href: '/settings/profile',
            icon: Settings,
        },
    ];

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
