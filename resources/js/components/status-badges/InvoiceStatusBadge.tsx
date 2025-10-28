import { Badge } from '@/components/ui/badge';

interface InvoiceStatusBadgeProps {
    status: string;
}

const STATUS_VARIANTS: Record<string, { className: string; label: string }> = {
    draft: { className: 'bg-gray-100 text-gray-800', label: 'Draft' },
    sent: { className: 'bg-blue-100 text-blue-800', label: 'Sent' },
    partially_paid: { className: 'bg-yellow-100 text-yellow-800', label: 'Partially Paid' },
    paid: { className: 'bg-green-100 text-green-800', label: 'Paid' },
    cancelled: { className: 'bg-red-100 text-red-800', label: 'Cancelled' },
    overdue: { className: 'bg-orange-100 text-orange-800', label: 'Overdue' },
};

export function InvoiceStatusBadge({ status }: InvoiceStatusBadgeProps) {
    const config = STATUS_VARIANTS[status] || { className: 'bg-gray-100 text-gray-800', label: status };

    return <Badge className={config.className}>{config.label}</Badge>;
}
