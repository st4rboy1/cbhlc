import { Badge } from '@/components/ui/badge';

interface EnrollmentPeriodStatusBadgeProps {
    status: string;
}

const STATUS_VARIANTS: Record<string, { variant: 'default' | 'secondary' | 'destructive' | 'outline'; className?: string; label: string }> = {
    active: { variant: 'default', className: 'bg-green-100 text-green-800', label: 'Active' },
    upcoming: { variant: 'secondary', className: 'bg-yellow-100 text-yellow-800', label: 'Upcoming' },
    closed: { variant: 'outline', className: 'bg-red-100 text-red-800', label: 'Closed' },
};

export function EnrollmentPeriodStatusBadge({ status }: EnrollmentPeriodStatusBadgeProps) {
    const config = STATUS_VARIANTS[status] || { variant: 'outline' as const, label: status };

    return (
        <Badge variant={config.variant} className={config.className}>
            {config.label}
        </Badge>
    );
}
