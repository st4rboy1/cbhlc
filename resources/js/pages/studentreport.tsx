import { Head, Link, usePage } from '@inertiajs/react';
import { type SharedData } from '@/types';
import { ChevronDown, Calendar, Users, CreditCard, FileText, LogOut } from 'lucide-react';
import { useState } from 'react';

export default function StudentReport() {
    const { auth } = usePage<SharedData>().props;
    const [billingOpen, setBillingOpen] = useState(false);

    return (
        <>
            <Head title="Student Report" />
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
                                <Link href="/dashboard" className="flex items-center space-x-3 p-2 rounded hover:bg-slate-600 transition-all duration-200">
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
                                <Link href="/studentreport" className="flex items-center space-x-3 p-2 rounded bg-slate-600 text-white transition-all duration-200">
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
                            <h1 className="text-2xl font-bold text-gray-800">STUDENT REPORT</h1>
                            <div className="flex items-center space-x-4">
                                <span className="text-red-500">🏠</span>
                                <span className="text-sm text-gray-600">Welcome, {auth.user?.name || 'User'}!</span>
                                <div className="w-8 h-8 bg-gray-300 rounded-full"></div>
                            </div>
                        </div>
                    </header>

                    {/* Content Area */}
                    <div className="flex-1 p-6">
                        <div className="bg-white rounded-lg p-6 shadow-sm">
                            <div className="mb-6">
                                <h2 className="text-xl font-semibold text-gray-800 mb-4">READY FOR PAYMENT!</h2>
                                <p className="text-gray-600 mb-6">Visit cashier on-site to finish the process.</p>
                                
                                <div className="space-y-6">
                                    <div>
                                        <h3 className="text-lg font-semibold text-gray-800 mb-3 underline">Student Information</h3>
                                        <div className="grid grid-cols-2 gap-4">
                                            <p><span className="font-semibold">Age:</span> Manero Sj Rodriguez</p>
                                            <p><span className="font-semibold">Gender:</span> Manero Sj Rodriguez</p>
                                            <p><span className="font-semibold">Section:</span> Manero Sj Rodriguez</p>
                                            <p><span className="font-semibold">Birthdate:</span> Manero Sj Rodriguez</p>
                                            <p><span className="font-semibold">Student Name:</span> Manero Sj Rodriguez</p>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <h3 className="text-lg font-semibold text-gray-800 mb-3 underline">Additional Information</h3>
                                        <div className="grid grid-cols-2 gap-4">
                                            <p><span className="font-semibold">Age:</span> Manero Sj Rodriguez</p>
                                            <p><span className="font-semibold">Gender:</span> Manero Sj Rodriguez</p>
                                            <p><span className="font-semibold">Section:</span> Manero Sj Rodriguez</p>
                                            <p><span className="font-semibold">Birthdate:</span> Manero Sj Rodriguez</p>
                                            <p><span className="font-semibold">Student Name:</span> Manero Sj Rodriguez</p>
                                        </div>
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
