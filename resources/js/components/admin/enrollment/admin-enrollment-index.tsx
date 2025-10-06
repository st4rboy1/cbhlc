import { router } from '@inertiajs/react';
import { CheckCircle, Clock, Filter, Search, XCircle } from 'lucide-react';
import { useState } from 'react';

interface Enrollment {
    id: number;
    student_name: string;
    grade: string;
    status: string;
}

export default function EnrollmentManager({ enrollments: initialEnrollments }: { enrollments: Enrollment[] }) {
    const [enrollments, setEnrollments] = useState(initialEnrollments);

    const [filterStatus, setFilterStatus] = useState('all');
    const [searchTerm, setSearchTerm] = useState('');

    const updateStatus = (id: number, newStatus: string) => {
        if (newStatus === 'rejected') {
            const reason = prompt('Enter reason for rejection:');
            if (!reason) return;
            router.post(
                `/admin/enrollments/${id}/reject`,
                { reason },
                {
                    onSuccess: () => {
                        setEnrollments(enrollments.map((enrollment) => (enrollment.id === id ? { ...enrollment, status: newStatus } : enrollment)));
                    },
                },
            );
        } else {
            router.post(
                `/admin/enrollments/${id}/${newStatus}`,
                {},
                {
                    onSuccess: () => {
                        setEnrollments(enrollments.map((enrollment) => (enrollment.id === id ? { ...enrollment, status: newStatus } : enrollment)));
                    },
                },
            );
        }
    };

    const getStatusIcon = (status: string) => {
        switch (status) {
            case 'approved':
                return <CheckCircle className="h-5 w-5 text-green-500" />;
            case 'pending':
                return <Clock className="h-5 w-5 text-yellow-500" />;
            case 'rejected':
                return <XCircle className="h-5 w-5 text-red-500" />;
            default:
                return null;
        }
    };

    const getStatusBadge = (status: string) => {
        const colors: { [key: string]: string } = {
            approved: 'bg-green-100 text-green-800',
            pending: 'bg-yellow-100 text-yellow-800',
            rejected: 'bg-red-100 text-red-800',
        };
        return colors[status] || 'bg-gray-100 text-gray-800';
    };

    const filteredEnrollments = enrollments.filter((enrollment) => {
        const matchesStatus = filterStatus === 'all' || enrollment.status === filterStatus;
        const matchesSearch = enrollment.student_name.toLowerCase().includes(searchTerm.toLowerCase());
        return matchesStatus && matchesSearch;
    });

    const stats = {
        total: enrollments.length,
        approved: enrollments.filter((e) => e.status === 'approved').length,
        pending: enrollments.filter((e) => e.status === 'pending').length,
        rejected: enrollments.filter((e) => e.status === 'rejected').length,
    };

    return (
        <div className="p-4 sm:p-6 lg:p-8">
            <div className="mx-auto max-w-7xl">
                <div className="mb-8 grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div className="rounded-lg bg-white p-6 shadow">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm text-gray-500">Total</p>
                                <p className="text-3xl font-bold text-gray-800">{stats.total}</p>
                            </div>
                            <div className="rounded-full bg-blue-100 p-3">
                                <Filter className="h-6 w-6 text-blue-600" />
                            </div>
                        </div>
                    </div>

                    <div className="rounded-lg bg-white p-6 shadow">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm text-gray-500">Approved</p>
                                <p className="text-3xl font-bold text-green-600">{stats.approved}</p>
                            </div>
                            <CheckCircle className="h-8 w-8 text-green-500" />
                        </div>
                    </div>

                    <div className="rounded-lg bg-white p-6 shadow">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm text-gray-500">Pending</p>
                                <p className="text-3xl font-bold text-yellow-600">{stats.pending}</p>
                            </div>
                            <Clock className="h-8 w-8 text-yellow-500" />
                        </div>
                    </div>

                    <div className="rounded-lg bg-white p-6 shadow">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm text-gray-500">Rejected</p>
                                <p className="text-3xl font-bold text-red-600">{stats.rejected}</p>
                            </div>
                            <XCircle className="h-8 w-8 text-red-500" />
                        </div>
                    </div>
                </div>

                <div className="rounded-lg bg-white p-6 shadow-lg">
                    <div className="mb-6 flex flex-col gap-4 md:flex-row">
                        <div className="relative flex-1">
                            <Search className="absolute top-1/2 left-3 h-5 w-5 -translate-y-1/2 transform text-gray-400" />
                            <input
                                type="text"
                                placeholder="Search by student name..."
                                className="w-full rounded-lg border border-gray-300 py-2 pr-4 pl-10 focus:border-transparent focus:ring-2 focus:ring-indigo-500"
                                value={searchTerm}
                                onChange={(e) => setSearchTerm(e.target.value)}
                            />
                        </div>

                        <div className="flex gap-2">
                            <button
                                onClick={() => setFilterStatus('all')}
                                className={`rounded-lg px-4 py-2 font-medium transition ${
                                    filterStatus === 'all' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                }`}
                            >
                                All
                            </button>
                            <button
                                onClick={() => setFilterStatus('pending')}
                                className={`rounded-lg px-4 py-2 font-medium transition ${
                                    filterStatus === 'pending' ? 'bg-yellow-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                }`}
                            >
                                Pending
                            </button>
                            <button
                                onClick={() => setFilterStatus('approved')}
                                className={`rounded-lg px-4 py-2 font-medium transition ${
                                    filterStatus === 'approved' ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                }`}
                            >
                                Approved
                            </button>
                        </div>
                    </div>

                    <div className="overflow-x-auto">
                        <table className="w-full">
                            <thead>
                                <tr className="border-b border-gray-200">
                                    <th className="px-4 py-3 text-left font-semibold text-gray-700">ID</th>
                                    <th className="px-4 py-3 text-left font-semibold text-gray-700">Student Name</th>
                                    <th className="px-4 py-3 text-left font-semibold text-gray-700">Grade</th>
                                    <th className="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
                                    <th className="px-4 py-3 text-left font-semibold text-gray-700">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {filteredEnrollments.map((enrollment) => (
                                    <tr key={enrollment.id} className="border-b border-gray-100 transition hover:bg-gray-50">
                                        <td className="px-4 py-4 text-gray-600">#{enrollment.id}</td>
                                        <td className="px-4 py-4">
                                            <div className="flex items-center gap-3">
                                                <div className="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 font-semibold text-indigo-600">
                                                    {enrollment.student_name
                                                        .split(' ')
                                                        .map((n) => n[0])
                                                        .join('')}
                                                </div>
                                                <span className="font-medium text-gray-800">{enrollment.student_name}</span>
                                            </div>
                                        </td>
                                        <td className="px-4 py-4 text-gray-600">{enrollment.grade}</td>
                                        <td className="px-4 py-4">
                                            <span
                                                className={`inline-flex items-center gap-1 rounded-full px-3 py-1 text-sm font-medium ${getStatusBadge(enrollment.status)}`}
                                            >
                                                {getStatusIcon(enrollment.status)}
                                                {enrollment.status.charAt(0).toUpperCase() + enrollment.status.slice(1)}
                                            </span>
                                        </td>
                                        <td className="px-4 py-4">
                                            <div className="flex gap-2">
                                                {enrollment.status !== 'approved' && (
                                                    <button
                                                        onClick={() => updateStatus(enrollment.id, 'approved')}
                                                        className="rounded bg-green-500 px-3 py-1 text-sm text-white transition hover:bg-green-600"
                                                    >
                                                        Approve
                                                    </button>
                                                )}
                                                {enrollment.status !== 'rejected' && (
                                                    <button
                                                        onClick={() => updateStatus(enrollment.id, 'rejected')}
                                                        className="rounded bg-red-500 px-3 py-1 text-sm text-white transition hover:bg-red-600"
                                                    >
                                                        Reject
                                                    </button>
                                                )}
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>

                        {filteredEnrollments.length === 0 && (
                            <div className="py-12 text-center text-gray-500">
                                <p className="text-lg">No enrollments found</p>
                                <p className="text-sm">Try adjusting your filters or search term</p>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
}
