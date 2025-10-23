import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { formatCurrency } from '@/lib/utils';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { Copy, Edit, PlusCircle, Search, Trash } from 'lucide-react';
import { useState } from 'react';

interface GradeLevelFee {
    id: number;
    grade_level: string;
    school_year: string;
    tuition_fee: number;
    miscellaneous_fee: number;
    other_fees: number;
    total_amount: number;
    payment_terms: string;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface SchoolYear {
    id: number;
    name: string;
    status: string;
    is_active: boolean;
}

interface Props {
    fees: {
        data: GradeLevelFee[];
        links: PaginationLink[];
        current_page: number;
        last_page: number;
        total: number;
    };
    filters: {
        search?: string;
        school_year?: string;
        active?: string;
    };
    schoolYears: SchoolYear[];
}

export default function RegistrarGradeLevelFeesIndex({ fees, filters, schoolYears }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const [schoolYear, setSchoolYear] = useState(filters.school_year || '');
    const [activeFilter, setActiveFilter] = useState(filters.active || '');

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Registrar', href: '/registrar/dashboard' },
        { title: 'Grade Level Fees', href: '/registrar/grade-level-fees' },
    ];

    const handleSearch = () => {
        router.get(
            '/registrar/grade-level-fees',
            {
                search: search || undefined,
                school_year: schoolYear || undefined,
                active: activeFilter || undefined,
            },
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    };

    const handleDelete = (id: number) => {
        if (confirm('Are you sure you want to delete this fee structure?')) {
            router.delete(`/registrar/grade-level-fees/${id}`);
        }
    };

    const handleDuplicate = (id: number) => {
        const newSchoolYear = prompt('Enter the school year to duplicate to (e.g., 2025-2026):');
        if (newSchoolYear) {
            router.post(`/registrar/grade-level-fees/${id}/duplicate`, {
                school_year: newSchoolYear,
            });
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Grade Level Fees" />
            <div className="container mx-auto px-4 py-6">
                <div className="mb-6 flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Grade Level Fees</h1>
                    <Link href="/registrar/grade-level-fees/create">
                        <Button>
                            <PlusCircle className="mr-2 h-4 w-4" />
                            Add New Fee
                        </Button>
                    </Link>
                </div>

                <div className="mb-6 grid gap-4 md:grid-cols-4">
                    <Input
                        placeholder="Search grade level..."
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
                    />
                    <Select value={schoolYear || 'all'} onValueChange={(value) => setSchoolYear(value === 'all' ? '' : value)}>
                        <SelectTrigger>
                            <SelectValue placeholder="Filter by school year" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All School Years</SelectItem>
                            {schoolYears.map((sy) => (
                                <SelectItem key={sy.id} value={sy.name}>
                                    {sy.name}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <Select value={activeFilter || 'all'} onValueChange={(value) => setActiveFilter(value === 'all' ? '' : value)}>
                        <SelectTrigger>
                            <SelectValue placeholder="Filter by status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All</SelectItem>
                            <SelectItem value="true">Active</SelectItem>
                            <SelectItem value="false">Inactive</SelectItem>
                        </SelectContent>
                    </Select>
                    <Button onClick={handleSearch} variant="secondary">
                        <Search className="mr-2 h-4 w-4" />
                        Search
                    </Button>
                </div>

                <div className="rounded-lg border bg-white shadow">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Grade Level</TableHead>
                                <TableHead>School Year</TableHead>
                                <TableHead>Tuition Fee</TableHead>
                                <TableHead>Misc. Fee</TableHead>
                                <TableHead>Other Fees</TableHead>
                                <TableHead>Total</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead className="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {fees.data.length === 0 ? (
                                <TableRow>
                                    <TableCell colSpan={8} className="text-center text-gray-500">
                                        No grade level fees found.
                                    </TableCell>
                                </TableRow>
                            ) : (
                                fees.data.map((fee) => (
                                    <TableRow key={fee.id}>
                                        <TableCell className="font-medium">{fee.grade_level}</TableCell>
                                        <TableCell>{fee.school_year}</TableCell>
                                        <TableCell>{formatCurrency(fee.tuition_fee)}</TableCell>
                                        <TableCell>{formatCurrency(fee.miscellaneous_fee)}</TableCell>
                                        <TableCell>{formatCurrency(fee.other_fees)}</TableCell>
                                        <TableCell className="font-semibold">{formatCurrency(fee.total_amount)}</TableCell>
                                        <TableCell>
                                            <Badge variant={fee.is_active ? 'default' : 'secondary'}>{fee.is_active ? 'Active' : 'Inactive'}</Badge>
                                        </TableCell>
                                        <TableCell className="text-right">
                                            <div className="flex justify-end gap-2">
                                                <Link href={`/registrar/grade-level-fees/${fee.id}/edit`}>
                                                    <Button size="sm" variant="outline">
                                                        <Edit className="h-4 w-4" />
                                                    </Button>
                                                </Link>
                                                <Button size="sm" variant="outline" onClick={() => handleDuplicate(fee.id)}>
                                                    <Copy className="h-4 w-4" />
                                                </Button>
                                                <Button size="sm" variant="destructive" onClick={() => handleDelete(fee.id)}>
                                                    <Trash className="h-4 w-4" />
                                                </Button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>
                </div>

                {fees.last_page > 1 && (
                    <div className="mt-4 flex justify-center gap-2">
                        {fees.links.map((link, index) => (
                            <Link
                                key={index}
                                href={link.url || '#'}
                                preserveState
                                preserveScroll
                                className={`rounded px-3 py-1 ${
                                    link.active
                                        ? 'bg-primary text-white'
                                        : link.url
                                          ? 'bg-gray-200 hover:bg-gray-300'
                                          : 'cursor-not-allowed bg-gray-100 text-gray-400'
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
