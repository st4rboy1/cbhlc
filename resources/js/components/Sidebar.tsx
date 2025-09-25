import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { cn } from '@/lib/utils';
import { logout } from '@/routes';
import { type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { Book, ChevronDown, CreditCard, GraduationCap, Home, LogOut, Plus, UserCircle, Users } from 'lucide-react';
import { useState } from 'react';

interface SidebarProps {
    currentPage?: string;
}

export default function Sidebar({ currentPage = '' }: SidebarProps) {
    const { auth } = usePage<SharedData>().props;
    const [billingOpen, setBillingOpen] = useState(false);
    const [studentsOpen, setStudentsOpen] = useState(false);
    const [enrollmentsOpen, setEnrollmentsOpen] = useState(false);
    const isActive = (page: string) => currentPage === page;

    // Get the dashboard URL from the auth props (provided by backend)
    const getDashboardUrl = () => {
        // Use the dashboard_route provided by the backend
        return auth.user?.dashboard_route || '/';
    };

    return (
        <div className="flex w-64 flex-col border-r bg-card">
            {/* Profile Section */}
            <div className="border-b p-6">
                <div className="flex items-center gap-3">
                    <Avatar className="h-10 w-10">
                        <AvatarImage src="/api/placeholder/48/48" alt="Profile" />
                        <AvatarFallback className="bg-primary/10">
                            <UserCircle className="h-5 w-5 text-primary" />
                        </AvatarFallback>
                    </Avatar>
                    <div className="flex flex-col">
                        <span className="text-sm font-semibold">Welcome!</span>
                        <span className="text-xs text-muted-foreground">{auth.user?.name || 'User'}</span>
                    </div>
                </div>
            </div>

            {/* Navigation */}
            <nav className="flex-1 space-y-1 p-3">
                <Button
                    variant={isActive('dashboard') ? 'secondary' : 'ghost'}
                    className={cn('w-full justify-start gap-3', isActive('dashboard') && 'bg-accent')}
                    asChild
                >
                    <Link href={getDashboardUrl()}>
                        <Home className="h-4 w-4" />
                        Dashboard
                    </Link>
                </Button>

                {/* Students Section - Show only for guardians */}
                {auth.user?.roles?.some((role) => role.name === 'guardian') && (
                    <Collapsible open={studentsOpen} onOpenChange={setStudentsOpen}>
                        <CollapsibleTrigger asChild>
                            <Button
                                variant={currentPage?.includes('guardian.students') ? 'secondary' : 'ghost'}
                                className={cn('w-full justify-between gap-3', currentPage?.includes('guardian.students') && 'bg-accent')}
                            >
                                <Link href="/guardian/students" className="flex flex-1 items-center gap-3">
                                    <Users className="h-4 w-4" />
                                    Students
                                </Link>
                                <ChevronDown className={cn('h-4 w-4 transition-transform', studentsOpen && 'rotate-180')} />
                            </Button>
                        </CollapsibleTrigger>
                        <CollapsibleContent className="ml-7 space-y-1">
                            <Button
                                variant={isActive('guardian.students.create') ? 'secondary' : 'ghost'}
                                size="sm"
                                className={cn('w-full justify-start gap-2', isActive('guardian.students.create') && 'bg-accent')}
                                asChild
                            >
                                <Link href="/guardian/students/create">
                                    <Plus className="h-3 w-3" />
                                    Add Student
                                </Link>
                            </Button>
                        </CollapsibleContent>
                    </Collapsible>
                )}

                {/* Enrollments Section */}
                <Collapsible open={enrollmentsOpen} onOpenChange={setEnrollmentsOpen}>
                    <CollapsibleTrigger asChild>
                        <Button
                            variant={currentPage?.includes('enrollments') ? 'secondary' : 'ghost'}
                            className={cn('w-full justify-between gap-3', currentPage?.includes('enrollments') && 'bg-accent')}
                        >
                            <Link href="/enrollments" className="flex flex-1 items-center gap-3">
                                <GraduationCap className="h-4 w-4" />
                                Enrollments
                            </Link>
                            <ChevronDown className={cn('h-4 w-4 transition-transform', enrollmentsOpen && 'rotate-180')} />
                        </Button>
                    </CollapsibleTrigger>
                    <CollapsibleContent className="ml-7 space-y-1">
                        <Button
                            variant={isActive('enrollments.create') ? 'secondary' : 'ghost'}
                            size="sm"
                            className={cn('w-full justify-start gap-2', isActive('enrollments.create') && 'bg-accent')}
                            asChild
                        >
                            <Link href="/enrollments/create">
                                <Plus className="h-3 w-3" />
                                New Enrollment
                            </Link>
                        </Button>
                    </CollapsibleContent>
                </Collapsible>
                <Collapsible open={billingOpen} onOpenChange={setBillingOpen}>
                    <CollapsibleTrigger asChild>
                        <Button variant="ghost" className="w-full justify-between gap-3">
                            <div className="flex items-center gap-3">
                                <CreditCard className="h-4 w-4" />
                                Billing
                            </div>
                            <ChevronDown className={cn('h-4 w-4 transition-transform', billingOpen && 'rotate-180')} />
                        </Button>
                    </CollapsibleTrigger>
                    <CollapsibleContent className="ml-7 space-y-1">
                        <Button
                            variant={isActive('tuition') ? 'secondary' : 'ghost'}
                            size="sm"
                            className={cn('w-full justify-start', isActive('tuition') && 'bg-accent')}
                            asChild
                        >
                            <Link href="/tuition">Tuition</Link>
                        </Button>
                        <Button
                            variant={isActive('invoice') ? 'secondary' : 'ghost'}
                            size="sm"
                            className={cn('w-full justify-start', isActive('invoice') && 'bg-accent')}
                            asChild
                        >
                            <Link href="/invoices">Invoice</Link>
                        </Button>
                    </CollapsibleContent>
                </Collapsible>

                <Button
                    variant={isActive('registrar') ? 'secondary' : 'ghost'}
                    className={cn('w-full justify-start gap-3', isActive('registrar') && 'bg-accent')}
                    asChild
                >
                    <Link href="/registrar">
                        <Book className="h-4 w-4" />
                        Registrar
                    </Link>
                </Button>

                <Button
                    variant={isActive('profile.edit') ? 'secondary' : 'ghost'}
                    className={cn('w-full justify-start gap-3', isActive('profile.edit') && 'bg-accent')}
                    asChild
                >
                    <Link href="/settings/profile">
                        <UserCircle className="h-4 w-4" />
                        Profile Settings
                    </Link>
                </Button>
            </nav>

            {/* Logout */}
            <div className="border-t p-3">
                <Button
                    variant="ghost"
                    className="w-full justify-start gap-3 text-destructive hover:bg-destructive/10 hover:text-destructive"
                    asChild
                >
                    <Link href={logout()} method="post" as="button">
                        <LogOut className="h-4 w-4" />
                        Logout
                    </Link>
                </Button>
            </div>
        </div>
    );
}
