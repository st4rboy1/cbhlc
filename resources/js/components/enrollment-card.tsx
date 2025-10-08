import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Check, Clock, MoreVertical, User, X } from 'lucide-react';

interface Enrollment {
    id: number;
    student_name: string;
    grade: string;
    status: 'pending' | 'approved' | 'rejected';
}

interface EnrollmentCardProps {
    enrollment: Enrollment;
    onStatusChange: (id: number, status: 'pending' | 'approved' | 'rejected') => void;
}

export function EnrollmentCard({ enrollment, onStatusChange }: EnrollmentCardProps) {
    const statusConfig = {
        pending: {
            label: 'Pending',
            icon: Clock,
            className: 'bg-warning/10 text-warning-foreground border-warning/20',
        },
        approved: {
            label: 'Approved',
            icon: Check,
            className: 'bg-success/10 text-success-foreground border-success/20',
        },
        rejected: {
            label: 'Rejected',
            icon: X,
            className: 'bg-destructive/10 text-destructive-foreground border-destructive/20',
        },
    };

    const config = statusConfig[enrollment.status];
    const StatusIcon = config.icon;

    return (
        <div className="rounded-lg border border-border bg-card p-4 transition-colors hover:border-muted-foreground/20">
            <div className="flex items-center justify-between gap-4">
                <div className="flex min-w-0 flex-1 items-center gap-4">
                    <div className="flex h-10 w-10 items-center justify-center rounded-full bg-secondary">
                        <User className="h-5 w-5 text-secondary-foreground" />
                    </div>

                    <div className="min-w-0 flex-1">
                        <h3 className="text-base font-semibold text-foreground">{enrollment.student_name}</h3>
                        <p className="text-sm text-muted-foreground">{enrollment.grade}</p>
                    </div>
                </div>

                <div className="flex items-center gap-3">
                    <div className={`flex items-center gap-2 rounded-md border px-3 py-1.5 text-sm font-medium ${config.className}`}>
                        <StatusIcon className="h-4 w-4" />
                        {config.label}
                    </div>

                    {enrollment.status === 'pending' && (
                        <div className="flex gap-2">
                            <Button
                                size="sm"
                                variant="outline"
                                onClick={() => onStatusChange(enrollment.id, 'approved')}
                                className="bg-success/10 border-success/20 text-success-foreground hover:bg-success/20 gap-2"
                            >
                                <Check className="h-4 w-4" />
                                Approve
                            </Button>
                            <Button
                                size="sm"
                                variant="outline"
                                onClick={() => onStatusChange(enrollment.id, 'rejected')}
                                className="gap-2 border-destructive/20 bg-destructive/10 text-destructive-foreground hover:bg-destructive/20"
                            >
                                <X className="h-4 w-4" />
                                Reject
                            </Button>
                        </div>
                    )}

                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <Button variant="ghost" size="sm" className="h-8 w-8 p-0">
                                <MoreVertical className="h-4 w-4" />
                                <span className="sr-only">Open menu</span>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" className="w-40">
                            <DropdownMenuItem>View Details</DropdownMenuItem>
                            <DropdownMenuItem>Edit</DropdownMenuItem>
                            {enrollment.status !== 'pending' && (
                                <DropdownMenuItem onClick={() => onStatusChange(enrollment.id, 'pending')}>Reset to Pending</DropdownMenuItem>
                            )}
                            <DropdownMenuItem className="text-destructive">Delete</DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>
        </div>
    );
}
