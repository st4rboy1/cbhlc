import { type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { Calendar, ChevronDown, CreditCard, FileText, LogOut, Users } from 'lucide-react';
import { useState } from 'react';

interface SidebarProps {
    currentPage?: string;
}

export default function Sidebar({ currentPage = '' }: SidebarProps) {
    const { auth } = usePage<SharedData>().props;
    const [billingOpen, setBillingOpen] = useState(false);
    const isActive = (page: string) => currentPage === page;

    return (
        <div className="flex w-64 flex-col bg-slate-700 text-white">
            {/* Profile Section */}
            <div className="border-b border-slate-600 p-4">
                <div className="flex items-center space-x-3">
                    <div className="flex h-12 w-12 items-center justify-center rounded-full bg-teal-500">
                        <img src="/api/placeholder/48/48" alt="Profile" className="h-12 w-12 rounded-full object-cover" />
                    </div>
                    <div>
                        <h3 className="text-sm font-medium">Welcome, {auth.user?.name || 'User'}!</h3>
                    </div>
                </div>
            </div>

            {/* Navigation */}
            <nav className="flex-1 p-4">
                <ul className="space-y-2">
                    <li>
                        <Link
                            href="/dashboard"
                            className={`flex items-center space-x-3 rounded p-2 transition-all duration-200 ${
                                isActive('dashboard') ? 'bg-slate-600 text-white' : 'hover:bg-slate-600'
                            }`}
                        >
                            <Calendar className="h-5 w-5" />
                            <span>DASHBOARD</span>
                        </Link>
                    </li>
                    <li>
                        <Link
                            href="/enrollment"
                            className={`flex items-center space-x-3 rounded p-2 transition-all duration-200 ${
                                isActive('enrollment') ? 'bg-slate-600 text-white' : 'hover:bg-slate-600'
                            }`}
                        >
                            <Users className="h-5 w-5" />
                            <span>ENROLLMENT</span>
                        </Link>
                    </li>
                    <li>
                        <div>
                            <button
                                onClick={() => setBillingOpen(!billingOpen)}
                                className="flex w-full items-center justify-between rounded p-2 transition-all duration-200 hover:bg-slate-600"
                            >
                                <div className="flex items-center space-x-3">
                                    <CreditCard className="h-5 w-5" />
                                    <span>BILLING</span>
                                </div>
                                <ChevronDown className={`h-4 w-4 transition-transform duration-200 ${billingOpen ? 'rotate-180' : ''}`} />
                            </button>
                            {billingOpen && (
                                <div className="mt-2 ml-8 space-y-1">
                                    <Link
                                        href="/tuition"
                                        className={`block rounded p-2 text-sm transition-all duration-200 ${
                                            isActive('tuition') ? 'bg-slate-600' : 'hover:bg-slate-600'
                                        }`}
                                    >
                                        Tuition
                                    </Link>
                                    <Link
                                        href="/invoice"
                                        className={`block rounded p-2 text-sm transition-all duration-200 ${
                                            isActive('invoice') ? 'bg-slate-600' : 'hover:bg-slate-600'
                                        }`}
                                    >
                                        Invoice
                                    </Link>
                                </div>
                            )}
                        </div>
                    </li>
                    <li>
                        <Link
                            href="/studentreport"
                            className={`flex items-center space-x-3 rounded p-2 transition-all duration-200 ${
                                isActive('studentreport') ? 'bg-slate-600 text-white' : 'hover:bg-slate-600'
                            }`}
                        >
                            <FileText className="h-5 w-5" />
                            <span>STUDENT REPORT</span>
                        </Link>
                    </li>
                </ul>
            </nav>

            {/* Logout */}
            <div className="border-t border-slate-600 p-4">
                <Link href="/logout" method="post" className="flex items-center space-x-3 rounded p-2 transition-all duration-200 hover:bg-slate-600">
                    <LogOut className="h-5 w-5" />
                    <span>LOGOUT</span>
                </Link>
            </div>
        </div>
    );
}
