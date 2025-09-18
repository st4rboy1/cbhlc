import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { Calendar, ChevronDown, CreditCard, FileText, LogOut, Users } from 'lucide-react';
import { useState } from 'react';

export default function Dashboard() {
    const { auth } = usePage<SharedData>().props;
    const [billingOpen, setBillingOpen] = useState(false);

    return (
        <>
            <Head title="Dashboard" />
            <div className="flex h-screen bg-gray-100">
                {/* Sidebar */}
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
                                    className="flex items-center space-x-3 rounded bg-slate-600 p-2 text-white transition-all duration-200"
                                >
                                    <Calendar className="h-5 w-5" />
                                    <span>DASHBOARD</span>
                                </Link>
                            </li>
                            <li>
                                <Link
                                    href="/enrollment"
                                    className="flex items-center space-x-3 rounded p-2 transition-all duration-200 hover:bg-slate-600"
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
                                        <div className="mt-2 ml-8 space-y-1 duration-200 animate-in slide-in-from-top-2">
                                            <Link
                                                href="/tuition"
                                                className="block rounded p-2 text-sm transition-all duration-200 hover:bg-slate-600"
                                            >
                                                Tuition
                                            </Link>
                                            <Link
                                                href="/invoice"
                                                className="block rounded p-2 text-sm transition-all duration-200 hover:bg-slate-600"
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
                                    className="flex items-center space-x-3 rounded p-2 transition-all duration-200 hover:bg-slate-600"
                                >
                                    <FileText className="h-5 w-5" />
                                    <span>STUDENT REPORT</span>
                                </Link>
                            </li>
                        </ul>
                    </nav>

                    {/* Logout */}
                    <div className="border-t border-slate-600 p-4">
                        <Link
                            href="/logout"
                            method="post"
                            className="flex items-center space-x-3 rounded p-2 transition-all duration-200 hover:bg-slate-600"
                        >
                            <LogOut className="h-5 w-5" />
                            <span>LOGOUT</span>
                        </Link>
                    </div>
                </div>

                {/* Main Content */}
                <div className="flex flex-1 flex-col">
                    {/* Header */}
                    <header className="border-b bg-white p-6">
                        <div className="flex items-center justify-between">
                            <h1 className="text-2xl font-bold text-gray-800">DASHBOARD</h1>
                            <div className="flex items-center space-x-4">
                                <span className="text-red-500">🏠</span>
                                <span className="text-sm text-gray-600">Welcome, {auth.user?.name || 'User'}!</span>
                                <div className="h-8 w-8 rounded-full bg-gray-300"></div>
                            </div>
                        </div>
                    </header>

                    {/* Content Area */}
                    <div className="flex-1 p-6">
                        {/* Image Cards */}
                        <div className="mb-8 grid grid-cols-2 gap-6">
                            <div className="h-48 overflow-hidden rounded-lg bg-teal-500">
                                <img src="/api/placeholder/400/200" alt="Student 1" className="h-full w-full object-cover" />
                            </div>
                            <div className="h-48 overflow-hidden rounded-lg bg-teal-500">
                                <img src="/api/placeholder/400/200" alt="Student 2" className="h-full w-full object-cover" />
                            </div>
                        </div>

                        {/* Schedules Section */}
                        <div className="rounded-lg bg-white p-6 shadow-sm">
                            <div className="mb-6 flex items-center justify-between">
                                <h2 className="text-xl font-semibold text-gray-800">Schedules</h2>
                                <Calendar className="h-6 w-6 text-gray-600" />
                            </div>

                            <div>
                                <h3 className="mb-4 text-sm font-medium text-gray-600">SCHOOL EVENTS</h3>
                                <div className="space-y-4">
                                    <div className="flex items-center space-x-3">
                                        <div className="flex h-6 w-6 items-center justify-center rounded bg-blue-500">
                                            <span className="text-xs text-white">📚</span>
                                        </div>
                                        <span className="text-gray-700">START OF CLASSES</span>
                                    </div>
                                    <div className="flex items-center space-x-3">
                                        <div className="flex h-6 w-6 items-center justify-center rounded bg-blue-500">
                                            <span className="text-xs text-white">👥</span>
                                        </div>
                                        <span className="text-gray-700">PARENT ORIENTATION</span>
                                    </div>
                                    <div className="flex items-center space-x-3">
                                        <div className="flex h-6 w-6 items-center justify-center rounded bg-blue-500">
                                            <span className="text-xs text-white">🏛️</span>
                                        </div>
                                        <span className="text-gray-700">FOUNDATION DAY</span>
                                    </div>
                                    <div className="flex items-center space-x-3">
                                        <div className="flex h-6 w-6 items-center justify-center rounded bg-blue-500">
                                            <span className="text-xs text-white">🎄</span>
                                        </div>
                                        <span className="text-gray-700">CHRISTMAS PROGRAM</span>
                                    </div>
                                    <div className="flex items-center space-x-3">
                                        <div className="flex h-6 w-6 items-center justify-center rounded bg-blue-500">
                                            <span className="text-xs text-white">🎓</span>
                                        </div>
                                        <span className="text-gray-700">GRADUATION DAY</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
