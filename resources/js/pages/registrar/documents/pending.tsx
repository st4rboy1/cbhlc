import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

export default function RegistrarPendingDocuments() {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Registrar', href: '/registrar/dashboard' },
        { title: 'Documents', href: '/registrar/documents/pending' },
        { title: 'Pending', href: '/registrar/documents/pending' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Pending Documents" />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">Pending Documents</h1>
                <p className="text-muted-foreground">This page will contain a list of pending documents.</p>
            </div>
        </AppLayout>
    );
}
