import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Link } from '@inertiajs/react';
import { AlertCircle, CheckCircle, ChevronDown, ChevronUp, Clock, DollarSign, Search } from 'lucide-react';
import { useState } from 'react';

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

interface BillingModuleProps {
    enrollments: Enrollment[];
    summary: Summary;
    paymentPlans: PaymentPlan[];
}

function getPaymentStatusVariant(status: string): 'default' | 'secondary' | 'outline' | 'destructive' {
    switch (status) {
        case 'paid':
            return 'default';
        case 'partial':
            return 'secondary';
        case 'pending':
            return 'outline';
        case 'overdue':
            return 'destructive';
        default:
            return 'outline';
    }
}

function getEnrollmentStatusVariant(status: string): 'default' | 'secondary' | 'outline' | 'destructive' {
    switch (status) {
        case 'completed':
            return 'default';
        case 'enrolled':
            return 'secondary';
        case 'pending':
            return 'outline';
        case 'rejected':
            return 'destructive';
        default:
            return 'outline';
    }
}

function formatStatusName(status: string) {
    return status.charAt(0).toUpperCase() + status.slice(1);
}

export function BillingModule({ enrollments, summary, paymentPlans }: BillingModuleProps) {
    const [searchQuery, setSearchQuery] = useState('');
    const [expandedEnrollment, setExpandedEnrollment] = useState<number | null>(null);
    const [filterStatus, setFilterStatus] = useState<string>('all');

    const filteredEnrollments = enrollments.filter((enrollment) => {
        const matchesSearch =
            enrollment.student_name.toLowerCase().includes(searchQuery.toLowerCase()) ||
            enrollment.student_id.toLowerCase().includes(searchQuery.toLowerCase()) ||
            enrollment.school_year_name.toLowerCase().includes(searchQuery.toLowerCase());

        const matchesFilter = filterStatus === 'all' || enrollment.payment_status === filterStatus;

        return matchesSearch && matchesFilter;
    });

    const toggleExpand = (enrollmentId: number) => {
        setExpandedEnrollment(expandedEnrollment === enrollmentId ? null : enrollmentId);
    };

    return (
        <div className="min-h-screen bg-muted/20 p-6 md:p-12">
            <div className="mx-auto max-w-7xl space-y-8">
                {/* Header */}
                <div className="space-y-2">
                    <h1 className="text-3xl font-semibold text-foreground">Billing & Payments</h1>
                </div>

                {/* Summary Cards */}
                <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card className="bg-card p-6">
                        <div className="flex items-start justify-between">
                            <div className="space-y-1">
                                <p className="text-sm text-muted-foreground">Total Due</p>
                                <p className="text-2xl font-semibold text-foreground">{summary.total_due}</p>
                            </div>
                            <div className="rounded-lg bg-destructive/10 p-2">
                                <DollarSign className="h-5 w-5 text-destructive" />
                            </div>
                        </div>
                    </Card>

                    <Card className="bg-card p-6">
                        <div className="flex items-start justify-between">
                            <div className="space-y-1">
                                <p className="text-sm text-muted-foreground">Total Paid</p>
                                <p className="text-2xl font-semibold text-foreground">{summary.total_paid}</p>
                            </div>
                            <div className="rounded-lg bg-primary/10 p-2">
                                <CheckCircle className="h-5 w-5 text-primary" />
                            </div>
                        </div>
                    </Card>

                    <Card className="bg-card p-6">
                        <div className="flex items-start justify-between">
                            <div className="space-y-1">
                                <p className="text-sm text-muted-foreground">Pending Payments</p>
                                <p className="text-2xl font-semibold text-foreground">{summary.pending_count}</p>
                            </div>
                            <div className="rounded-lg bg-secondary/10 p-2">
                                <Clock className="h-5 w-5 text-secondary-foreground" />
                            </div>
                        </div>
                    </Card>

                    <Card className="bg-card p-6">
                        <div className="flex items-start justify-between">
                            <div className="space-y-1">
                                <p className="text-sm text-muted-foreground">Overdue</p>
                                <p className="text-2xl font-semibold text-foreground">{summary.overdue_count}</p>
                            </div>
                            <div className="rounded-lg bg-destructive/10 p-2">
                                <AlertCircle className="h-5 w-5 text-destructive" />
                            </div>
                        </div>
                    </Card>
                </div>

                {/* Payment Plans */}
                <div className="space-y-4">
                    <h2 className="text-xl font-semibold text-foreground">Available Payment Plans</h2>
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                        {paymentPlans.map((plan, index) => (
                            <Card key={index} className="bg-card p-5 transition-shadow hover:shadow-md">
                                <div className="space-y-2">
                                    <div className="flex items-center justify-between">
                                        <h3 className="font-semibold text-foreground">{plan.name}</h3>
                                        {plan.discount !== '0%' && (
                                            <Badge variant="secondary" className="text-xs">
                                                {plan.discount} off
                                            </Badge>
                                        )}
                                    </div>
                                    <p className="text-sm leading-relaxed text-muted-foreground">{plan.description}</p>
                                </div>
                            </Card>
                        ))}
                    </div>
                </div>

                {/* Enrollments Section */}
                <div className="space-y-4">
                    <div className="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                        <div>
                            <h2 className="text-xl font-semibold text-foreground">Enrollment Billing</h2>
                            <p className="mt-1 text-sm text-muted-foreground">
                                {filteredEnrollments.length} {filteredEnrollments.length === 1 ? 'record' : 'records'}
                            </p>
                        </div>
                        <div className="flex flex-col gap-3 sm:flex-row">
                            <div className="relative w-full sm:w-64">
                                <Search className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    type="text"
                                    placeholder="Search enrollments..."
                                    value={searchQuery}
                                    onChange={(e) => setSearchQuery(e.target.value)}
                                    className="pl-10"
                                />
                            </div>
                            <div className="flex gap-2">
                                <Button variant={filterStatus === 'all' ? 'default' : 'outline'} size="sm" onClick={() => setFilterStatus('all')}>
                                    All
                                </Button>
                                <Button variant={filterStatus === 'paid' ? 'default' : 'outline'} size="sm" onClick={() => setFilterStatus('paid')}>
                                    Paid
                                </Button>
                                <Button
                                    variant={filterStatus === 'partial' ? 'default' : 'outline'}
                                    size="sm"
                                    onClick={() => setFilterStatus('partial')}
                                >
                                    Partial
                                </Button>
                                <Button
                                    variant={filterStatus === 'pending' ? 'default' : 'outline'}
                                    size="sm"
                                    onClick={() => setFilterStatus('pending')}
                                >
                                    Pending
                                </Button>
                            </div>
                        </div>
                    </div>

                    {/* Enrollments Table */}
                    <Card className="overflow-hidden bg-card">
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead className="border-b bg-muted/50">
                                    <tr>
                                        <th className="w-12 px-6 py-4 text-left text-sm font-medium text-muted-foreground"></th>
                                        <th className="px-6 py-4 text-left text-sm font-medium text-muted-foreground">Enrollment ID</th>
                                        <th className="px-6 py-4 text-left text-sm font-medium text-muted-foreground">Student</th>
                                        <th className="px-6 py-4 text-left text-sm font-medium text-muted-foreground">School Year</th>
                                        <th className="px-6 py-4 text-left text-sm font-medium text-muted-foreground">Grade Level</th>
                                        <th className="px-6 py-4 text-left text-sm font-medium text-muted-foreground">Total Amount</th>
                                        <th className="px-6 py-4 text-left text-sm font-medium text-muted-foreground">Payment Status</th>
                                        <th className="px-6 py-4 text-left text-sm font-medium text-muted-foreground">Status</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y">
                                    {filteredEnrollments.length === 0 ? (
                                        <tr>
                                            <td colSpan={8} className="px-6 py-12 text-center text-sm text-muted-foreground">
                                                No enrollment records found
                                            </td>
                                        </tr>
                                    ) : (
                                        filteredEnrollments.map((enrollment) => (
                                            <>
                                                <tr
                                                    key={enrollment.id}
                                                    className="cursor-pointer transition-colors hover:bg-muted/30"
                                                    onClick={() => toggleExpand(enrollment.id)}
                                                >
                                                    <td className="px-6 py-4">
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            className="h-8 w-8 p-0"
                                                            onClick={(e) => {
                                                                e.stopPropagation();
                                                                toggleExpand(enrollment.id);
                                                            }}
                                                        >
                                                            {expandedEnrollment === enrollment.id ? (
                                                                <ChevronUp className="h-4 w-4" />
                                                            ) : (
                                                                <ChevronDown className="h-4 w-4" />
                                                            )}
                                                        </Button>
                                                    </td>
                                                    <td className="px-6 py-4 font-mono text-sm text-muted-foreground">
                                                        #{enrollment.id.toString().padStart(4, '0')}
                                                    </td>
                                                    <td className="px-6 py-4">
                                                        <Link
                                                            href={route('guardian.billing.show', { enrollment: enrollment.id })}
                                                            className="group block space-y-1"
                                                        >
                                                            <div className="text-sm font-medium group-hover:text-primary">
                                                                {enrollment.student_name}
                                                            </div>
                                                            <div className="font-mono text-xs text-muted-foreground">{enrollment.student_id}</div>
                                                        </Link>
                                                    </td>
                                                    <td className="px-6 py-4 text-sm">{enrollment.school_year_name}</td>
                                                    <td className="px-6 py-4 text-sm">{enrollment.grade_level}</td>
                                                    <td className="px-6 py-4">
                                                        <div className="text-sm font-semibold">{enrollment.total_amount}</div>
                                                    </td>
                                                    <td className="px-6 py-4">
                                                        <Badge variant={getPaymentStatusVariant(enrollment.payment_status)}>
                                                            {formatStatusName(enrollment.payment_status)}
                                                        </Badge>
                                                    </td>
                                                    <td className="px-6 py-4">
                                                        <Badge variant={getEnrollmentStatusVariant(enrollment.status)}>
                                                            {formatStatusName(enrollment.status)}
                                                        </Badge>
                                                    </td>
                                                </tr>
                                                {expandedEnrollment === enrollment.id && (
                                                    <tr className="bg-muted/20">
                                                        <td colSpan={8} className="px-6 py-6">
                                                            <div className="space-y-4">
                                                                <h3 className="text-sm font-semibold text-foreground">Fee Breakdown</h3>
                                                                <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                                                                    <Card className="bg-background p-4">
                                                                        <p className="mb-1 text-xs text-muted-foreground">Tuition Fee</p>
                                                                        <p className="text-lg font-semibold">{enrollment.tuition_fee}</p>
                                                                    </Card>
                                                                    <Card className="bg-background p-4">
                                                                        <p className="mb-1 text-xs text-muted-foreground">Miscellaneous Fee</p>
                                                                        <p className="text-lg font-semibold">{enrollment.miscellaneous_fee}</p>
                                                                    </Card>
                                                                    <Card className="bg-background p-4">
                                                                        <p className="mb-1 text-xs text-muted-foreground">Total Amount</p>
                                                                        <p className="text-lg font-semibold text-primary">
                                                                            {enrollment.total_amount}
                                                                        </p>
                                                                    </Card>
                                                                </div>
                                                                <div className="border-t pt-4">
                                                                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                                                        <div>
                                                                            <p className="mb-1 text-xs text-muted-foreground">Student Information</p>
                                                                            <p className="text-sm font-medium">{enrollment.student_name}</p>
                                                                            <p className="mt-1 font-mono text-xs text-muted-foreground">
                                                                                ID: {enrollment.student_id}
                                                                            </p>
                                                                        </div>
                                                                        <div>
                                                                            <p className="mb-1 text-xs text-muted-foreground">Enrollment Details</p>
                                                                            <p className="text-sm">
                                                                                {enrollment.school_year_name} • {enrollment.grade_level}
                                                                            </p>
                                                                            <div className="mt-2 flex gap-2">
                                                                                <Badge variant={getEnrollmentStatusVariant(enrollment.status)}>
                                                                                    {formatStatusName(enrollment.status)}
                                                                                </Badge>
                                                                                <Badge variant={getPaymentStatusVariant(enrollment.payment_status)}>
                                                                                    {formatStatusName(enrollment.payment_status)}
                                                                                </Badge>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                )}
                                            </>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </Card>
                </div>
            </div>
        </div>
    );
}
