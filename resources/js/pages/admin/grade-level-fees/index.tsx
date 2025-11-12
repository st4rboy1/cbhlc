import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { GradeLevelFeesTable } from '@/pages/admin/grade-level-fees/grade-level-fees-table';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { AlertCircle, CheckCircle2, PlusCircle } from 'lucide-react';

export type GradeLevelFee = {
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
};

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
        school_year_id?: string;
        active?: string;
    };
    gradeLevels: string[];
    schoolYears: SchoolYear[];
}

export default function AdminGradeLevelFeesIndex({ fees, filters, gradeLevels, schoolYears }: Props) {
    const { flash } = usePage<{ flash: { success?: string; error?: string } }>().props;

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Administrator', href: '/admin/dashboard' },
        { title: 'Grade Level Fees', href: '/admin/grade-level-fees' },
    ];

    const formattedFees = fees.data.map((fee) => ({
        id: fee.id,
        gradeLevel: fee.grade_level,
        schoolYear: fee.school_year,
        tuitionFee: fee.tuition_fee,
        miscellaneousFee: fee.miscellaneous_fee,
        otherFees: fee.other_fees,
        totalAmount: fee.total_amount,
        paymentTerms: fee.payment_terms,
        isActive: fee.is_active,
    }));

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Grade Level Fees" />
            <div className="px-4 py-6">
                {flash?.success && (
                    <Alert className="mb-4 border-green-200 bg-green-50 text-green-900">
                        <CheckCircle2 className="h-4 w-4 text-green-600" />
                        <AlertDescription>{flash.success}</AlertDescription>
                    </Alert>
                )}
                {flash?.error && (
                    <Alert variant="destructive" className="mb-4">
                        <AlertCircle className="h-4 w-4" />
                        <AlertDescription>{flash.error}</AlertDescription>
                    </Alert>
                )}
                <div className="mb-4 flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Grade Level Fees</h1>
                    <Link href="/admin/grade-level-fees/create">
                        <Button>
                            <PlusCircle className="mr-2 h-4 w-4" />
                            Add New Fee
                        </Button>
                    </Link>
                </div>
                <GradeLevelFeesTable
                    fees={formattedFees}
                    filters={{
                        search: filters.search || null,
                        school_year_id: filters.school_year_id || null,
                        active: filters.active || null,
                    }}
                    gradeLevels={gradeLevels}
                    schoolYears={schoolYears}
                />
            </div>
        </AppLayout>
    );
}
