import { Head, Link } from '@inertiajs/react'
import { Button } from '@/components/ui/button'
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import { Badge } from '@/components/ui/badge'
import { PlusCircle } from 'lucide-react'
import type { Enrollment } from '@/types'

interface Props {
    enrollments: {
        data: Enrollment[]
        links: any
        meta: any
    }
}

const statusColors = {
    pending: 'yellow',
    approved: 'blue',
    enrolled: 'green',
    rejected: 'red',
    completed: 'gray',
} as const

const paymentStatusColors = {
    pending: 'yellow',
    partial: 'orange',
    paid: 'green',
    overdue: 'red',
} as const

export default function GuardianEnrollmentsIndex({ enrollments }: Props) {
    return (
        <>
            <Head title="My Children's Enrollments" />

            <div className="container mx-auto py-6">
                <div className="flex justify-between items-center mb-6">
                    <h1 className="text-3xl font-bold">My Children's Enrollments</h1>
                    <Link href={route('guardian.enrollments.create')}>
                        <Button>
                            <PlusCircle className="mr-2 h-4 w-4" />
                            New Enrollment
                        </Button>
                    </Link>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Enrollment Applications</CardTitle>
                        <CardDescription>
                            View and manage your children's enrollment applications
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Student Name</TableHead>
                                    <TableHead>School Year</TableHead>
                                    <TableHead>Grade Level</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Payment Status</TableHead>
                                    <TableHead>Submission Date</TableHead>
                                    <TableHead>Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {enrollments.data.map((enrollment) => (
                                    <TableRow key={enrollment.id}>
                                        <TableCell>
                                            {enrollment.student.first_name} {enrollment.student.last_name}
                                        </TableCell>
                                        <TableCell>{enrollment.school_year}</TableCell>
                                        <TableCell>{enrollment.grade_level}</TableCell>
                                        <TableCell>
                                            <Badge variant={statusColors[enrollment.status] || 'default'}>
                                                {enrollment.status}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant={paymentStatusColors[enrollment.payment_status] || 'default'}>
                                                {enrollment.payment_status}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>{enrollment.created_at}</TableCell>
                                        <TableCell>
                                            <div className="flex gap-2">
                                                <Link href={route('guardian.enrollments.show', enrollment.id)}>
                                                    <Button size="sm" variant="outline">
                                                        View
                                                    </Button>
                                                </Link>
                                                {enrollment.status === 'pending' && (
                                                    <Link href={route('guardian.enrollments.edit', enrollment.id)}>
                                                        <Button size="sm" variant="outline">
                                                            Edit
                                                        </Button>
                                                    </Link>
                                                )}
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>
        </>
    )
}