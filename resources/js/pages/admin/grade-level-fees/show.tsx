import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { formatCurrency } from '@/lib/utils';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Edit } from 'lucide-react';

interface GradeLevelFee {
    id: number;
    grade_level: string;
    school_year: string;
    tuition_fee: number;
    miscellaneous_fee: number;
    other_fees: number;
    total_amount: number;
    payment_terms: string;
    notes?: string;
    is_active: boolean;
    created_at: string;
    updated_at: string;
    created_by?: {
        id: number;
        name: string;
    };
    updated_by?: {
        id: number;
        name: string;
    };
}

interface Props {
    fee: GradeLevelFee;
}

export default function AdminGradeLevelFeesShow({ fee }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Administrator', href: '/admin/dashboard' },
        { title: 'Grade Level Fees', href: '/admin/grade-level-fees' },
        { title: 'View', href: '#' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="View Grade Level Fee" />
            <div className="container mx-auto max-w-3xl px-4 py-6">
                <div className="mb-6 flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Grade Level Fee Details</h1>
                    <div className="flex gap-2">
                        <Link href="/admin/grade-level-fees">
                            <Button variant="outline">
                                <ArrowLeft className="mr-2 h-4 w-4" />
                                Back to List
                            </Button>
                        </Link>
                        <Link href={`/admin/grade-level-fees/${fee.id}/edit`}>
                            <Button>
                                <Edit className="mr-2 h-4 w-4" />
                                Edit
                            </Button>
                        </Link>
                    </div>
                </div>

                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div>
                                <CardTitle>{fee.grade_level}</CardTitle>
                                <CardDescription>School Year {fee.school_year}</CardDescription>
                            </div>
                            <Badge variant={fee.is_active ? 'default' : 'secondary'} className="px-3 py-1 text-lg">
                                {fee.is_active ? 'Active' : 'Inactive'}
                            </Badge>
                        </div>
                    </CardHeader>
                    <CardContent className="space-y-6">
                        <div className="grid gap-4 md:grid-cols-2">
                            <div>
                                <h3 className="mb-2 font-semibold">Fee Breakdown</h3>
                                <dl className="space-y-1 text-sm">
                                    <div className="flex justify-between">
                                        <dt className="text-gray-600">Tuition Fee:</dt>
                                        <dd className="font-medium">{formatCurrency(fee.tuition_fee)}</dd>
                                    </div>
                                    <div className="flex justify-between">
                                        <dt className="text-gray-600">Miscellaneous Fee:</dt>
                                        <dd className="font-medium">{formatCurrency(fee.miscellaneous_fee)}</dd>
                                    </div>
                                    <div className="flex justify-between">
                                        <dt className="text-gray-600">Other Fees:</dt>
                                        <dd className="font-medium">{formatCurrency(fee.other_fees)}</dd>
                                    </div>
                                    <div className="flex justify-between border-t pt-1">
                                        <dt className="font-semibold">Total Amount:</dt>
                                        <dd className="text-lg font-semibold">{formatCurrency(fee.total_amount)}</dd>
                                    </div>
                                </dl>
                            </div>

                            <div>
                                <h3 className="mb-2 font-semibold">Payment Information</h3>
                                <dl className="space-y-1 text-sm">
                                    <div className="flex justify-between">
                                        <dt className="text-gray-600">Payment Terms:</dt>
                                        <dd className="font-medium capitalize">{fee.payment_terms.toLowerCase()}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        {fee.notes && (
                            <div>
                                <h3 className="mb-2 font-semibold">Notes</h3>
                                <p className="text-sm text-gray-600">{fee.notes}</p>
                            </div>
                        )}

                        <div className="border-t pt-4">
                            <h3 className="mb-2 font-semibold">Audit Information</h3>
                            <dl className="grid gap-2 text-sm md:grid-cols-2">
                                <div>
                                    <dt className="text-gray-600">Created:</dt>
                                    <dd className="font-medium">
                                        {new Date(fee.created_at).toLocaleDateString('en-US', {
                                            year: 'numeric',
                                            month: 'long',
                                            day: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit',
                                        })}
                                        {fee.created_by && ` by ${fee.created_by.name}`}
                                    </dd>
                                </div>
                                <div>
                                    <dt className="text-gray-600">Last Updated:</dt>
                                    <dd className="font-medium">
                                        {new Date(fee.updated_at).toLocaleDateString('en-US', {
                                            year: 'numeric',
                                            month: 'long',
                                            day: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit',
                                        })}
                                        {fee.updated_by && ` by ${fee.updated_by.name}`}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
