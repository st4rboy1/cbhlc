import { Head, Link, usePage } from '@inertiajs/react';
import { type SharedData } from '@/types';
import { ChevronDown, Calendar, Users, CreditCard, FileText, LogOut } from 'lucide-react';
import { useState } from 'react';

export default function Dashboard() {
    const { auth } = usePage<SharedData>().props;
    const [billingOpen, setBillingOpen] = useState(false);

    return (
        <>
            <Head title="Dashboard" />
            <div className="flex h-screen bg-gray-100">
                {/* Sidebar */}
                <div className="w-64 bg-slate-700 text-white flex flex-col">
                    {/* Profile Section */}
                    <div className="p-4 border-b border-slate-600">
                        <div className="flex items-center space-x-3">
                            <div className="w-12 h-12 bg-teal-500 rounded-full flex items-center justify-center">
                                <img src="/api/placeholder/48/48" alt="Profile" className="w-12 h-12 rounded-full object-cover" />
                            </div>
                            <div>
                                <h3 className="font-medium text-sm">Welcome, {auth.user?.name || 'User'}!</h3>
                            </div>
                        </div>
                    </div>

                    {/* Navigation */}
                    <nav className="flex-1 p-4">
                        <ul className="space-y-2">
                            <li>
                                <Link href="/dashboard" className="flex items-center space-x-3 p-2 rounded bg-slate-600 text-white transition-all duration-200">
                                    <Calendar className="w-5 h-5" />
                                    <span>DASHBOARD</span>
                                </Link>
                            </li>
                            <li>
                                <Link href="/enrollment" className="flex items-center space-x-3 p-2 rounded hover:bg-slate-600 transition-all duration-200">
                                    <Users className="w-5 h-5" />
                                    <span>ENROLLMENT</span>
                                </Link>
                            </li>
                            <li>
                                <div>
                                    <button 
                                        onClick={() => setBillingOpen(!billingOpen)}
                                        className="flex items-center justify-between w-full p-2 rounded hover:bg-slate-600 transition-all duration-200"
                                    >
                                        <div className="flex items-center space-x-3">
                                            <CreditCard className="w-5 h-5" />
                                            <span>BILLING</span>
                                        </div>
                                        <ChevronDown className={`w-4 h-4 transition-transform duration-200 ${billingOpen ? 'rotate-180' : ''}`} />
                                    </button>
                                    {billingOpen && (
                                        <div className="ml-8 mt-2 space-y-1 animate-in slide-in-from-top-2 duration-200">
                                            <Link href="/tuition" className="block p-2 text-sm hover:bg-slate-600 rounded transition-all duration-200">
                                                Tuition
                                            </Link>
                                            <Link href="/invoice" className="block p-2 text-sm hover:bg-slate-600 rounded transition-all duration-200">
                                                Invoice
                                            </Link>
                                        </div>
                                    )}
                                </div>
                            </li>
                            <li>
                                <Link href="/studentreport" className="flex items-center space-x-3 p-2 rounded hover:bg-slate-600 transition-all duration-200">
                                    <FileText className="w-5 h-5" />
                                    <span>STUDENT REPORT</span>
                                </Link>
                            </li>
                        </ul>
                    </nav>

                    {/* Logout */}
                    <div className="p-4 border-t border-slate-600">
                        <Link href="/logout" method="post" className="flex items-center space-x-3 p-2 rounded hover:bg-slate-600 transition-all duration-200">
                            <LogOut className="w-5 h-5" />
                            <span>LOGOUT</span>
                        </Link>
                    </div>
                </div>

                {/* Main Content */}
                <div className="flex-1 flex flex-col">
                    {/* Header */}
                    <header className="bg-white p-6 border-b">
                        <div className="flex justify-between items-center">
                            <h1 className="text-2xl font-bold text-gray-800">DASHBOARD</h1>
                            <div className="flex items-center space-x-4">
                                <span className="text-red-500">🏠</span>
                                <span className="text-sm text-gray-600">Welcome, {auth.user?.name || 'User'}!</span>
                                <div className="w-8 h-8 bg-gray-300 rounded-full"></div>
                            </div>
                        </div>
                    </header>

                    {/* Content Area */}
                    <div className="flex-1 p-6">
                        {/* Image Cards */}
                        <div className="grid grid-cols-2 gap-6 mb-8">
                            <div className="bg-teal-500 rounded-lg h-48 overflow-hidden">
                                <img src="/api/placeholder/400/200" alt="Student 1" className="w-full h-full object-cover" />
                            </div>
                            <div className="bg-teal-500 rounded-lg h-48 overflow-hidden">
                                <img src="/api/placeholder/400/200" alt="Student 2" className="w-full h-full object-cover" />
                            </div>
                        </div>

                        {/* Schedules Section */}
                        <div className="bg-white rounded-lg p-6 shadow-sm">
                            <div className="flex justify-between items-center mb-6">
                                <h2 className="text-xl font-semibold text-gray-800">Schedules</h2>
                                <Calendar className="w-6 h-6 text-gray-600" />
                            </div>
                            
                            <div>
                                <h3 className="text-sm font-medium text-gray-600 mb-4">SCHOOL EVENTS</h3>
                                <div className="space-y-4">
                                    <div className="flex items-center space-x-3">
                                        <div className="w-6 h-6 bg-blue-500 rounded flex items-center justify-center">
                                            <span className="text-white text-xs">📚</span>
                                        </div>
                                        <span className="text-gray-700">START OF CLASSES</span>
                                    </div>
                                    <div className="flex items-center space-x-3">
                                        <div className="w-6 h-6 bg-blue-500 rounded flex items-center justify-center">
                                            <span className="text-white text-xs">👥</span>
                                        </div>
                                        <span className="text-gray-700">PARENT ORIENTATION</span>
                                    </div>
                                    <div className="flex items-center space-x-3">
                                        <div className="w-6 h-6 bg-blue-500 rounded flex items-center justify-center">
                                            <span className="text-white text-xs">🏛️</span>
                                        </div>
                                        <span className="text-gray-700">FOUNDATION DAY</span>
                                    </div>
                                    <div className="flex items-center space-x-3">
                                        <div className="w-6 h-6 bg-blue-500 rounded flex items-center justify-center">
                                            <span className="text-white text-xs">🎄</span>
                                        </div>
                                        <span className="text-gray-700">CHRISTMAS PROGRAM</span>
                                    </div>
                                    <div className="flex items-center space-x-3">
                                        <div className="w-6 h-6 bg-blue-500 rounded flex items-center justify-center">
                                            <span className="text-white text-xs">🎓</span>
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
