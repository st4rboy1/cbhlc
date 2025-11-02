import { SchoolYearFilter } from '@/components/school-year-filter';
import { EnrollmentStatusBadge, PaymentStatusBadge } from '@/components/status-badges';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { DataTable } from '@/components/ui/data-table';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { type ColumnDef } from '@tanstack/react-table';
import { PlusCircle, Search, X } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import { toast } from 'sonner';

interface Enrollment {
    id: number;
    student: {
        first_name: string;
        last_name: string;
    };
    school_year?: {
        id: number;
        name: string;
        start_year: number;
        end_year: number;
    };
    grade_level: string;
    status: 'pending' | 'approved' | 'enrolled' | 'rejected' | 'completed';
    payment_status: 'pending' | 'partial' | 'paid' | 'overdue';
    created_at: string;
}

interface FilterOption {
    value: string;
    label: string;
}

interface SchoolYear {
    id: number;
    name: string;
    status: string;
}

interface Props {
    enrollments: {
        data: Enrollment[];
        links: unknown;
        meta: unknown;
    };
    filters: {
        school_year_id?: string;
        student_id?: string;
        status?: string;
        search?: string;
    };
    filterOptions: {
        students: FilterOption[];
        schoolYears: SchoolYear[];
        statuses: FilterOption[];
    };
}

export default function GuardianEnrollmentsIndex({ enrollments, filters, filterOptions }: Props) {
    const { props } = usePage();
    const flash = props.flash as { success?: string; error?: string } | undefined;

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Guardian', href: '/guardian/dashboard' },
        { title: 'Enrollments', href: '/guardian/enrollments' },
    ];

    const [searchInput, setSearchInput] = useState(filters.search || '');

    // Show flash messages as toasts
    useEffect(() => {
        if (flash?.success) {
            toast.success(flash.success);
        }
        if (flash?.error) {
            toast.error(flash.error);
        }
    }, [flash]);

    const handleFilterChange = (key: string, value: string) => {
        router.get(
            '/guardian/enrollments',
            {
                ...filters,
                [key]: value || undefined,
            },
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    };

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        handleFilterChange('search', searchInput);
    };

    const handleClearFilters = () => {
        setSearchInput('');
        router.get('/guardian/enrollments', {}, { preserveState: true });
    };

    const hasActiveFilters = filters.school_year_id || filters.student_id || filters.status || filters.search;

    const columns: ColumnDef<Enrollment>[] = useMemo(
        () => [
            {
                accessorKey: 'student',
                header: 'Student Name',
                cell: ({ row }) => {
                    const student = row.original.student;
                    return `${student.first_name} ${student.last_name}`;
                },
            },
            {
                accessorKey: 'school_year',
                header: 'School Year',
                cell: ({ row }) => {
                    return row.original.school_year?.name || '-';
                },
            },
            {
                accessorKey: 'grade_level',
                header: 'Grade Level',
            },
            {
                accessorKey: 'status',
                header: 'Status',
                cell: ({ row }) => <EnrollmentStatusBadge status={row.original.status} />,
            },
            {
                accessorKey: 'payment_status',
                header: 'Payment Status',
                cell: ({ row }) => <PaymentStatusBadge status={row.original.payment_status} />,
            },
            {
                accessorKey: 'created_at',
                header: 'Submission Date',
                cell: ({ row }) => {
                    return new Date(row.original.created_at).toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric',
                    });
                },
            },
            {
                id: 'actions',
                header: 'Actions',
                cell: ({ row }) => {
                    const enrollment = row.original;
                    return (
                        <div className="flex gap-2">
                            <Link href={`/guardian/enrollments/${enrollment.id}`}>
                                <Button size="sm" variant="outline">
                                    View
                                </Button>
                            </Link>
                            {enrollment.status === 'pending' && (
                                <Link href={`/guardian/enrollments/${enrollment.id}/edit`}>
                                    <Button size="sm" variant="outline">
                                        Edit
                                    </Button>
                                </Link>
                            )}
                        </div>
                    );
                },
            },
        ],
        [],
    );

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="My Children's Enrollments" />

            <div className="px-4 py-6">
                <div className="mb-4 flex items-center justify-between">
                    <h1 className="text-2xl font-bold">My Children's Enrollments</h1>
                    <Link href="/guardian/enrollments/create">
                        <Button>
                            <PlusCircle className="mr-2 h-4 w-4" />
                            New Enrollment
                        </Button>
                    </Link>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Enrollment Applications</CardTitle>
                        <CardDescription>View and manage your children's enrollment applications</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="mb-4 space-y-4">
                            <form onSubmit={handleSearch} className="flex gap-2">
                                <div className="relative flex-1">
                                    <Search className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                    <Input
                                        placeholder="Search by student name or enrollment ID..."
                                        value={searchInput}
                                        onChange={(e) => setSearchInput(e.target.value)}
                                        className="pl-9"
                                    />
                                </div>
                                <Button type="submit" variant="secondary">
                                    Search
                                </Button>
                            </form>

                            <div className="flex flex-wrap gap-2">
                                <div className="w-[180px]">
                                    <SchoolYearFilter
                                        value={filters.school_year_id || 'all'}
                                        onChange={(value) => handleFilterChange('school_year_id', value === 'all' ? '' : value)}
                                        schoolYears={filterOptions.schoolYears}
                                    />
                                </div>

                                <Select
                                    value={filters.student_id || 'all'}
                                    onValueChange={(value) => handleFilterChange('student_id', value === 'all' ? '' : value)}
                                >
                                    <SelectTrigger className="w-[180px]">
                                        <SelectValue placeholder="Student" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Students</SelectItem>
                                        {filterOptions.students.map((option) => (
                                            <SelectItem key={option.value} value={option.value}>
                                                {option.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>

                                <Select
                                    value={filters.status || 'all'}
                                    onValueChange={(value) => handleFilterChange('status', value === 'all' ? '' : value)}
                                >
                                    <SelectTrigger className="w-[180px]">
                                        <SelectValue placeholder="Status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Statuses</SelectItem>
                                        {filterOptions.statuses.map((option) => (
                                            <SelectItem key={option.value} value={option.value}>
                                                {option.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>

                                {hasActiveFilters && (
                                    <Button variant="ghost" onClick={handleClearFilters}>
                                        <X className="mr-2 h-4 w-4" />
                                        Clear Filters
                                    </Button>
                                )}
                            </div>
                        </div>

                        <DataTable columns={columns} data={enrollments.data} />
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
