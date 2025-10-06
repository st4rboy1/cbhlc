import { CheckCircle, Filter, Search, XCircle } from 'lucide-react';
import { useState } from 'react';

interface Student {
    id: number;
    name: string;
    grade: string;
    status: string;
}

export default function StudentManager({ students: initialStudents }: { students: Student[] }) {
    const [students] = useState(initialStudents);

    const [filterStatus, setFilterStatus] = useState('all');
    const [searchTerm, setSearchTerm] = useState('');

    const getStatusIcon = (status: string) => {
        switch (status) {
            case 'active':
                return <CheckCircle className="h-5 w-5 text-green-500" />;
            case 'inactive':
                return <XCircle className="h-5 w-5 text-red-500" />;
            default:
                return null;
        }
    };

    const getStatusBadge = (status: string) => {
        const colors: { [key: string]: string } = {
            active: 'bg-green-100 text-green-800',
            inactive: 'bg-red-100 text-red-800',
        };
        return colors[status] || 'bg-gray-100 text-gray-800';
    };

    const filteredStudents = students.filter((student) => {
        const matchesStatus = filterStatus === 'all' || student.status === filterStatus;
        const matchesSearch = student.name.toLowerCase().includes(searchTerm.toLowerCase());
        return matchesStatus && matchesSearch;
    });

    const stats = {
        total: students.length,
        active: students.filter((e) => e.status === 'active').length,
        inactive: students.filter((e) => e.status === 'inactive').length,
    };

    return (
        <div className="p-4 sm:p-6 lg:p-8">
            <div className="mx-auto max-w-7xl">
                <div className="mb-8 grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div className="rounded-lg bg-white p-6 shadow">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm text-gray-500">Total Students</p>
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
                                <p className="text-sm text-gray-500">Active</p>
                                <p className="text-3xl font-bold text-green-600">{stats.active}</p>
                            </div>
                            <CheckCircle className="h-8 w-8 text-green-500" />
                        </div>
                    </div>

                    <div className="rounded-lg bg-white p-6 shadow">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm text-gray-500">Inactive</p>
                                <p className="text-3xl font-bold text-red-600">{stats.inactive}</p>
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
                                onClick={() => setFilterStatus('active')}
                                className={`rounded-lg px-4 py-2 font-medium transition ${
                                    filterStatus === 'active' ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                }`}
                            >
                                Active
                            </button>
                            <button
                                onClick={() => setFilterStatus('inactive')}
                                className={`rounded-lg px-4 py-2 font-medium transition ${
                                    filterStatus === 'inactive' ? 'bg-red-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                }`}
                            >
                                Inactive
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
                                </tr>
                            </thead>
                            <tbody>
                                {filteredStudents.map((student) => (
                                    <tr key={student.id} className="border-b border-gray-100 transition hover:bg-gray-50">
                                        <td className="px-4 py-4 text-gray-600">#{student.id}</td>
                                        <td className="px-4 py-4">
                                            <div className="flex items-center gap-3">
                                                <div className="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 font-semibold text-indigo-600">
                                                    {student.name
                                                        .split(' ')
                                                        .map((n) => n[0])
                                                        .join('')}
                                                </div>
                                                <span className="font-medium text-gray-800">{student.name}</span>
                                            </div>
                                        </td>
                                        <td className="px-4 py-4 text-gray-600">{student.grade}</td>
                                        <td className="px-4 py-4">
                                            <span
                                                className={`inline-flex items-center gap-1 rounded-full px-3 py-1 text-sm font-medium ${getStatusBadge(student.status)}`}
                                            >
                                                {getStatusIcon(student.status)}
                                                {student.status.charAt(0).toUpperCase() + student.status.slice(1)}
                                            </span>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>

                        {filteredStudents.length === 0 && (
                            <div className="py-12 text-center text-gray-500">
                                <p className="text-lg">No students found</p>
                                <p className="text-sm">Try adjusting your filters or search term</p>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
}
