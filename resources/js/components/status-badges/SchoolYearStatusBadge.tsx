import { Badge } from '@/components/ui/badge';

interface SchoolYearStatusBadgeProps {
    status: string;
}

const STATUS_VARIANTS: Record<string, { variant: 'default' | 'secondary' | 'destructive' | 'outline'; className?: string; label: string }> = {
    active: { variant: 'default', className: 'bg-green-100 text-green-800', label: 'Active' },
    upcoming: { variant: 'secondary', className: 'bg-yellow-100 text-yellow-800', label: 'Upcoming' },
    completed: { variant: 'outline', className: 'bg-gray-100 text-gray-800', label: 'Completed' },
};

export function SchoolYearStatusBadge({ status }: SchoolYearStatusBadgeProps) {
    const config = STATUS_VARIANTS[status] || { variant: 'outline' as const, label: status };

    return (
        <Badge variant={config.variant} className={config.className}>
            {config.label}
        </Badge>
    );
}
