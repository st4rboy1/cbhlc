import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

interface PaymentScheduleItem {
    period: string;
    due_date: string;
    amount: string;
    status: string;
}

interface EnrollmentDetails {
    id: number;
    student_name: string;
    student_id: string;
    school_year_name: string;
    grade_level: string;
    status: string;
    payment_status: string;
}

interface BillingDetails {
    tuition_fee: string;
    miscellaneous_fee: string;
    total_amount: string;
    payment_schedule: PaymentScheduleItem[];
}

interface PaymentInstructions {
    bank_name: string;
    account_name: string;
    account_number: string;
    notes: string;
}

interface GuardianBillingShowProps {
    enrollment: EnrollmentDetails;
    billing: BillingDetails;
    paymentInstructions: PaymentInstructions;
}

export default function GuardianBillingShow({ enrollment, billing, paymentInstructions }: GuardianBillingShowProps) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Guardian', href: '/guardian/dashboard' },
        { title: 'Billing', href: '/guardian/billing' },
        { title: 'Invoice Details', href: '#' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Billing Show" />
            <div className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <h1 className="mb-6 text-3xl font-bold text-gray-900">Invoice Details</h1>

                {/* Enrollment Details */}
                <div className="mb-8 rounded-lg bg-white p-6 shadow-md">
                    <h2 className="mb-4 text-2xl font-semibold text-gray-800">Enrollment Information</h2>
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <p className="text-sm font-medium text-gray-500">Student Name:</p>
                            <p className="text-lg font-semibold text-gray-900">{enrollment.student_name}</p>
                        </div>
                        <div>
                            <p className="text-sm font-medium text-gray-500">Student ID:</p>
                            <p className="text-lg font-semibold text-gray-900">{enrollment.student_id}</p>
                        </div>
                        <div>
                            <p className="text-sm font-medium text-gray-500">School Year:</p>
                            <p className="text-lg font-semibold text-gray-900">{enrollment.school_year_name}</p>
                        </div>
                        <div>
                            <p className="text-sm font-medium text-gray-500">Grade Level:</p>
                            <p className="text-lg font-semibold text-gray-900">{enrollment.grade_level}</p>
                        </div>
                        <div>
                            <p className="text-sm font-medium text-gray-500">Enrollment Status:</p>
                            <p className="text-lg font-semibold text-gray-900">{enrollment.status}</p>
                        </div>
                        <div>
                            <p className="text-sm font-medium text-gray-500">Payment Status:</p>
                            <p className="text-lg font-semibold text-gray-900">{enrollment.payment_status}</p>
                        </div>
                    </div>
                </div>

                {/* Billing Summary */}
                <div className="mb-8 rounded-lg bg-white p-6 shadow-md">
                    <h2 className="mb-4 text-2xl font-semibold text-gray-800">Billing Summary</h2>
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <p className="text-sm font-medium text-gray-500">Tuition Fee:</p>
                            <p className="text-lg font-semibold text-gray-900">{billing.tuition_fee}</p>
                        </div>
                        <div>
                            <p className="text-sm font-medium text-gray-500">Miscellaneous Fee:</p>
                            <p className="text-lg font-semibold text-gray-900">{billing.miscellaneous_fee}</p>
                        </div>
                        <div>
                            <p className="text-sm font-medium text-gray-500">Total Amount:</p>
                            <p className="text-xl font-bold text-indigo-600">{billing.total_amount}</p>
                        </div>
                    </div>
                </div>

                {/* Payment Schedule */}
                <div className="mb-8 rounded-lg bg-white p-6 shadow-md">
                    <h2 className="mb-4 text-2xl font-semibold text-gray-800">Payment Schedule</h2>
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th scope="col" className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">
                                        Period
                                    </th>
                                    <th scope="col" className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">
                                        Due Date
                                    </th>
                                    <th scope="col" className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">
                                        Amount
                                    </th>
                                    <th scope="col" className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">
                                        Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200 bg-white">
                                {billing.payment_schedule.map((item, index) => (
                                    <tr key={index}>
                                        <td className="px-6 py-4 text-sm font-medium whitespace-nowrap text-gray-900">{item.period}</td>
                                        <td className="px-6 py-4 text-sm whitespace-nowrap text-gray-500">{item.due_date}</td>
                                        <td className="px-6 py-4 text-sm whitespace-nowrap text-gray-500">{item.amount}</td>
                                        <td className="px-6 py-4 text-sm whitespace-nowrap text-gray-500">
                                            <span
                                                className={`inline-flex rounded-full px-2 text-xs leading-5 font-semibold ${
                                                    item.status === 'pending'
                                                        ? 'bg-yellow-100 text-yellow-800'
                                                        : item.status === 'paid'
                                                          ? 'bg-green-100 text-green-800'
                                                          : 'bg-gray-100 text-gray-800'
                                                }`}
                                            >
                                                {item.status}
                                            </span>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>

                {/* Payment Instructions */}
                <div className="rounded-lg bg-white p-6 shadow-md">
                    <h2 className="mb-4 text-2xl font-semibold text-gray-800">Payment Instructions</h2>
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <p className="text-sm font-medium text-gray-500">Bank Name:</p>
                            <p className="text-lg font-semibold text-gray-900">{paymentInstructions.bank_name}</p>
                        </div>
                        <div>
                            <p className="text-sm font-medium text-gray-500">Account Name:</p>
                            <p className="text-lg font-semibold text-gray-900">{paymentInstructions.account_name}</p>
                        </div>
                        <div>
                            <p className="text-sm font-medium text-gray-500">Account Number:</p>
                            <p className="text-lg font-semibold text-gray-900">{paymentInstructions.account_number}</p>
                        </div>
                        <div className="sm:col-span-2">
                            <p className="text-sm font-medium text-gray-500">Notes:</p>
                            <p className="text-lg font-semibold text-gray-900">{paymentInstructions.notes}</p>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
