import PageLayout from '@/components/PageLayout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link } from '@inertiajs/react';
import { Plus, Users } from 'lucide-react';

interface Student {
    id: number;
    student_id: string;
    first_name: string;
    middle_name: string | null;
    last_name: string;
    full_name: string;
    birthdate: string;
    grade_level: string;
    relationship_type: string;
    is_primary_contact: boolean;
    user: {
        id: number;
        email: string;
    } | null;
}

interface StudentsIndexProps {
    children: Student[];
}

export default function StudentsIndex({ children }: StudentsIndexProps) {
    return (
        <>
            <Head title="My Children" />
            <PageLayout title="MY CHILDREN" currentPage="guardian.students.index">
                <div className="mx-auto max-w-6xl">
                    {/* Header with Add Button */}
                    <div className="mb-6 flex items-center justify-between">
                        <div className="flex items-center gap-2">
                            <Users className="h-6 w-6 text-primary" />
                            <h2 className="text-2xl font-semibold">Registered Children</h2>
                        </div>
                        <Button asChild>
                            <Link href="/guardian/students/create">
                                <Plus className="mr-2 h-4 w-4" />
                                Add New Student
                            </Link>
                        </Button>
                    </div>

                    {/* Students List */}
                    {children.length === 0 ? (
                        <Card>
                            <CardContent className="flex flex-col items-center justify-center py-12">
                                <Users className="mb-4 h-12 w-12 text-muted-foreground" />
                                <CardTitle className="mb-2">No Students Yet</CardTitle>
                                <CardDescription className="mb-4 text-center">You haven't registered any children yet.</CardDescription>
                                <Button asChild>
                                    <Link href="/guardian/students/create">
                                        <Plus className="mr-2 h-4 w-4" />
                                        Add Your First Student
                                    </Link>
                                </Button>
                            </CardContent>
                        </Card>
                    ) : (
                        <div className="grid gap-4 md:grid-cols-2">
                            {children.map((student) => (
                                <Card key={student.id}>
                                    <CardHeader>
                                        <div className="flex items-start justify-between">
                                            <div>
                                                <CardTitle className="text-xl">{student.full_name}</CardTitle>
                                                <CardDescription>Student ID: {student.student_id}</CardDescription>
                                            </div>
                                            {student.is_primary_contact && (
                                                <span className="rounded-full bg-primary/10 px-2 py-1 text-xs font-medium text-primary">Primary</span>
                                            )}
                                        </div>
                                    </CardHeader>
                                    <CardContent>
                                        <div className="space-y-2 text-sm">
                                            <div className="flex justify-between">
                                                <span className="text-muted-foreground">Grade Level:</span>
                                                <span className="font-medium">{student.grade_level}</span>
                                            </div>
                                            <div className="flex justify-between">
                                                <span className="text-muted-foreground">Birthdate:</span>
                                                <span className="font-medium">{student.birthdate}</span>
                                            </div>
                                            <div className="flex justify-between">
                                                <span className="text-muted-foreground">Relationship:</span>
                                                <span className="font-medium capitalize">{student.relationship_type}</span>
                                            </div>
                                            {student.user && (
                                                <div className="flex justify-between">
                                                    <span className="text-muted-foreground">Email:</span>
                                                    <span className="font-medium">{student.user.email}</span>
                                                </div>
                                            )}
                                        </div>

                                        <div className="mt-4 flex gap-2">
                                            <Button variant="outline" size="sm" className="flex-1" asChild>
                                                <Link href={`/guardian/students/${student.id}/edit`}>Edit</Link>
                                            </Button>
                                            <Button variant="outline" size="sm" className="flex-1" asChild>
                                                <Link href={`/enrollments/create?student_id=${student.id}`}>Enroll</Link>
                                            </Button>
                                        </div>
                                    </CardContent>
                                </Card>
                            ))}
                        </div>
                    )}
                </div>
            </PageLayout>
        </>
    );
}
