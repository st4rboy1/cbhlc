import { Icon } from '@/components/icon';
import { LoginDialog } from '@/components/login-dialog';
import { Button } from '@/components/ui/button';
import { type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { GraduationCap } from 'lucide-react';

interface PublicNavProps {
    currentPage?: 'home' | 'about';
}

export function PublicNav({ currentPage }: PublicNavProps) {
    const { auth } = usePage<SharedData>().props;

    // Helper function to get dashboard URL based on user role
    const getDashboardUrl = () => {
        if (!auth.user) return '/login';
        // The server will redirect to the correct dashboard after login
        return '/admin/dashboard';
    };

    return (
        <header className="fixed top-0 left-0 z-50 w-full border-b bg-white/80 backdrop-blur-md">
            <div className="container mx-auto flex h-16 items-center justify-between px-6">
                <Link href="/" className="flex items-center space-x-2 text-xl font-bold text-slate-800 transition-colors hover:text-blue-600">
                    <Icon iconNode={GraduationCap} className="h-6 w-6" />
                    <span>CBHLC</span>
                </Link>
                <nav className="flex items-center space-x-8">
                    <Link
                        href="/"
                        className={`font-medium transition-colors ${
                            currentPage === 'home' ? 'text-blue-600' : 'text-slate-600 hover:text-slate-800'
                        }`}
                    >
                        Home
                    </Link>
                    <Link
                        href="/about"
                        className={`font-medium transition-colors ${
                            currentPage === 'about' ? 'text-blue-600' : 'text-slate-600 hover:text-slate-800'
                        }`}
                    >
                        About
                    </Link>
                    {auth.user ? (
                        <Button asChild variant="default">
                            <Link href={getDashboardUrl()}>Dashboard</Link>
                        </Button>
                    ) : (
                        <LoginDialog trigger={<Button variant="outline">Login</Button>} />
                    )}
                </nav>
            </div>
        </header>
    );
}
