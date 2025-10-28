import { EnrollmentStatusBadge } from '@/components/status-badges';
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/react';
import { type ColumnDef } from '@tanstack/react-table';
import { ArrowUpDown, Edit, Eye } from 'lucide-react';

export interface Student {
    id: number;
    student_id: string;
    first_name: string;
    last_name: string;
}

export interface Guardian {
    id: number;
    first_name: string;
    last_name: string;
    user: {
        name: string;
        email: string;
    };
}

export interface Enrollment {
    id: number;
    enrollment_id: string;
    student: Student;
    guardian: Guardian;
    grade_level: string;
    school_year: string;
    status: string;
    created_at: string;
}

// The handleDelete function is removed as admin users typically don't have direct delete access from the table.
// If needed, a different approach for admin-level deletion with proper authorization should be implemented.

export const columns: ColumnDef<Enrollment>[] = [
    {
        accessorKey: 'enrollment_id',
        header: ({ column }) => {
            return (
                <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                    Reference #
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                </Button>
            );
        },
        cell: ({ row }) => (
            <Link href={`/admin/enrollments/${row.original.id}`} className="font-medium hover:underline">
                {row.getValue('enrollment_id')}
            </Link>
        ),
    },
    {
        id: 'student',
        accessorFn: (row) => `${row.student?.first_name} ${row.student?.last_name}`,
        header: ({ column }) => {
            return (
                <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                    Student
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                </Button>
            );
        },
        cell: ({ row }) => {
            const enrollment = row.original;
            return (
                <div>
                    <div>
                        {enrollment.student?.first_name} {enrollment.student?.last_name}
                    </div>
                    <div className="text-sm text-muted-foreground">ID: {enrollment.student?.student_id}</div>
                </div>
            );
        },
    },
    {
        id: 'guardian',
        accessorFn: (row) => `${row.guardian?.first_name} ${row.guardian?.last_name}`,
        header: ({ column }) => {
            return (
                <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                    Guardian
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                </Button>
            );
        },
        cell: ({ row }) => {
            const enrollment = row.original;
            return (
                <div>
                    <div>
                        {enrollment.guardian?.first_name} {enrollment.guardian?.last_name}
                    </div>
                    <div className="text-sm text-muted-foreground">{enrollment.guardian?.user?.email}</div>
                </div>
            );
        },
    },
    {
        accessorKey: 'grade_level',
        header: ({ column }) => {
            return (
                <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                    Grade Level
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                </Button>
            );
        },
        cell: ({ row }) => <div>{row.getValue('grade_level')}</div>,
    },
    {
        accessorKey: 'school_year',
        header: ({ column }) => {
            return (
                <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                    School Year
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                </Button>
            );
        },
        cell: ({ row }) => <div>S.Y. {row.getValue('school_year')}</div>,
    },
    {
        accessorKey: 'status',
        header: ({ column }) => {
            return (
                <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                    Status
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                </Button>
            );
        },
        cell: ({ row }) => <EnrollmentStatusBadge status={row.getValue('status')} />,
    },
    {
        id: 'actions',
        header: () => <div className="text-right">Actions</div>,
        cell: ({ row }) => {
            const enrollment = row.original;

            return (
                <div className="flex justify-end gap-2">
                    <Link href={`/admin/enrollments/${enrollment.id}`}>
                        <Button size="sm" variant="outline">
                            <Eye className="h-4 w-4" />
                        </Button>
                    </Link>
                    <Link href={`/admin/enrollments/${enrollment.id}/edit`}>
                        <Button size="sm" variant="outline">
                            <Edit className="h-4 w-4" />
                        </Button>
                    </Link>
                </div>
            );
        },
    },
];
