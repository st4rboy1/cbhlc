import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Link, router } from '@inertiajs/react';
import { type ColumnDef } from '@tanstack/react-table';
import { ArrowUpDown, Edit, Eye, Trash } from 'lucide-react';

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
    reference_number: string;
    student: Student;
    guardian: Guardian;
    grade_level: string;
    school_year: string;
    status: string;
    created_at: string;
}

const getStatusBadge = (status: string) => {
    const variants: Record<string, { variant: 'default' | 'secondary' | 'destructive' | 'outline'; className?: string; label: string }> = {
        pending: { variant: 'outline', className: 'bg-yellow-100 text-yellow-800', label: 'Pending Review' },
        approved: { variant: 'default', className: 'bg-blue-100 text-blue-800', label: 'Approved' },
        rejected: { variant: 'destructive', label: 'Rejected' },
        ready_for_payment: { variant: 'outline', className: 'bg-yellow-100 text-yellow-800', label: 'Ready for Payment' },
        paid: { variant: 'default', className: 'bg-green-100 text-green-800', label: 'Paid' },
        enrolled: { variant: 'default', className: 'bg-primary text-primary-foreground', label: 'Enrolled' },
        completed: { variant: 'secondary', label: 'Completed' },
    };

    const config = variants[status] || { variant: 'outline' as const, label: status };

    return (
        <Badge variant={config.variant} className={config.className}>
            {config.label}
        </Badge>
    );
};

const handleDelete = (id: number, referenceNumber: string) => {
    if (
        confirm(
            `Are you sure you want to delete enrollment ${referenceNumber}? This action cannot be undone and only pending enrollments can be deleted.`,
        )
    ) {
        router.delete(`/super-admin/enrollments/${id}`);
    }
};

export const columns: ColumnDef<Enrollment>[] = [
    {
        accessorKey: 'reference_number',
        header: ({ column }) => {
            return (
                <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                    Reference #
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                </Button>
            );
        },
        cell: ({ row }) => <div className="font-medium">{row.getValue('reference_number')}</div>,
    },
    {
        id: 'student',
        accessorFn: (row) => `${row.student.first_name} ${row.student.last_name}`,
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
                        {enrollment.student.first_name} {enrollment.student.last_name}
                    </div>
                    <div className="text-sm text-muted-foreground">ID: {enrollment.student.student_id}</div>
                </div>
            );
        },
    },
    {
        id: 'guardian',
        accessorFn: (row) => `${row.guardian.first_name} ${row.guardian.last_name}`,
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
                        {enrollment.guardian.first_name} {enrollment.guardian.last_name}
                    </div>
                    <div className="text-sm text-muted-foreground">{enrollment.guardian.user.email}</div>
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
        cell: ({ row }) => <div>{row.getValue('school_year')}</div>,
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
        cell: ({ row }) => getStatusBadge(row.getValue('status')),
    },
    {
        id: 'actions',
        header: () => <div className="text-right">Actions</div>,
        cell: ({ row }) => {
            const enrollment = row.original;

            return (
                <div className="flex justify-end gap-2">
                    <Link href={`/super-admin/enrollments/${enrollment.id}`}>
                        <Button size="sm" variant="outline">
                            <Eye className="h-4 w-4" />
                        </Button>
                    </Link>
                    <Link href={`/super-admin/enrollments/${enrollment.id}/edit`}>
                        <Button size="sm" variant="outline">
                            <Edit className="h-4 w-4" />
                        </Button>
                    </Link>
                    {enrollment.status === 'pending' && (
                        <Button size="sm" variant="destructive" onClick={() => handleDelete(enrollment.id, enrollment.reference_number)}>
                            <Trash className="h-4 w-4" />
                        </Button>
                    )}
                </div>
            );
        },
    },
];
