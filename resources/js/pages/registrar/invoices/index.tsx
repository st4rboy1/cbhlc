import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Card } from '@/components/ui/card';
import { DataTable } from '@/components/ui/data-table';
import { Input } from '@/components/ui/input';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { SortingState } from '@tanstack/react-table';
import { format } from 'date-fns';
import { CalendarIcon, PlusCircle, Search } from 'lucide-react';
import { useEffect, useState } from 'react';
import { columns } from './columns';

interface Student {
    id: number;
    student_id: string;
    first_name: string;
    last_name: string;
}

interface Enrollment {
    id: number;
    student: Student;
}

interface Invoice {
    id: number;
    invoice_number: string;
    enrollment: Enrollment;
    total_amount: number;
    paid_amount: number;
    status: string;
    due_date: string;
    created_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    invoices: {
        data: Invoice[];
        links: PaginationLink[];
        current_page: number;
        last_page: number;
        total: number;
    };
    filters: {
        search?: string;
        status?: string;
        from_date?: string;
        to_date?: string;
        sort_by?: string;
        sort_direction?: string;
    };
}

export default function RegistrarInvoicesIndex({ invoices, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const [status, setStatus] = useState(filters.status || 'all');
    const [fromDate, setFromDate] = useState<Date | undefined>(filters.from_date ? new Date(filters.from_date) : undefined);
    const [toDate, setToDate] = useState<Date | undefined>(filters.to_date ? new Date(filters.to_date) : undefined);
    const [sorting, setSorting] = useState<SortingState>(
        filters.sort_by && filters.sort_direction ? [{ id: filters.sort_by, desc: filters.sort_direction === 'desc' }] : [],
    );

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Registrar', href: '/registrar/dashboard' },
        { title: 'Invoices', href: '/registrar/invoices' },
    ];

    useEffect(() => {
        const handler = setTimeout(() => {
            router.get(
                route('registrar.invoices.index'),
                {
                    ...filters,
                    sort_by: sorting.length > 0 ? sorting[0].id : undefined,
                    sort_direction: sorting.length > 0 ? (sorting[0].desc ? 'desc' : 'asc') : undefined,
                },
                { preserveState: true, replace: true },
            );
        }, 300);

        return () => clearTimeout(handler);
    }, [sorting]);

    const handleSearch = () => {
        router.get(
            '/registrar/invoices',
            {
                search: search || undefined,
                status: status && status !== 'all' ? status : undefined,
                from_date: fromDate ? format(fromDate, 'yyyy-MM-dd') : undefined,
                to_date: toDate ? format(toDate, 'yyyy-MM-dd') : undefined,
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
        setFromDate(undefined);
        setToDate(undefined);
        setSorting([]);
        router.get('/registrar/invoices', {}, { preserveState: true, preserveScroll: true });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Invoices" />
            <div className="container mx-auto px-4 py-6">
                <div className="mb-6 flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Invoices</h1>
                    <Link href="/registrar/invoices/create">
                        <Button>
                            <PlusCircle className="mr-2 h-4 w-4" />
                            Create Invoice
                        </Button>
                    </Link>
                </div>

                {/* Filters */}
                <Card className="mb-6 p-6">
                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <div className="space-y-2">
                            <label className="text-sm font-medium">Search</label>
                            <Input
                                placeholder="Invoice # or student..."
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
                                    <SelectItem value="draft">Draft</SelectItem>
                                    <SelectItem value="sent">Sent</SelectItem>
                                    <SelectItem value="partially_paid">Partially Paid</SelectItem>
                                    <SelectItem value="paid">Paid</SelectItem>
                                    <SelectItem value="overdue">Overdue</SelectItem>
                                    <SelectItem value="cancelled">Cancelled</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="space-y-2">
                            <label className="text-sm font-medium">From Date</label>
                            <Popover>
                                <PopoverTrigger asChild>
                                    <Button
                                        variant="outline"
                                        className={cn('w-full justify-start text-left font-normal', !fromDate && 'text-muted-foreground')}
                                    >
                                        <CalendarIcon className="mr-2 h-4 w-4" />
                                        {fromDate ? format(fromDate, 'PPP') : <span>Pick a date</span>}
                                    </Button>
                                </PopoverTrigger>
                                <PopoverContent className="w-auto p-0" align="start">
                                    <Calendar
                                        mode="single"
                                        selected={fromDate}
                                        onSelect={setFromDate}
                                        initialFocus
                                        className="rounded-md border shadow"
                                    />
                                </PopoverContent>
                            </Popover>
                        </div>
                        <div className="space-y-2">
                            <label className="text-sm font-medium">To Date</label>
                            <Popover>
                                <PopoverTrigger asChild>
                                    <Button
                                        variant="outline"
                                        className={cn('w-full justify-start text-left font-normal', !toDate && 'text-muted-foreground')}
                                    >
                                        <CalendarIcon className="mr-2 h-4 w-4" />
                                        {toDate ? format(toDate, 'PPP') : <span>Pick a date</span>}
                                    </Button>
                                </PopoverTrigger>
                                <PopoverContent className="w-auto p-0" align="start">
                                    <Calendar
                                        mode="single"
                                        selected={toDate}
                                        onSelect={setToDate}
                                        initialFocus
                                        className="rounded-md border shadow"
                                    />
                                </PopoverContent>
                            </Popover>
                        </div>
                    </div>
                    <div className="mt-6 flex gap-2">
                        <Button onClick={handleSearch} variant="secondary">
                            <Search className="mr-2 h-4 w-4" />
                            Search
                        </Button>
                        {(search || (status && status !== 'all') || fromDate || toDate) && (
                            <Button onClick={handleClearFilters} variant="outline">
                                Clear Filters
                            </Button>
                        )}
                    </div>
                </Card>

                {/* Data Table */}
                <DataTable columns={columns} data={invoices.data} sorting={sorting} onSortingChange={setSorting} />

                {/* Pagination */}
                {invoices.last_page > 1 && (
                    <div className="mt-4 flex justify-center gap-2">
                        {invoices.links.map((link, index) => (
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
