import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { ScrollArea } from '@/components/ui/scroll-area';
import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';
import { Bell, User } from 'lucide-react';
import { ReactNode, useState } from 'react';
import Sidebar from './Sidebar';

interface PageLayoutProps {
    children: ReactNode;
    title: string;
    currentPage?: string;
}

interface Notification {
    id: number;
    title: string;
    message: string;
    time: string;
    read: boolean;
    avatar?: string;
}

export default function PageLayout({ children, title, currentPage = '' }: PageLayoutProps) {
    const { auth } = usePage<SharedData>().props;
    const [notifications] = useState<Notification[]>([
        {
            id: 1,
            title: 'New Enrollment',
            message: 'A new student has enrolled in Grade 3',
            time: '2 hours ago',
            read: false,
        },
        {
            id: 2,
            title: 'Payment Received',
            message: 'Tuition payment received from John Doe',
            time: '5 hours ago',
            read: false,
        },
        {
            id: 3,
            title: 'System Update',
            message: 'System maintenance scheduled for tonight',
            time: '1 day ago',
            read: true,
        },
    ]);

    const unreadCount = notifications.filter((n) => !n.read).length;

    return (
        <div className="flex h-screen bg-background">
            {/* Sidebar */}
            <Sidebar currentPage={currentPage} />

            {/* Main Content */}
            <div className="flex flex-1 flex-col overflow-hidden">
                {/* Header */}
                <header className="border-b bg-background">
                    <div className="flex h-16 items-center justify-between px-6">
                        <h1 className="text-2xl font-bold tracking-tight">{title}</h1>

                        <div className="flex items-center gap-4">
                            {/* Notifications */}
                            <DropdownMenu>
                                <DropdownMenuTrigger asChild>
                                    <Button variant="ghost" size="icon" className="relative">
                                        <Bell className="h-5 w-5" />
                                        {unreadCount > 0 && (
                                            <Badge variant="destructive" className="absolute -top-1 -right-1 h-5 w-5 rounded-full p-0 text-xs">
                                                {unreadCount}
                                            </Badge>
                                        )}
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" className="w-80">
                                    <DropdownMenuLabel className="font-normal">
                                        <div className="flex flex-col space-y-1">
                                            <p className="text-sm font-medium">Notifications</p>
                                            <p className="text-xs text-muted-foreground">You have {unreadCount} unread messages</p>
                                        </div>
                                    </DropdownMenuLabel>
                                    <DropdownMenuSeparator />
                                    <ScrollArea className="h-72">
                                        {notifications.map((notification) => (
                                            <DropdownMenuItem key={notification.id} className="flex cursor-pointer flex-col items-start p-3">
                                                <div className="flex w-full items-start gap-2">
                                                    {!notification.read && <div className="mt-1.5 h-2 w-2 rounded-full bg-blue-500" />}
                                                    <div className="flex-1 space-y-1">
                                                        <p className="text-sm font-medium">{notification.title}</p>
                                                        <p className="text-xs text-muted-foreground">{notification.message}</p>
                                                        <p className="text-xs text-muted-foreground">{notification.time}</p>
                                                    </div>
                                                </div>
                                            </DropdownMenuItem>
                                        ))}
                                    </ScrollArea>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem className="w-full text-center">View all notifications</DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>

                            {/* User Menu */}
                            <div className="flex items-center gap-3">
                                <span className="text-sm text-muted-foreground">Welcome, {auth.user?.name || 'User'}!</span>
                                <DropdownMenu>
                                    <DropdownMenuTrigger asChild>
                                        <Button variant="ghost" size="icon">
                                            <Avatar className="h-8 w-8">
                                                <AvatarImage src="/api/placeholder/32/32" />
                                                <AvatarFallback>
                                                    <User className="h-4 w-4" />
                                                </AvatarFallback>
                                            </Avatar>
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuLabel>My Account</DropdownMenuLabel>
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem>Profile</DropdownMenuItem>
                                        <DropdownMenuItem>Settings</DropdownMenuItem>
                                        <DropdownMenuItem>Support</DropdownMenuItem>
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem className="text-destructive">Log out</DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                        </div>
                    </div>
                </header>

                {/* Page Content */}
                <main className="flex-1 overflow-y-auto bg-muted/10">
                    <div className="container mx-auto p-6">{children}</div>
                </main>
            </div>
        </div>
    );
}
