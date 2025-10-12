import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { AlertCircle, DollarSign, MapPin, User } from 'lucide-react';

interface Student {
    id: number;
    student_id: string;
    first_name: string;
    middle_name?: string;
    last_name: string;
    birthdate: string;
    gender: string;
    age: number;
    grade_level: string;
    section?: string;
}

interface Enrollment {
    id: number;
    enrollment_id: string;
    student: Student;
    school_year: string;
    semester?: string;
    status: string;
    tuition_fee: number;
    miscellaneous_fee: number;
    laboratory_fee: number;
    library_fee: number;
    sports_fee: number;
    total_amount: number;
    discount: number;
    net_amount: number;
    payment_status: 'pending' | 'partial' | 'paid';
    amount_paid: number;
    balance: number;
    payment_due_date?: string;
}

interface PaginatedData {
    data: Enrollment[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface GradeLevelFee {
    tuition: number;
    miscellaneous: number;
}

interface Props {
    enrollments: PaginatedData | { data: [] };
    gradeLevelFees: Record<string, GradeLevelFee>;
    settings: {
        payment_location: string;
        payment_hours: string;
        payment_methods: string;
        payment_note: string;
    };
}

export default function Tuition({ enrollments, gradeLevelFees, settings }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Tuition',
            href: '/tuition',
        },
    ];

    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('en-PH', {
            style: 'currency',
            currency: 'PHP',
        }).format(amount);
    };

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });
    };

    const getPaymentStatusBadge = (status: string) => {
        switch (status) {
            case 'paid':
                return <Badge className="bg-green-100 text-green-800 hover:bg-green-200">PAID</Badge>;
            case 'partial':
                return <Badge className="bg-yellow-100 text-yellow-800 hover:bg-yellow-200">PARTIAL PAYMENT</Badge>;
            default:
                return <Badge className="bg-red-100 text-red-800 hover:bg-red-200">PENDING</Badge>;
        }
    };

    const hasEnrollments = enrollments.data && enrollments.data.length > 0;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Tuition" />

            <div className="px-4 py-6">
                <Heading title="Tuition Information" description="View your tuition fees and payment details" />
                {!hasEnrollments ? (
                    <Card className="mb-6 border-yellow-200 bg-yellow-50">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-yellow-800">
                                <AlertCircle className="h-5 w-5" />
                                No Enrollments Found
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p className="text-yellow-700">No enrollment records found. Please complete the enrollment process first.</p>
                            <Link href="/enrollment" className="mt-4 inline-block text-primary hover:underline">
                                Go to Enrollment →
                            </Link>
                        </CardContent>
                    </Card>
                ) : (
                    <>
                        {enrollments.data.map((enrollment) => (
                            <div key={enrollment.id} className="mb-6 space-y-6">
                                {/* Payment Status Alert */}
                                <Card
                                    className={
                                        enrollment.payment_status === 'paid'
                                            ? 'border-green-200 bg-green-50'
                                            : enrollment.payment_status === 'partial'
                                              ? 'border-yellow-200 bg-yellow-50'
                                              : 'border-red-200 bg-red-50'
                                    }
                                >
                                    <CardHeader>
                                        <CardTitle className="flex items-center justify-between">
                                            <span className="flex items-center gap-2 text-gray-800">
                                                <DollarSign className="h-5 w-5" />
                                                Payment Status - {enrollment.enrollment_id}
                                            </span>
                                            {getPaymentStatusBadge(enrollment.payment_status)}
                                        </CardTitle>
                                    </CardHeader>
                                    <CardContent>
                                        <div className="space-y-2">
                                            {enrollment.payment_status === 'pending' && (
                                                <>
                                                    <p className="text-red-700">Amount Due: {formatCurrency(enrollment.balance)}</p>
                                                    <p className="text-red-700">Visit cashier on-site to finish the payment process.</p>
                                                </>
                                            )}
                                            {enrollment.payment_status === 'partial' && (
                                                <>
                                                    <p className="text-yellow-700">Amount Paid: {formatCurrency(enrollment.amount_paid)}</p>
                                                    <p className="text-yellow-700">Balance: {formatCurrency(enrollment.balance)}</p>
                                                    <p className="text-yellow-700">Visit cashier on-site to pay remaining balance.</p>
                                                </>
                                            )}
                                            {enrollment.payment_status === 'paid' && <p className="text-green-700">Tuition fully paid. Thank you!</p>}
                                            {enrollment.payment_due_date && (
                                                <p className="text-sm text-gray-600">Due Date: {formatDate(enrollment.payment_due_date)}</p>
                                            )}
                                        </div>
                                    </CardContent>
                                </Card>

                                <div className="grid gap-6 md:grid-cols-2">
                                    {/* Student Information */}
                                    <Card>
                                        <CardHeader>
                                            <CardTitle className="flex items-center gap-2">
                                                <User className="h-5 w-5 text-primary" />
                                                Student Information
                                            </CardTitle>
                                        </CardHeader>
                                        <CardContent className="space-y-4">
                                            <div className="grid gap-3">
                                                <div className="flex justify-between">
                                                    <span className="font-medium text-muted-foreground">Student Name:</span>
                                                    <span className="font-semibold">
                                                        {enrollment.student.first_name} {enrollment.student.middle_name || ''}{' '}
                                                        {enrollment.student.last_name}
                                                    </span>
                                                </div>
                                                <Separator />
                                                <div className="flex justify-between">
                                                    <span className="font-medium text-muted-foreground">Student ID:</span>
                                                    <span className="font-semibold">{enrollment.student.student_id}</span>
                                                </div>
                                                <Separator />
                                                <div className="flex justify-between">
                                                    <span className="font-medium text-muted-foreground">Age:</span>
                                                    <span className="font-semibold">{enrollment.student.age} years old</span>
                                                </div>
                                                <Separator />
                                                <div className="flex justify-between">
                                                    <span className="font-medium text-muted-foreground">Gender:</span>
                                                    <span className="font-semibold">{enrollment.student.gender}</span>
                                                </div>
                                                <Separator />
                                                <div className="flex justify-between">
                                                    <span className="font-medium text-muted-foreground">Grade Level:</span>
                                                    <span className="font-semibold">{enrollment.student.grade_level}</span>
                                                </div>
                                                {enrollment.student.section && (
                                                    <>
                                                        <Separator />
                                                        <div className="flex justify-between">
                                                            <span className="font-medium text-muted-foreground">Section:</span>
                                                            <span className="font-semibold">{enrollment.student.section}</span>
                                                        </div>
                                                    </>
                                                )}
                                                <Separator />
                                                <div className="flex justify-between">
                                                    <span className="font-medium text-muted-foreground">School Year:</span>
                                                    <span className="font-semibold">{enrollment.school_year}</span>
                                                </div>
                                            </div>
                                        </CardContent>
                                    </Card>

                                    {/* Tuition Breakdown */}
                                    <Card>
                                        <CardHeader>
                                            <CardTitle className="flex items-center gap-2">
                                                <DollarSign className="h-5 w-5 text-primary" />
                                                Tuition Breakdown
                                            </CardTitle>
                                        </CardHeader>
                                        <CardContent className="space-y-4">
                                            <div className="space-y-3">
                                                <div className="flex items-center justify-between">
                                                    <span className="text-sm font-medium text-muted-foreground">Tuition Fee</span>
                                                    <span className="text-right font-semibold">{formatCurrency(enrollment.tuition_fee)}</span>
                                                </div>
                                                <Separator />
                                                <div className="flex items-center justify-between">
                                                    <span className="text-sm font-medium text-muted-foreground">Miscellaneous Fee</span>
                                                    <span className="text-right font-semibold">{formatCurrency(enrollment.miscellaneous_fee)}</span>
                                                </div>
                                                {enrollment.laboratory_fee > 0 && (
                                                    <>
                                                        <Separator />
                                                        <div className="flex items-center justify-between">
                                                            <span className="text-sm font-medium text-muted-foreground">Laboratory Fee</span>
                                                            <span className="text-right font-semibold">
                                                                {formatCurrency(enrollment.laboratory_fee)}
                                                            </span>
                                                        </div>
                                                    </>
                                                )}
                                                {enrollment.library_fee > 0 && (
                                                    <>
                                                        <Separator />
                                                        <div className="flex items-center justify-between">
                                                            <span className="text-sm font-medium text-muted-foreground">Library Fee</span>
                                                            <span className="text-right font-semibold">{formatCurrency(enrollment.library_fee)}</span>
                                                        </div>
                                                    </>
                                                )}
                                                {enrollment.sports_fee > 0 && (
                                                    <>
                                                        <Separator />
                                                        <div className="flex items-center justify-between">
                                                            <span className="text-sm font-medium text-muted-foreground">Sports Fee</span>
                                                            <span className="text-right font-semibold">{formatCurrency(enrollment.sports_fee)}</span>
                                                        </div>
                                                    </>
                                                )}
                                            </div>

                                            <Separator className="my-4" />

                                            <div className="space-y-2">
                                                <div className="flex items-center justify-between">
                                                    <span className="font-semibold">Subtotal:</span>
                                                    <span className="font-semibold">{formatCurrency(enrollment.total_amount)}</span>
                                                </div>
                                                {enrollment.discount > 0 && (
                                                    <div className="flex items-center justify-between text-green-600">
                                                        <span className="font-semibold">Discount:</span>
                                                        <span className="font-semibold">-{formatCurrency(enrollment.discount)}</span>
                                                    </div>
                                                )}
                                            </div>

                                            <div className="flex items-center justify-between rounded-lg bg-primary/5 p-3">
                                                <span className="text-lg font-bold">Net Amount:</span>
                                                <span className="text-lg font-bold text-primary">{formatCurrency(enrollment.net_amount)}</span>
                                            </div>

                                            {enrollment.amount_paid > 0 && (
                                                <div className="space-y-2 border-t pt-3">
                                                    <div className="flex items-center justify-between text-green-600">
                                                        <span className="font-semibold">Amount Paid:</span>
                                                        <span className="font-semibold">{formatCurrency(enrollment.amount_paid)}</span>
                                                    </div>
                                                    <div className="flex items-center justify-between">
                                                        <span className="font-bold">Balance:</span>
                                                        <span className="font-bold text-red-600">{formatCurrency(enrollment.balance)}</span>
                                                    </div>
                                                </div>
                                            )}
                                        </CardContent>
                                    </Card>
                                </div>

                                {/* View Invoice Link */}
                                <div className="text-center">
                                    <Link
                                        href={`/invoice/${enrollment.id}`}
                                        className="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-white hover:bg-primary/90"
                                    >
                                        View Invoice
                                    </Link>
                                </div>
                            </div>
                        ))}
                    </>
                )}

                {/* Grade Level Fee Reference */}
                {gradeLevelFees && Object.keys(gradeLevelFees).length > 0 && (
                    <Card className="mt-6">
                        <CardHeader>
                            <CardTitle>Grade Level Fees Reference</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid gap-2 md:grid-cols-3">
                                {Object.entries(gradeLevelFees).map(([level, fees]) => (
                                    <div key={level} className="rounded-lg border p-3">
                                        <p className="font-semibold">{level}</p>
                                        <p className="text-sm text-muted-foreground">Tuition: {formatCurrency(fees.tuition)}</p>
                                        <p className="text-sm text-muted-foreground">Miscellaneous: {formatCurrency(fees.miscellaneous)}</p>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Payment Instructions */}
                <Card className="mt-6">
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <MapPin className="h-5 w-5 text-primary" />
                            Payment Instructions
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-3 text-muted-foreground">
                            <p>
                                <strong className="text-foreground">Payment Location:</strong> {settings.payment_location}
                            </p>
                            <p>
                                <strong className="text-foreground">Business Hours:</strong> {settings.payment_hours}
                            </p>
                            <p>
                                <strong className="text-foreground">Payment Methods:</strong> {settings.payment_methods}
                            </p>
                            <p>
                                <strong className="text-foreground">Note:</strong> {settings.payment_note}
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
