import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { GradeLevelFeesTable } from '@/pages/registrar/grade-level-fees/grade-level-fees-table';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { PlusCircle } from 'lucide-react';

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
    gradeLevels: string[];
    schoolYears: SchoolYear[];
}

export default function RegistrarGradeLevelFeesIndex({ fees, filters, gradeLevels, schoolYears }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Registrar', href: '/registrar/dashboard' },
        { title: 'Grade Level Fees', href: '/registrar/grade-level-fees' },
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
                <div className="mb-4 flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Grade Level Fees</h1>
                    <Link href="/registrar/grade-level-fees/create">
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
                        school_year: filters.school_year || null,
                        active: filters.active || null,
                    }}
                    gradeLevels={gradeLevels}
                    schoolYears={schoolYears}
                />
            </div>
        </AppLayout>
    );
}
