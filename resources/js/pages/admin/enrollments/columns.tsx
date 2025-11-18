import { EnrollmentStatusBadge } from '@/components/status-badges';
import { Button } from '@/components/ui/button';
import { ConfirmationDialog } from '@/components/ui/confirmation-dialog';
import { Link, router } from '@inertiajs/react';
import { type ColumnDef } from '@tanstack/react-table';
import { ArrowUpDown, Edit, Eye, Trash } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';

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

function ActionsCell({ enrollment }: { enrollment: Enrollment }) {
    const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);

    const handleDelete = () => {
        router.delete(`/admin/enrollments/${enrollment.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Enrollment deleted successfully');
                setDeleteDialogOpen(false);
            },
            onError: () => {
                toast.error('Failed to delete enrollment. Please try again.');
            },
        });
    };

    return (
        <>
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
                {enrollment.status === 'pending' && (
                    <Button size="sm" variant="destructive" onClick={() => setDeleteDialogOpen(true)}>
                        <Trash className="h-4 w-4" />
                    </Button>
                )}
            </div>

            <ConfirmationDialog
                open={deleteDialogOpen}
                onOpenChange={setDeleteDialogOpen}
                onConfirm={handleDelete}
                title="Delete Enrollment?"
                description={`Are you sure you want to delete enrollment ${enrollment.enrollment_id}? This action cannot be undone and only pending enrollments can be deleted.`}
                confirmText="Delete"
                variant="destructive"
            />
        </>
    );
}

export const columns: ColumnDef<Enrollment>[] = [
    {
        accessorKey: 'enrollment_id',
        header: ({ column }) => {
            return (
                <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                    Enrollment ID
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
        cell: ({ row }) => <ActionsCell enrollment={row.original} />,
    },
];
