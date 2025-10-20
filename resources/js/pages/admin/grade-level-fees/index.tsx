import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

export default function AdminGradeLevelFeesIndex() {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Admin', href: '/admin/dashboard' },
        { title: 'Grade Level Fees', href: '/admin/grade-level-fees' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Grade Level Fees" />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">Grade Level Fees</h1>
                <p className="text-muted-foreground">This page will contain a list of grade level fees.</p>
            </div>
        </AppLayout>
    );
}
