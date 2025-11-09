import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { Head, Link } from '@inertiajs/react';
import { Calendar, FileText, Users } from 'lucide-react';

interface SchoolYear {
    id: number;
    name: string;
    start_year: number;
    end_year: number;
    start_date: string;
    end_date: string;
    status: string;
    is_active: boolean;
    enrollments_count: number;
    invoices_count: number;
}

interface Props {
    schoolYear: SchoolYear;
}

export default function SchoolYearShow({ schoolYear }: Props) {
    return (
        <AppLayout
            breadcrumbs={[
                { title: 'Admin', href: '/admin/dashboard' },
                { title: 'School Years', href: '/admin/school-years' },
                { title: schoolYear.name, href: '#' },
            ]}
        >
            <Head title={schoolYear.name} />
            <div className="px-4 py-6">
                <div className="mb-6 flex items-center justify-between">
                    <div>
                        <h1 className="flex items-center gap-2 text-2xl font-bold">
                            {schoolYear.name}
                            {schoolYear.is_active && <Badge>Active</Badge>}
                        </h1>
                        <p className="text-muted-foreground">{schoolYear.status}</p>
                    </div>
                    <Link href={`/admin/school-years/${schoolYear.id}/edit`}>
                        <Button>Edit School Year</Button>
                    </Link>
                </div>

                <div className="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Period</CardTitle>
                            <Calendar className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {schoolYear.start_year} - {schoolYear.end_year}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                {new Date(schoolYear.start_date).toLocaleDateString()} - {new Date(schoolYear.end_date).toLocaleDateString()}
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Enrollments</CardTitle>
                            <Users className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{schoolYear.enrollments_count}</div>
                            <p className="text-xs text-muted-foreground">Total enrollments</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Invoices</CardTitle>
                            <FileText className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{schoolYear.invoices_count}</div>
                            <p className="text-xs text-muted-foreground">Total invoices</p>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
