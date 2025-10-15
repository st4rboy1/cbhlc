import { EnrollmentCard } from '@/components/enrollment-card';
import { EnrollmentFilters } from '@/components/enrollment-filters';
import { Button } from '@/components/ui/button';
import { type Enrollment, type Paginated } from '@/types';
import { Link } from '@inertiajs/react';
import { Plus } from 'lucide-react';

interface Props {
    enrollments: Paginated<Enrollment>;
    filters: Record<string, string>;
    statusCounts: {
        all: number;
        pending: number;
        approved: number;
        rejected: number;
        enrolled: number;
        completed: number;
    };
}

export function EnrollmentList({ enrollments, filters, statusCounts }: Props) {
    return (
        <div className="space-y-6">
            <div className="flex items-center justify-between">
                <div>
                    <h2 className="text-2xl font-semibold text-foreground">Enrollments</h2>
                    <p className="mt-1 text-sm text-muted-foreground">
                        {enrollments.total} {enrollments.total === 1 ? 'enrollment' : 'enrollments'}
                    </p>
                </div>
                <Button asChild className="gap-2">
                    <Link href="/admin/enrollments/create">
                        <Plus className="h-4 w-4" />
                        New Enrollment
                    </Link>
                </Button>
            </div>

            <EnrollmentFilters filters={filters} statusCounts={statusCounts} />

            <div className="space-y-3">
                {enrollments.data.length === 0 ? (
                    <div className="rounded-lg border border-border bg-card py-12 text-center">
                        <p className="text-muted-foreground">No enrollments found</p>
                    </div>
                ) : (
                    enrollments.data.map((enrollment) => <EnrollmentCard key={enrollment.id} enrollment={enrollment} />)
                )}
            </div>

            <div className="mt-4 flex items-center justify-between border-t pt-4">
                {enrollments.prev_page_url ? (
                    <Link href={enrollments.prev_page_url} className="text-sm font-medium text-primary hover:underline">
                        Previous
                    </Link>
                ) : (
                    <div />
                )}
                <div className="text-sm text-gray-500">
                    Page {enrollments.current_page} of {enrollments.last_page}
                </div>
                {enrollments.next_page_url ? (
                    <Link href={enrollments.next_page_url} className="text-sm font-medium text-primary hover:underline">
                        Next
                    </Link>
                ) : (
                    <div />
                )}
            </div>
        </div>
    );
}
