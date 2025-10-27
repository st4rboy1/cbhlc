import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Link } from '@inertiajs/react';
import { type ColumnDef } from '@tanstack/react-table';
import { ArrowUpDown, Eye, GraduationCap, MoreHorizontal, Pencil } from 'lucide-react';

export interface Student {
    id: number;
    student_id: string;
    first_name: string;
    middle_name: string;
    last_name: string;
    full_name: string;
    birthdate: string;
    gender: string;
    grade_level: string | null;
    latest_enrollment: {
        school_year: string;
        status: string;
        grade_level: string;
    } | null;
}

const statusColors = {
    pending: 'secondary',
    enrolled: 'default',
    rejected: 'destructive',
    completed: 'outline',
} as const;

function calculateAge(birthdate: string): number {
    const birth = new Date(birthdate);
    const today = new Date();
    let age = today.getFullYear() - birth.getFullYear();
    const monthDiff = today.getMonth() - birth.getMonth();
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
        age--;
    }
    return age;
}

export const columns: ColumnDef<Student>[] = [
    {
        accessorKey: 'student_id',
        header: ({ column }) => {
            return (
                <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                    Student ID
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                </Button>
            );
        },
        cell: ({ row }) => <div className="font-mono text-sm">{row.getValue('student_id')}</div>,
    },
    {
        accessorKey: 'full_name',
        header: ({ column }) => {
            return (
                <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                    Name
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                </Button>
            );
        },
        cell: ({ row }) => <div className="font-medium">{row.getValue('full_name')}</div>,
    },
    {
        accessorKey: 'birthdate',
        header: 'Age',
        cell: ({ row }) => {
            const birthdate = row.getValue('birthdate') as string;
            return <div className="text-sm">{calculateAge(birthdate)} years</div>;
        },
    },
    {
        accessorKey: 'gender',
        header: ({ column }) => {
            return (
                <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                    Gender
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                </Button>
            );
        },
        cell: ({ row }) => <div className="text-sm">{row.getValue('gender')}</div>,
    },
    {
        accessorKey: 'grade_level',
        header: 'Current Grade',
        cell: ({ row }) => {
            const gradeLevel = row.getValue('grade_level') as string | null;
            return <div className="text-sm">{gradeLevel || <span className="text-muted-foreground">Not enrolled</span>}</div>;
        },
    },
    {
        accessorKey: 'latest_enrollment',
        header: 'Enrollment Status',
        cell: ({ row }) => {
            const enrollment = row.getValue('latest_enrollment') as Student['latest_enrollment'];
            if (!enrollment) {
                return <Badge variant="outline">No enrollment</Badge>;
            }
            return (
                <Badge variant={statusColors[enrollment.status as keyof typeof statusColors] || 'default'}>
                    {enrollment.status.charAt(0).toUpperCase() + enrollment.status.slice(1)}
                </Badge>
            );
        },
    },
    {
        id: 'actions',
        enableHiding: false,
        cell: ({ row }) => {
            const student = row.original;

            return (
                <div className="text-right">
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <Button variant="ghost" className="h-8 w-8 p-0">
                                <span className="sr-only">Open menu</span>
                                <MoreHorizontal className="h-4 w-4" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            <DropdownMenuLabel>Actions</DropdownMenuLabel>
                            <DropdownMenuItem asChild>
                                <Link href={`/guardian/students/${student.id}`} className="flex cursor-pointer items-center">
                                    <Eye className="mr-2 h-4 w-4" />
                                    View details
                                </Link>
                            </DropdownMenuItem>
                            <DropdownMenuItem asChild>
                                <Link href={`/guardian/students/${student.id}/edit`} className="flex cursor-pointer items-center">
                                    <Pencil className="mr-2 h-4 w-4" />
                                    Edit
                                </Link>
                            </DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem asChild>
                                <Link href={`/guardian/enrollments/create?student_id=${student.id}`} className="flex cursor-pointer items-center">
                                    <GraduationCap className="mr-2 h-4 w-4" />
                                    Enroll Student
                                </Link>
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            );
        },
    },
];
