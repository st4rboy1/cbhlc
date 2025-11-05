import Heading from '@/components/heading';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { CalendarDays, DollarSign, MapPin } from 'lucide-react';
import { useState } from 'react';

interface GradeLevelFeeDetail {
    tuition: number;
    miscellaneous: number;
    laboratory: number;
    library: number;
    sports: number;
    total: number;
    payment_plans: Record<
        string,
        {
            label: string;
            installments: number;
            amount_per_installment: number;
        }
    >;
}

interface PaymentPlanDetail {
    value: string;
    label: string;
    installments: number;
    description: string;
}

interface Props {
    gradeLevelFees: Record<string, GradeLevelFeeDetail>;
    settings: {
        payment_location: string;
        payment_hours: string;
        payment_methods: string;
        payment_note: string;
    };
    paymentPlans: PaymentPlanDetail[];
}

export default function Tuition({ gradeLevelFees, settings, paymentPlans }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Tuition',
            href: '/tuition',
        },
    ];

    const [selectedPaymentPlan, setSelectedPaymentPlan] = useState<string>(paymentPlans[0]?.value || '');

    const parseCurrency = (amount: number) => amount || 0;

    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('en-PH', {
            style: 'currency',
            currency: 'PHP',
        }).format(amount);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Tuition" />

            <div className="px-4 py-6">
                <Heading title="Tuition Information" description="View grade level fees and payment instructions" />

                {/* Grade Level Fee Reference */}
                {gradeLevelFees && Object.keys(gradeLevelFees).length > 0 ? (
                    <Card className="mt-6">
                        <CardHeader>
                            <CardTitle>Grade Level Fees Reference</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="mb-4 flex items-center gap-2">
                                <span className="text-sm font-medium">View by Payment Plan:</span>
                                <Select value={selectedPaymentPlan} onValueChange={setSelectedPaymentPlan}>
                                    <SelectTrigger className="w-[180px]">
                                        <SelectValue placeholder="Select a plan" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {paymentPlans.map((plan) => (
                                            <SelectItem key={plan.value} value={plan.value}>
                                                {plan.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                                {Object.entries(gradeLevelFees).map(([level, fees]) => {
                                    const planDetails = fees.payment_plans[selectedPaymentPlan];
                                    return (
                                        <Card key={level} className="p-4">
                                            <CardTitle className="mb-2 text-lg">{level}</CardTitle>
                                            <Separator className="my-2" />
                                            <div className="space-y-1 text-sm">
                                                <div className="flex justify-between">
                                                    <span>Tuition Fee:</span>
                                                    <span className="font-medium">{formatCurrency(parseCurrency(fees.tuition))}</span>
                                                </div>
                                                <div className="flex justify-between">
                                                    <span>Miscellaneous Fee:</span>
                                                    <span className="font-medium">{formatCurrency(parseCurrency(fees.miscellaneous))}</span>
                                                </div>
                                                {parseCurrency(fees.laboratory) > 0 && (
                                                    <div className="flex justify-between">
                                                        <span>Laboratory Fee:</span>
                                                        <span className="font-medium">{formatCurrency(parseCurrency(fees.laboratory))}</span>
                                                    </div>
                                                )}
                                                {parseCurrency(fees.library) > 0 && (
                                                    <div className="flex justify-between">
                                                        <span>Library Fee:</span>
                                                        <span className="font-medium">{formatCurrency(parseCurrency(fees.library))}</span>
                                                    </div>
                                                )}
                                                {parseCurrency(fees.sports) > 0 && (
                                                    <div className="flex justify-between">
                                                        <span>Sports Fee:</span>
                                                        <span className="font-medium">{formatCurrency(parseCurrency(fees.sports))}</span>
                                                    </div>
                                                )}
                                                <Separator className="my-2" />
                                                <div className="flex justify-between font-semibold">
                                                    <span>Total per {planDetails?.label || 'Installment'}:</span>
                                                    <span>{formatCurrency(parseCurrency(planDetails?.amount_per_installment || 0))}</span>
                                                </div>
                                                {planDetails && planDetails.installments > 1 && (
                                                    <p className="text-right text-xs text-muted-foreground">
                                                        ({planDetails.installments} installments)
                                                    </p>
                                                )}
                                            </div>
                                        </Card>
                                    );
                                })}
                            </div>
                        </CardContent>
                    </Card>
                ) : (
                    <Card className="mt-6 border-yellow-200 bg-yellow-50">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-yellow-800">
                                <DollarSign className="h-5 w-5" />
                                No Grade Level Fees Configured
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p className="text-yellow-700">There are no grade level fees configured for the current school year.</p>
                        </CardContent>
                    </Card>
                )}

                {/* Payment Plans */}
                {paymentPlans && paymentPlans.length > 0 && (
                    <Card className="mt-6">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <CalendarDays className="h-5 w-5" />
                                Available Payment Plans
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                                {paymentPlans.map((plan) => (
                                    <Card key={plan.value} className="p-4">
                                        <CardTitle className="mb-2 text-lg">{plan.label}</CardTitle>
                                        <Separator className="my-2" />
                                        <div className="space-y-1 text-sm">
                                            <p>{plan.description}</p>
                                            <p className="font-medium">Installments: {plan.installments}</p>
                                        </div>
                                    </Card>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Payment Instructions */}
                <Card className="mt-6">
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <MapPin className="h-5 w-5" />
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
