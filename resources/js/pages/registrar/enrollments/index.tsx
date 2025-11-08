import { SchoolYearFilter } from '@/components/school-year-filter';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { DataTable } from '@/components/ui/data-table';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { SortingState } from '@tanstack/react-table';
import { Search } from 'lucide-react';
import { useEffect, useState } from 'react';
import { columns, type Enrollment } from './columns';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface SchoolYear {
    id: number;
    name: string;
    status: string;
}

interface Props {
    enrollments: {
        data: Enrollment[];
        links: PaginationLink[];
        current_page: number;
        last_page: number;
        total: number;
    };
    filters: {
        search?: string;
        status?: string;
        grade_level?: string;
        school_year_id?: string;
        payment_status?: string;
        sort_by?: string;
        sort_direction?: string;
    };
    statuses: Array<{ label: string; value: string }>;
    paymentStatuses: Array<{ label: string; value: string }>;
    schoolYears: SchoolYear[];
}

export default function RegistrarEnrollmentsIndex({ enrollments, filters, statuses, paymentStatuses, schoolYears }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const [status, setStatus] = useState(filters.status || 'all');
    const [gradeLevel, setGradeLevel] = useState(filters.grade_level || 'all');
    const [schoolYearId, setSchoolYearId] = useState(filters.school_year_id || 'all');
    const [paymentStatus, setPaymentStatus] = useState(filters.payment_status || 'all');
    const [sorting, setSorting] = useState<SortingState>(
        filters.sort_by && filters.sort_direction ? [{ id: filters.sort_by, desc: filters.sort_direction === 'desc' }] : [],
    );

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Registrar', href: '/registrar/dashboard' },
        { title: 'Enrollments', href: '/registrar/enrollments' },
    ];

    useEffect(() => {
        router.get(
            '/registrar/enrollments',
            {
                search: search || undefined,
                status: status && status !== 'all' ? status : undefined,
                grade_level: gradeLevel && gradeLevel !== 'all' ? gradeLevel : undefined,
                school_year_id: schoolYearId && schoolYearId !== 'all' ? schoolYearId : undefined,
                payment_status: paymentStatus && paymentStatus !== 'all' ? paymentStatus : undefined,
                sort_by: sorting.length > 0 ? sorting[0].id : undefined,
                sort_direction: sorting.length > 0 ? (sorting[0].desc ? 'desc' : 'asc') : undefined,
            },
            {
                preserveState: true,
                preserveScroll: true,
                only: ['enrollments'],
            },
        );
    }, [status, gradeLevel, schoolYearId, paymentStatus, sorting]);

    const handleSearch = () => {
        router.get(
            '/registrar/enrollments',
            {
                search: search || undefined,
                status: status && status !== 'all' ? status : undefined,
                grade_level: gradeLevel && gradeLevel !== 'all' ? gradeLevel : undefined,
                school_year_id: schoolYearId && schoolYearId !== 'all' ? schoolYearId : undefined,
                payment_status: paymentStatus && paymentStatus !== 'all' ? paymentStatus : undefined,
                sort_by: sorting.length > 0 ? sorting[0].id : undefined,
                sort_direction: sorting.length > 0 ? (sorting[0].desc ? 'desc' : 'asc') : undefined,
            },
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    };

    const handleClearFilters = () => {
        setSearch('');
        setStatus('all');
        setGradeLevel('all');
        setSchoolYearId('all');
        setPaymentStatus('all');
        setSorting([]);
        router.get('/registrar/enrollments', {}, { preserveState: true, preserveScroll: true });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Enrollments" />
            <div className="container mx-auto px-4 py-6">
                <div className="mb-6 flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Enrollments</h1>
                </div>

                <Card className="mb-6 p-6">
                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                        <div className="space-y-2">
                            <label className="text-sm font-medium">Search</label>
                            <Input
                                placeholder="Reference # or student..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
                            />
                        </div>
                        <div className="space-y-2">
                            <label className="text-sm font-medium">Status</label>
                            <Select value={status} onValueChange={setStatus}>
                                <SelectTrigger>
                                    <SelectValue placeholder="All Statuses" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Statuses</SelectItem>
                                    {statuses.map((s) => (
                                        <SelectItem key={s.value} value={s.value}>
                                            {s.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="space-y-2">
                            <label className="text-sm font-medium">Grade Level</label>
                            <Select value={gradeLevel} onValueChange={setGradeLevel}>
                                <SelectTrigger>
                                    <SelectValue placeholder="All Grades" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Grades</SelectItem>
                                    <SelectItem value="kinder">Kinder</SelectItem>
                                    <SelectItem value="grade_1">Grade 1</SelectItem>
                                    <SelectItem value="grade_2">Grade 2</SelectItem>
                                    <SelectItem value="grade_3">Grade 3</SelectItem>
                                    <SelectItem value="grade_4">Grade 4</SelectItem>
                                    <SelectItem value="grade_5">Grade 5</SelectItem>
                                    <SelectItem value="grade_6">Grade 6</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="space-y-2">
                            <label className="text-sm font-medium">School Year</label>
                            <SchoolYearFilter value={schoolYearId} onChange={setSchoolYearId} schoolYears={schoolYears} />
                        </div>
                        <div className="space-y-2">
                            <label className="text-sm font-medium">Payment Status</label>
                            <Select value={paymentStatus} onValueChange={setPaymentStatus}>
                                <SelectTrigger>
                                    <SelectValue placeholder="All Statuses" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Statuses</SelectItem>
                                    {paymentStatuses.map((s) => (
                                        <SelectItem key={s.value} value={s.value}>
                                            {s.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                    <div className="mt-6 flex gap-2">
                        <Button onClick={handleSearch} variant="secondary">
                            <Search className="mr-2 h-4 w-4" />
                            Search
                        </Button>
                        {(search ||
                            (status && status !== 'all') ||
                            (gradeLevel && gradeLevel !== 'all') ||
                            (schoolYearId && schoolYearId !== 'all') ||
                            (paymentStatus && paymentStatus !== 'all')) && (
                            <Button onClick={handleClearFilters} variant="outline">
                                Clear Filters
                            </Button>
                        )}
                    </div>
                </Card>

                <DataTable columns={columns} data={enrollments.data} sorting={sorting} onSortingChange={setSorting} />

                {enrollments.last_page > 1 && (
                    <div className="mt-4 flex justify-center gap-2">
                        {enrollments.links.map((link, index) => (
                            <Link
                                key={index}
                                href={link.url || '#'}
                                preserveState
                                preserveScroll
                                className={`rounded px-3 py-1 ${
                                    link.active
                                        ? 'bg-primary text-primary-foreground'
                                        : link.url
                                          ? 'bg-secondary text-secondary-foreground hover:bg-secondary/80'
                                          : 'cursor-not-allowed bg-muted text-muted-foreground'
                                }`}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        ))}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
