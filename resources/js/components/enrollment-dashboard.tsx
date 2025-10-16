'use client';

import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import axios from 'axios';
import { FileText, Loader2, Search, User } from 'lucide-react';
import { useEffect, useState } from 'react';

// Define the types for the enrollment data
interface Student {
    id: number;
    student_id: string;
    first_name: string;
    last_name: string;
    middle_name: string;
    birthdate: string;
    gender: string;
    address: string;
    contact_number: string;
}

interface Guardian {
    id: number;
    first_name: string;
    last_name: string;
    occupation: string;
    contact_number: string;
}

interface Enrollment {
    id: number;
    enrollment_id: string;
    student: Student;
    guardian: Guardian;
    status: string;
    payment_status: string;
    net_amount_cents: number;
    school_year: string;
    quarter: string;
    grade_level: string;
    tuition_fee_cents: number;
    miscellaneous_fee_cents: number;
    laboratory_fee_cents: number;
    amount_paid_cents: number;
    balance_cents: number;
    approved_at: string | null;
    remarks: string | null;
}

interface EnrollmentData {
    total: number;
    data: Enrollment[];
}

export function EnrollmentDashboard() {
    const [enrollmentData, setEnrollmentData] = useState<EnrollmentData | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [searchTerm, setSearchTerm] = useState('');
    const [statusFilter, setStatusFilter] = useState('all');
    const [paymentFilter, setPaymentFilter] = useState('all');

    useEffect(() => {
        const fetchEnrollments = async () => {
            try {
                setLoading(true);
                const response = await axios.get('/super-admin/enrollments');
                setEnrollmentData(response.data.enrollments);
                setError(null);
            } catch (err) {
                setError('Failed to fetch enrollments. Please try again later.');
                console.error(err);
            } finally {
                setLoading(false);
            }
        };

        fetchEnrollments();
    }, []);

    const filteredEnrollments = enrollmentData?.data.filter((enrollment) => {
        const matchesSearch =
            enrollment.student.first_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
            enrollment.student.last_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
            enrollment.enrollment_id.toLowerCase().includes(searchTerm.toLowerCase());

        const matchesStatus = statusFilter === 'all' || enrollment.status === statusFilter;
        const matchesPayment = paymentFilter === 'all' || enrollment.payment_status === paymentFilter;

        return matchesSearch && matchesStatus && matchesPayment;
    });

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'completed':
                return 'bg-green-500/10 text-green-700 dark:text-green-400';
            case 'enrolled':
                return 'bg-blue-500/10 text-blue-700 dark:text-blue-400';
            case 'pending':
                return 'bg-yellow-500/10 text-yellow-700 dark:text-yellow-400';
            case 'rejected':
                return 'bg-red-500/10 text-red-700 dark:text-red-400';
            default:
                return 'bg-gray-500/10 text-gray-700 dark:text-gray-400';
        }
    };

    const getPaymentStatusColor = (status: string) => {
        switch (status) {
            case 'paid':
                return 'bg-green-500/10 text-green-700 dark:text-green-400';
            case 'partial':
                return 'bg-orange-500/10 text-orange-700 dark:text-orange-400';
            case 'pending':
                return 'bg-yellow-500/10 text-yellow-700 dark:text-yellow-400';
            case 'overdue':
                return 'bg-red-500/10 text-red-700 dark:text-red-400';
            default:
                return 'bg-gray-500/10 text-gray-700 dark:text-gray-400';
        }
    };

    const formatCurrency = (cents: number) => {
        return new Intl.NumberFormat('en-PH', {
            style: 'currency',
            currency: 'PHP',
        }).format(cents / 100);
    };

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
        });
    };

    if (loading) {
        return (
            <div className="flex h-screen items-center justify-center">
                <Loader2 className="h-8 w-8 animate-spin" />
            </div>
        );
    }

    if (error) {
        return (
            <div className="flex h-screen items-center justify-center">
                <Card className="w-full max-w-md">
                    <CardHeader>
                        <CardTitle>Error</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p className="text-red-500">{error}</p>
                    </CardContent>
                </Card>
            </div>
        );
    }

    return (
        <div className="container mx-auto px-4 py-8">
            <div className="mb-8">
                <h1 className="mb-2 text-4xl font-bold">Enrollment Management</h1>
                <p className="text-muted-foreground">Manage and track student enrollments across all school years</p>
            </div>

            {/* Filters */}
            <div className="mb-6 flex flex-col gap-4 md:flex-row">
                <div className="relative flex-1">
                    <Search className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        placeholder="Search by student name or enrollment ID..."
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                        className="pl-10"
                    />
                </div>
                <Select value={statusFilter} onValueChange={setStatusFilter}>
                    <SelectTrigger className="w-full md:w-[180px]">
                        <SelectValue placeholder="Status" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All Status</SelectItem>
                        <SelectItem value="pending">Pending</SelectItem>
                        <SelectItem value="enrolled">Enrolled</SelectItem>
                        <SelectItem value="completed">Completed</SelectItem>
                        <SelectItem value="rejected">Rejected</SelectItem>
                    </SelectContent>
                </Select>
                <Select value={paymentFilter} onValueChange={setPaymentFilter}>
                    <SelectTrigger className="w-full md:w-[180px]">
                        <SelectValue placeholder="Payment" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All Payments</SelectItem>
                        <SelectItem value="paid">Paid</SelectItem>
                        <SelectItem value="partial">Partial</SelectItem>
                        <SelectItem value="pending">Pending</SelectItem>
                        <SelectItem value="overdue">Overdue</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            {/* Stats */}
            <div className="mb-6 grid grid-cols-1 gap-4 md:grid-cols-4">
                <Card>
                    <CardHeader className="pb-3">
                        <CardTitle className="text-sm font-medium text-muted-foreground">Total Enrollments</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">{enrollmentData?.total}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader className="pb-3">
                        <CardTitle className="text-sm font-medium text-muted-foreground">Pending</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">{enrollmentData?.data.filter((e) => e.status === 'pending').length}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader className="pb-3">
                        <CardTitle className="text-sm font-medium text-muted-foreground">Enrolled</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">{enrollmentData?.data.filter((e) => e.status === 'enrolled').length}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader className="pb-3">
                        <CardTitle className="text-sm font-medium text-muted-foreground">Overdue Payments</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold text-red-600">
                            {enrollmentData?.data.filter((e) => e.payment_status === 'overdue').length}
                        </div>
                    </CardContent>
                </Card>
            </div>

            {/* Enrollment Cards */}
            <div className="grid grid-cols-1 gap-4">
                {filteredEnrollments?.map((enrollment) => (
                    <Card key={enrollment.id} className="transition-shadow hover:shadow-lg">
                        <CardHeader>
                            <div className="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                <div className="flex-1">
                                    <div className="mb-2 flex items-center gap-2">
                                        <CardTitle className="text-xl">
                                            {enrollment.student.first_name} {enrollment.student.middle_name} {enrollment.student.last_name}
                                        </CardTitle>
                                        <Badge className={getStatusColor(enrollment.status)}>{enrollment.status}</Badge>
                                        <Badge className={getPaymentStatusColor(enrollment.payment_status)}>{enrollment.payment_status}</Badge>
                                    </div>
                                    <CardDescription className="flex flex-col gap-1">
                                        <span className="flex items-center gap-2">
                                            <FileText className="h-4 w-4" />
                                            {enrollment.enrollment_id}
                                        </span>
                                        <span className="flex items-center gap-2">
                                            <User className="h-4 w-4" />
                                            Student ID: {enrollment.student.student_id}
                                        </span>
                                    </CardDescription>
                                </div>
                                <div className="text-right">
                                    <div className="text-2xl font-bold">{formatCurrency(enrollment.net_amount_cents)}</div>
                                    <div className="text-sm text-muted-foreground">Total Amount</div>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                                {/* Academic Info */}
                                <div className="space-y-2">
                                    <h4 className="text-sm font-semibold text-muted-foreground">Academic Information</h4>
                                    <div className="space-y-1 text-sm">
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">School Year:</span>
                                            <span className="font-medium">{enrollment.school_year}</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Quarter:</span>
                                            <span className="font-medium">{enrollment.quarter}</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Grade Level:</span>
                                            <span className="font-medium">{enrollment.grade_level}</span>
                                        </div>
                                    </div>
                                </div>

                                {/* Fee Breakdown */}
                                <div className="space-y-2">
                                    <h4 className="text-sm font-semibold text-muted-foreground">Fee Breakdown</h4>
                                    <div className="space-y-1 text-sm">
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Tuition:</span>
                                            <span className="font-medium">{formatCurrency(enrollment.tuition_fee_cents)}</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Miscellaneous:</span>
                                            <span className="font-medium">{formatCurrency(enrollment.miscellaneous_fee_cents)}</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Laboratory:</span>
                                            <span className="font-medium">{formatCurrency(enrollment.laboratory_fee_cents)}</span>
                                        </div>
                                    </div>
                                </div>

                                {/* Payment Info */}
                                <div className="space-y-2">
                                    <h4 className="text-sm font-semibold text-muted-foreground">Payment Information</h4>
                                    <div className="space-y-1 text-sm">
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Amount Paid:</span>
                                            <span className="font-medium text-green-600">{formatCurrency(enrollment.amount_paid_cents)}</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Balance:</span>
                                            <span className="font-medium text-red-600">{formatCurrency(enrollment.balance_cents)}</span>
                                        </div>
                                        {enrollment.approved_at && (
                                            <div className="flex justify-between">
                                                <span className="text-muted-foreground">Approved:</span>
                                                <span className="font-medium">{formatDate(enrollment.approved_at)}</span>
                                            </div>
                                        )}
                                    </div>
                                </div>

                                {/* Student Info */}
                                <div className="space-y-2">
                                    <h4 className="text-sm font-semibold text-muted-foreground">Student Details</h4>
                                    <div className="space-y-1 text-sm">
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Gender:</span>
                                            <span className="font-medium">{enrollment.student.gender}</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Birthdate:</span>
                                            <span className="font-medium">{formatDate(enrollment.student.birthdate)}</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Contact:</span>
                                            <span className="font-medium">{enrollment.student.contact_number}</span>
                                        </div>
                                    </div>
                                </div>

                                {/* Guardian Info */}
                                <div className="space-y-2">
                                    <h4 className="text-sm font-semibold text-muted-foreground">Guardian Details</h4>
                                    <div className="space-y-1 text-sm">
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Name:</span>
                                            <span className="font-medium">
                                                {enrollment.guardian.first_name} {enrollment.guardian.last_name}
                                            </span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Occupation:</span>
                                            <span className="font-medium">{enrollment.guardian.occupation}</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Contact:</span>
                                            <span className="font-medium">{enrollment.guardian.contact_number}</span>
                                        </div>
                                    </div>
                                </div>

                                {/* Address */}
                                <div className="space-y-2">
                                    <h4 className="text-sm font-semibold text-muted-foreground">Address</h4>
                                    <p className="text-sm">{enrollment.student.address}</p>
                                </div>
                            </div>

                            {enrollment.remarks && (
                                <div className="mt-4 rounded-lg bg-muted p-3">
                                    <p className="text-sm">
                                        <span className="font-semibold">Remarks:</span> {enrollment.remarks}
                                    </p>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                ))}
            </div>

            {filteredEnrollments?.length === 0 && (
                <Card>
                    <CardContent className="py-12 text-center">
                        <p className="text-muted-foreground">No enrollments found matching your filters.</p>
                    </CardContent>
                </Card>
            )}
        </div>
    );
}
