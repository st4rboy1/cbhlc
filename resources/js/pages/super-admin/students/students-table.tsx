import { Badge } from '@/components/ui/badge';
import { Card } from '@/components/ui/card';

type Student = {
    id: number;
    studentId: string;
    name: string;
    gradeLevel: string;
    guardian: string;
    enrollmentStatus: string;
    paymentStatus: string;
    balance: number;
    netAmount: number;
};

type StudentTableProps = {
    students: Student[];
};

function formatCurrency(cents: number) {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(cents / 100);
}

function getStatusVariant(status: string): 'default' | 'secondary' | 'destructive' | 'outline' {
    switch (status) {
        case 'enrolled':
        case 'completed':
        case 'paid':
            return 'default';
        case 'pending':
            return 'secondary';
        case 'overdue':
        case 'rejected':
            return 'destructive';
        default:
            return 'outline';
    }
}

export function StudentTable({ students }: StudentTableProps) {
    return (
        <Card className="overflow-hidden">
            <div className="overflow-x-auto">
                <table className="w-full">
                    <thead className="border-b bg-muted/50">
                        <tr>
                            <th className="px-6 py-4 text-left text-sm font-medium text-muted-foreground">Student ID</th>
                            <th className="px-6 py-4 text-left text-sm font-medium text-muted-foreground">Name</th>
                            <th className="px-6 py-4 text-left text-sm font-medium text-muted-foreground">Grade</th>
                            <th className="px-6 py-4 text-left text-sm font-medium text-muted-foreground">Guardian</th>
                            <th className="px-6 py-4 text-left text-sm font-medium text-muted-foreground">Enrollment</th>
                            <th className="px-6 py-4 text-left text-sm font-medium text-muted-foreground">Payment</th>
                            <th className="px-6 py-4 text-right text-sm font-medium text-muted-foreground">Balance</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y">
                        {students.map((student) => (
                            <tr key={student.id} className="transition-colors hover:bg-muted/30">
                                <td className="px-6 py-4 font-mono text-sm text-muted-foreground">{student.studentId}</td>
                                <td className="px-6 py-4 text-sm font-medium">{student.name}</td>
                                <td className="px-6 py-4 text-sm">{student.gradeLevel}</td>
                                <td className="px-6 py-4 text-sm text-muted-foreground">{student.guardian}</td>
                                <td className="px-6 py-4">
                                    <Badge variant={getStatusVariant(student.enrollmentStatus)} className="capitalize">
                                        {student.enrollmentStatus}
                                    </Badge>
                                </td>
                                <td className="px-6 py-4">
                                    <Badge variant={getStatusVariant(student.paymentStatus)} className="capitalize">
                                        {student.paymentStatus}
                                    </Badge>
                                </td>
                                <td className="px-6 py-4 text-right text-sm font-medium tabular-nums">
                                    {student.balance > 0 ? formatCurrency(student.balance) : '—'}
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </Card>
    );
}
