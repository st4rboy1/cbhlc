import { Badge } from '@/components/ui/badge';

interface EnrollmentStatusBadgeProps {
    status: string;
}

const STATUS_VARIANTS: Record<string, { variant: 'default' | 'secondary' | 'destructive' | 'outline'; className?: string; label: string }> = {
    pending: { variant: 'outline', className: 'bg-yellow-100 text-yellow-800', label: 'Pending Review' },
    approved: { variant: 'default', className: 'bg-blue-100 text-blue-800', label: 'Approved' },
    rejected: { variant: 'destructive', label: 'Rejected' },
    ready_for_payment: { variant: 'outline', className: 'bg-purple-100 text-purple-800', label: 'Ready for Payment' },
    paid: { variant: 'default', className: 'bg-green-100 text-green-800', label: 'Paid' },
    enrolled: { variant: 'default', className: 'bg-primary text-primary-foreground', label: 'Enrolled' },
    completed: { variant: 'secondary', label: 'Completed' },
};

export function EnrollmentStatusBadge({ status }: EnrollmentStatusBadgeProps) {
    const config = STATUS_VARIANTS[status] || { variant: 'outline' as const, label: status };

    return (
        <Badge variant={config.variant} className={config.className}>
            {config.label}
        </Badge>
    );
}
