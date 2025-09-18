import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { cn } from '@/lib/utils';
import { type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { ChevronDown, CreditCard, FileText, GraduationCap, Home, LogOut, UserCircle, Users } from 'lucide-react';
import { useState } from 'react';

interface SidebarProps {
    currentPage?: string;
}

export default function Sidebar({ currentPage = '' }: SidebarProps) {
    const { auth } = usePage<SharedData>().props;
    const [billingOpen, setBillingOpen] = useState(false);
    const isActive = (page: string) => currentPage === page;

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
                    <Link href="/dashboard">
                        <Home className="h-4 w-4" />
                        Dashboard
                    </Link>
                </Button>

                <Button
                    variant={isActive('enrollment') ? 'secondary' : 'ghost'}
                    className={cn('w-full justify-start gap-3', isActive('enrollment') && 'bg-accent')}
                    asChild
                >
                    <Link href="/enrollment">
                        <Users className="h-4 w-4" />
                        Enrollment
                    </Link>
                </Button>
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
                            <Link href="/invoice">Invoice</Link>
                        </Button>
                    </CollapsibleContent>
                </Collapsible>
                <Button
                    variant={isActive('studentreport') ? 'secondary' : 'ghost'}
                    className={cn('w-full justify-start gap-3', isActive('studentreport') && 'bg-accent')}
                    asChild
                >
                    <Link href="/studentreport">
                        <FileText className="h-4 w-4" />
                        Student Report
                    </Link>
                </Button>

                <Button
                    variant={isActive('registrar') ? 'secondary' : 'ghost'}
                    className={cn('w-full justify-start gap-3', isActive('registrar') && 'bg-accent')}
                    asChild
                >
                    <Link href="/registrar">
                        <GraduationCap className="h-4 w-4" />
                        Registrar
                    </Link>
                </Button>

                <Button
                    variant={isActive('profilesettings') ? 'secondary' : 'ghost'}
                    className={cn('w-full justify-start gap-3', isActive('profilesettings') && 'bg-accent')}
                    asChild
                >
                    <Link href="/profilesettings">
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
                    <Link href="/logout" method="post">
                        <LogOut className="h-4 w-4" />
                        Logout
                    </Link>
                </Button>
            </div>
        </div>
    );
}
