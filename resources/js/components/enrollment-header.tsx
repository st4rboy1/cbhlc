import { GraduationCap } from 'lucide-react';

export function EnrollmentHeader() {
    return (
        <div className="rounded-lg border border-border bg-card p-6">
            <div className="flex items-start gap-3">
                <div className="flex h-10 w-10 items-center justify-center rounded-md bg-accent">
                    <GraduationCap className="h-5 w-5 text-accent-foreground" />
                </div>
                <div>
                    <h2 className="text-lg font-semibold text-foreground">Student Enrollments</h2>
                    <p className="text-sm text-muted-foreground">Manage and review student enrollment applications</p>
                </div>
            </div>
        </div>
    );
}
