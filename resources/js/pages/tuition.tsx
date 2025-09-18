import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Head } from '@inertiajs/react';
import { Calendar, DollarSign, MapPin, User } from 'lucide-react';
import PageLayout from '../components/PageLayout';

interface StudentInfo {
    name: string;
    age: number;
    gender: string;
    section: string;
    birthdate: string;
}

export default function Tuition() {
    const studentInfo: StudentInfo = {
        name: 'Manero Sj Rodriguez',
        age: 12,
        gender: 'Male',
        section: 'Grade 6-A',
        birthdate: 'March 15, 2012',
    };

    const tuitionDetails = [
        { label: 'Monthly Tuition Fee', amount: '₱12,000.00' },
        { label: 'Miscellaneous Fee', amount: '₱3,500.00' },
        { label: 'Laboratory Fee', amount: '₱2,000.00' },
        { label: 'Library Fee', amount: '₱800.00' },
        { label: 'Sports Fee', amount: '₱1,200.00' },
    ];

    const totalAmount = '₱19,500.00';

    return (
        <>
            <Head title="Tuition" />
            <PageLayout title="TUITION" currentPage="tuition">
                {/* Payment Status Alert */}
                <Card className="mb-6 border-green-200 bg-green-50">
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2 text-green-800">
                            <DollarSign className="h-5 w-5" />
                            Payment Status
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-2">
                            <Badge variant="default" className="bg-green-100 text-green-800 hover:bg-green-200">
                                READY FOR PAYMENT!
                            </Badge>
                            <p className="text-green-700">Visit cashier on-site to finish the process.</p>
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
                                    <span className="font-semibold">{studentInfo.name}</span>
                                </div>
                                <Separator />
                                <div className="flex justify-between">
                                    <span className="font-medium text-muted-foreground">Age:</span>
                                    <span className="font-semibold">{studentInfo.age} years old</span>
                                </div>
                                <Separator />
                                <div className="flex justify-between">
                                    <span className="font-medium text-muted-foreground">Gender:</span>
                                    <span className="font-semibold">{studentInfo.gender}</span>
                                </div>
                                <Separator />
                                <div className="flex justify-between">
                                    <span className="font-medium text-muted-foreground">Section:</span>
                                    <span className="font-semibold">{studentInfo.section}</span>
                                </div>
                                <Separator />
                                <div className="flex justify-between">
                                    <span className="font-medium text-muted-foreground">Birthdate:</span>
                                    <span className="flex items-center gap-1 font-semibold">
                                        <Calendar className="h-4 w-4 text-muted-foreground" />
                                        {studentInfo.birthdate}
                                    </span>
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
                                {tuitionDetails.map((item, index) => (
                                    <div key={index}>
                                        <div className="flex items-center justify-between">
                                            <span className="text-sm font-medium text-muted-foreground">{item.label}</span>
                                            <span className="text-right font-semibold">{item.amount}</span>
                                        </div>
                                        {index < tuitionDetails.length - 1 && <Separator className="mt-2" />}
                                    </div>
                                ))}
                            </div>

                            <Separator className="my-4" />

                            <div className="flex items-center justify-between rounded-lg bg-primary/5 p-3">
                                <span className="text-lg font-bold">Total Amount:</span>
                                <span className="text-lg font-bold text-primary">{totalAmount}</span>
                            </div>
                        </CardContent>
                    </Card>
                </div>

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
                                <strong className="text-foreground">Payment Location:</strong> Visit the school cashier's office during business
                                hours.
                            </p>
                            <p>
                                <strong className="text-foreground">Business Hours:</strong> Monday to Friday, 8:00 AM - 5:00 PM
                            </p>
                            <p>
                                <strong className="text-foreground">Payment Methods:</strong> Cash, Check, or Bank Transfer
                            </p>
                            <p>
                                <strong className="text-foreground">Note:</strong> Please bring this tuition statement and a valid ID when making
                                payment.
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </PageLayout>
        </>
    );
}
