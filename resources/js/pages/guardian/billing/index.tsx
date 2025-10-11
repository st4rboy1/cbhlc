import AppLayout from '@/layouts/app-layout';
import { BillingModule } from '@/pages/guardian/billing/billing-module';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

export default function GuardianBillingIndex() {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Guardian', href: '/guardian/dashboard' },
        { title: 'Billing', href: '/guardian/billing' },
    ];
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Billing Index" />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">Billing Index</h1>
                <BillingModule />
            </div>
        </AppLayout>
    );
}
