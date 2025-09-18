import { type SharedData } from '@/types';
import { Head, usePage } from '@inertiajs/react';
import { Calendar } from 'lucide-react';
import Sidebar from '../components/Sidebar';

export default function Dashboard() {
    const { auth } = usePage<SharedData>().props;

    return (
        <>
            <Head title="Dashboard" />
            <div className="flex h-screen bg-gray-100">
                {/* Sidebar */}
                <Sidebar currentPage="dashboard" />

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
