import AppLayout from '@/layouts/app-layout';
import { BillingModule } from '@/pages/guardian/billing/billing-module';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

interface Enrollment {
    id: number;
    student_name: string;
    student_id: string;
    school_year_name: string;
    grade_level: string;
    status: string;
    payment_status: string;
    tuition_fee: string;
    miscellaneous_fee: string;
    total_amount: string;
    raw_total: number;
}

interface Summary {
    total_due: string;
    total_paid: string;
    pending_count: number;
    overdue_count: number;
}

interface PaymentPlan {
    name: string;
    description: string;
    discount: string;
}

interface GuardianBillingIndexProps {
    enrollments: Enrollment[];
    summary: Summary;
    paymentPlans: PaymentPlan[];
}

export default function GuardianBillingIndex({ enrollments, summary, paymentPlans }: GuardianBillingIndexProps) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Guardian', href: '/guardian/dashboard' },
        { title: 'Billing', href: '/guardian/billing' },
    ];
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Billing Index" />
            <div className="px-4 py-6">
                <BillingModule enrollments={enrollments} summary={summary} paymentPlans={paymentPlans} />
            </div>
        </AppLayout>
    );
}
