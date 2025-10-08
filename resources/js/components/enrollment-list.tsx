import { EnrollmentCard } from '@/components/enrollment-card';
import { EnrollmentFilters } from '@/components/enrollment-filters';
import { Button } from '@/components/ui/button';
import { Plus } from 'lucide-react';
import { useState } from 'react';

interface Enrollment {
    id: number;
    student_name: string;
    grade: string;
    status: 'pending' | 'approved' | 'rejected';
}

const initialEnrollments: Enrollment[] = [
    {
        id: 1,
        student_name: 'John Doe',
        grade: 'Grade 1',
        status: 'pending',
    },
    {
        id: 2,
        student_name: 'Jane Smith',
        grade: 'Grade 2',
        status: 'approved',
    },
    {
        id: 3,
        student_name: 'Michael Johnson',
        grade: 'Grade 3',
        status: 'pending',
    },
    {
        id: 4,
        student_name: 'Emily Davis',
        grade: 'Grade 1',
        status: 'approved',
    },
    {
        id: 5,
        student_name: 'David Wilson',
        grade: 'Grade 4',
        status: 'rejected',
    },
];

export function EnrollmentList() {
    const [enrollments, setEnrollments] = useState<Enrollment[]>(initialEnrollments);
    const [statusFilter, setStatusFilter] = useState<string>('all');
    const [searchQuery, setSearchQuery] = useState('');

    const filteredEnrollments = enrollments.filter((enrollment) => {
        const matchesStatus = statusFilter === 'all' || enrollment.status === statusFilter;
        const matchesSearch =
            enrollment.student_name.toLowerCase().includes(searchQuery.toLowerCase()) ||
            enrollment.grade.toLowerCase().includes(searchQuery.toLowerCase());
        return matchesStatus && matchesSearch;
    });

    const handleStatusChange = (id: number, newStatus: 'pending' | 'approved' | 'rejected') => {
        setEnrollments((prev) => prev.map((enrollment) => (enrollment.id === id ? { ...enrollment, status: newStatus } : enrollment)));
    };

    const statusCounts = {
        all: enrollments.length,
        pending: enrollments.filter((e) => e.status === 'pending').length,
        approved: enrollments.filter((e) => e.status === 'approved').length,
        rejected: enrollments.filter((e) => e.status === 'rejected').length,
    };

    return (
        <div className="space-y-6">
            <div className="flex items-center justify-between">
                <div>
                    <h2 className="text-2xl font-semibold text-foreground">Enrollments</h2>
                    <p className="mt-1 text-sm text-muted-foreground">
                        {filteredEnrollments.length} {filteredEnrollments.length === 1 ? 'enrollment' : 'enrollments'}
                    </p>
                </div>
                <Button className="gap-2">
                    <Plus className="h-4 w-4" />
                    New Enrollment
                </Button>
            </div>

            <EnrollmentFilters
                statusFilter={statusFilter}
                onStatusFilterChange={setStatusFilter}
                searchQuery={searchQuery}
                onSearchQueryChange={setSearchQuery}
                statusCounts={statusCounts}
            />

            <div className="space-y-3">
                {filteredEnrollments.length === 0 ? (
                    <div className="rounded-lg border border-border bg-card py-12 text-center">
                        <p className="text-muted-foreground">No enrollments found</p>
                    </div>
                ) : (
                    filteredEnrollments.map((enrollment) => (
                        <EnrollmentCard key={enrollment.id} enrollment={enrollment} onStatusChange={handleStatusChange} />
                    ))
                )}
            </div>
        </div>
    );
}
