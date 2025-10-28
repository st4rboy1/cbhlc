import { Badge } from '@/components/ui/badge';

interface PaymentStatusBadgeProps {
    status: string;
}

const STATUS_VARIANTS: Record<string, { variant: 'default' | 'secondary' | 'destructive' | 'outline'; className?: string; label: string }> = {
    pending: { variant: 'outline', className: 'bg-yellow-100 text-yellow-800', label: 'Pending' },
    partial: { variant: 'secondary', className: 'bg-orange-100 text-orange-800', label: 'Partial' },
    paid: { variant: 'default', className: 'bg-green-100 text-green-800', label: 'Paid' },
    overdue: { variant: 'destructive', label: 'Overdue' },
};

export function PaymentStatusBadge({ status }: PaymentStatusBadgeProps) {
    const config = STATUS_VARIANTS[status] || { variant: 'outline' as const, label: status };

    return (
        <Badge variant={config.variant} className={config.className}>
            {config.label}
        </Badge>
    );
}
