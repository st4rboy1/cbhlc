import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { DataTable } from '@/components/ui/data-table';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { PlusCircle, Search } from 'lucide-react';
import { useState } from 'react';
import { columns, type Enrollment } from './columns';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
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
        grade?: string;
        school_year?: string;
    };
    statuses: Array<{ label: string; value: string }>;
}

export default function SuperAdminEnrollmentsIndex({ enrollments, filters, statuses }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const [status, setStatus] = useState(filters.status || 'all');
    const [grade, setGrade] = useState(filters.grade || 'all');
    const [schoolYear, setSchoolYear] = useState(filters.school_year || '');

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Super Admin', href: '/super-admin/dashboard' },
        { title: 'Enrollments', href: '/super-admin/enrollments' },
    ];

    const handleSearch = () => {
        router.get(
            '/super-admin/enrollments',
            {
                search: search || undefined,
                status: status && status !== 'all' ? status : undefined,
                grade: grade && grade !== 'all' ? grade : undefined,
                school_year: schoolYear || undefined,
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
        setGrade('all');
        setSchoolYear('');
        router.get('/super-admin/enrollments', {}, { preserveState: true, preserveScroll: true });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Enrollments" />
            <div className="container mx-auto px-4 py-6">
                <div className="mb-6 flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Enrollments</h1>
                    <Link href="/super-admin/enrollments/create">
                        <Button>
                            <PlusCircle className="mr-2 h-4 w-4" />
                            Create Enrollment
                        </Button>
                    </Link>
                </div>

                {/* Stats Cards */}
                <div className="mb-6 grid gap-4 md:grid-cols-4">
                    <Card className="p-4">
                        <div className="text-sm text-muted-foreground">Total Enrollments</div>
                        <div className="text-2xl font-bold">{enrollments.total}</div>
                    </Card>
                    <Card className="p-4">
                        <div className="text-sm text-muted-foreground">Pending</div>
                        <div className="text-2xl font-bold text-yellow-600">{enrollments.data.filter((e) => e.status === 'pending').length}</div>
                    </Card>
                    <Card className="p-4">
                        <div className="text-sm text-muted-foreground">Approved</div>
                        <div className="text-2xl font-bold text-blue-600">{enrollments.data.filter((e) => e.status === 'approved').length}</div>
                    </Card>
                    <Card className="p-4">
                        <div className="text-sm text-muted-foreground">Enrolled</div>
                        <div className="text-2xl font-bold text-green-600">{enrollments.data.filter((e) => e.status === 'enrolled').length}</div>
                    </Card>
                </div>

                {/* Filters */}
                <Card className="mb-6 p-6">
                    <div className="grid gap-4 md:grid-cols-4">
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
                            <Select value={grade} onValueChange={setGrade}>
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
                            <Input
                                placeholder="YYYY-YYYY"
                                value={schoolYear}
                                onChange={(e) => setSchoolYear(e.target.value)}
                                onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
                            />
                        </div>
                    </div>
                    <div className="mt-6 flex gap-2">
                        <Button onClick={handleSearch} variant="secondary">
                            <Search className="mr-2 h-4 w-4" />
                            Search
                        </Button>
                        {(search || (status && status !== 'all') || (grade && grade !== 'all') || schoolYear) && (
                            <Button onClick={handleClearFilters} variant="outline">
                                Clear Filters
                            </Button>
                        )}
                    </div>
                </Card>

                {/* Data Table */}
                <DataTable columns={columns} data={enrollments.data} />

                {/* Pagination */}
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
