import { Head, Link, usePage } from '@inertiajs/react';
import { type SharedData } from '@/types';
import { ChevronDown, Calendar, Users, CreditCard, FileText, LogOut } from 'lucide-react';
import { useState } from 'react';

export default function Enrollment() {
    const { auth } = usePage<SharedData>().props;
    const [billingOpen, setBillingOpen] = useState(false);

    return (
        <>
            <Head title="Enrollment" />
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
                                <Link href="/enrollment" className="flex items-center space-x-3 p-2 rounded bg-slate-600 text-white transition-all duration-200">
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
                <div className="flex-1 flex flex-col overflow-auto">
                    {/* Header */}
                    <header className="bg-white p-6 border-b">
                        <div className="flex justify-between items-center">
                            <h1 className="text-2xl font-bold text-gray-800">ENROLLMENT</h1>
                            <div className="flex items-center space-x-4">
                                <span className="text-red-500">🏠</span>
                                <span className="text-sm text-gray-600">Welcome, {auth.user?.name || 'User'}!</span>
                                <div className="w-8 h-8 bg-gray-300 rounded-full"></div>
                            </div>
                        </div>
                    </header>

                    {/* Content Area */}
                    <div className="flex-1 py-7 px-12 flex flex-col overflow-auto">
                        <div className="top-bar flex justify-between items-center py-4 border-b-2 border-gray-300 mb-0">
                            <h1 className="m-0 text-3xl text-[#2c3e50]">ENROLL &gt; View My Application</h1>
                            <div className="user-info flex items-center">
                                <button className="icon-btn bg-transparent border-none cursor-pointer text-2xl ml-4 text-[#95a5a6] transition-colors duration-300">🔔</button>
                                <span className="mr-5 font-medium text-[#555]">Welcome, {auth.user?.name || 'User'}!</span>
                                <button className="icon-btn bg-transparent border-none cursor-pointer text-2xl ml-4 text-[#95a5a6] transition-colors duration-300">👤</button>
                            </div>
                        </div>

                        <hr className="my-4 border-none border-b-2 border-gray-300" />
                        <h2 className="text-2xl font-semibold mb-4">Edit Application</h2>

                        <form id="applicationForm" className="bg-white py-6 px-7 rounded-xl shadow-[0_4px_12px_rgba(0,0,0,0.1)]">
                            {/* Student details */}
                            <div className="form-section flex flex-wrap gap-5 mb-5">
                                <div className="form-group flex-1 min-w-[250px] flex flex-col gap-3">
                                    <label className="text-sm font-semibold mb-1 text-[#333]">Grade Level</label>
                                    <select required className="w-full py-2.5 px-3 border border-gray-300 rounded-md text-sm outline-none transition-all duration-200 focus:border-[#457b9d] bg-white">
                                        <option value="">Choose Grade</option>
                                        <option value="grade1">Grade 1</option>
                                        <option value="grade2">Grade 2</option>
                                        <option value="grade3">Grade 3</option>
                                        <option value="grade4">Grade 4</option>
                                        <option value="grade5">Grade 5</option>
                                        <option value="grade6">Grade 6</option>
                                    </select>

                                    <label className="text-sm font-semibold mb-1 text-[#333]">LRN Number</label>
                                    <input type="text" required className="w-full py-2.5 px-3 border border-gray-300 rounded-md text-sm outline-none transition-all duration-200 focus:border-[#457b9d]" />
                                </div>

                                <div className="form-group flex-1 min-w-[250px] flex flex-col gap-3">
                                    <label className="text-sm font-semibold mb-1 text-[#333]">Surname*</label>
                                    <input type="text" required className="w-full py-2.5 px-3 border border-gray-300 rounded-md text-sm outline-none transition-all duration-200 focus:border-[#457b9d]" />

                                    <label className="text-sm font-semibold mb-1 text-[#333]">Given Name*</label>
                                    <input type="text" required className="w-full py-2.5 px-3 border border-gray-300 rounded-md text-sm outline-none transition-all duration-200 focus:border-[#457b9d]" />

                                    <label className="text-sm font-semibold mb-1 text-[#333]">Middle Name</label>
                                    <input type="text" className="w-full py-2.5 px-3 border border-gray-300 rounded-md text-sm outline-none transition-all duration-200 focus:border-[#457b9d]" />
                                </div>

                                <div className="form-group flex-1 min-w-[250px] flex flex-col gap-3">
                                    <label className="text-sm font-semibold mb-1 text-[#333]">Date of Birth*</label>
                                    <input type="date" required className="w-full py-2.5 px-3 border border-gray-300 rounded-md text-sm outline-none transition-all duration-200 focus:border-[#457b9d]" />

                                    <label className="text-sm font-semibold mb-1 text-[#333]">Gender*</label>
                                    <select required className="w-full py-2.5 px-3 border border-gray-300 rounded-md text-sm outline-none transition-all duration-200 focus:border-[#457b9d] bg-white">
                                        <option value="">Choose Gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                    </select>

                                    <label className="text-sm font-semibold mb-1 text-[#333]">Address*</label>
                                    <input type="text" required className="w-full py-2.5 px-3 border border-gray-300 rounded-md text-sm outline-none transition-all duration-200 focus:border-[#457b9d]" />
                                </div>
                            </div>

                            {/* Guardian details */}
                            <h2 className="text-2xl font-semibold mb-4">Guardian Contact Details</h2>
                            <div className="form-group flex-1 min-w-[250px] flex flex-col gap-3">
                                <label className="text-sm font-semibold mb-1 text-[#333]">Surname*</label>
                                <input type="text" required className="w-full py-2.5 px-3 border border-gray-300 rounded-md text-sm outline-none transition-all duration-200 focus:border-[#457b9d]" />

                                <label className="text-sm font-semibold mb-1 text-[#333]">Given Name*</label>
                                <input type="text" required className="w-full py-2.5 px-3 border border-gray-300 rounded-md text-sm outline-none transition-all duration-200 focus:border-[#457b9d]" />

                                <label className="text-sm font-semibold mb-1 text-[#333]">Middle Name*</label>
                                <input type="text" className="w-full py-2.5 px-3 border border-gray-300 rounded-md text-sm outline-none transition-all duration-200 focus:border-[#457b9d]" />
                            </div>

                            <div className="form-group flex-1 min-w-[250px] flex flex-col gap-3">
                                <label className="text-sm font-semibold mb-1 text-[#333]">Cellphone Number*</label>
                                <input type="tel" required className="w-full py-2.5 px-3 border border-gray-300 rounded-md text-sm outline-none transition-all duration-200 focus:border-[#457b9d]" />

                                <label className="text-sm font-semibold mb-1 text-[#333]">Email Address</label>
                                <input type="email" className="w-full py-2.5 px-3 border border-gray-300 rounded-md text-sm outline-none transition-all duration-200 focus:border-[#457b9d]" />

                                <label className="text-sm font-semibold mb-1 text-[#333]">Relation to Student</label>
                                <input type="text" className="w-full py-2.5 px-3 border border-gray-300 rounded-md text-sm outline-none transition-all duration-200 focus:border-[#457b9d]" />
                            </div>

                            {/* File upload */}
                            <div className="upload my-5">
                                <label className="text-sm font-semibold mb-1 text-[#333]">Upload Documents*</label><br />
                                <input type="file" required className="w-full mt-2.5 text-sm py-2.5 px-3 border border-gray-300 rounded-md outline-none transition-all duration-200 focus:border-[#457b9d]" />
                            </div>

                            <nav className="navigation">
                                <a href="/enrollment" style={{ fontSize: '20px', color: 'blue' }}>Save</a>
                            </nav>
                        </form>
                    </div>
                </div>
            </div>
        </>
    );
}
