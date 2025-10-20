import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

export default function AdminSchoolInformationIndex() {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Admin', href: '/admin/dashboard' },
        { title: 'School Information', href: '/admin/school-information' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="School Information" />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">School Information</h1>
                <p className="text-muted-foreground">This page will contain school information.</p>
            </div>
        </AppLayout>
    );
}
