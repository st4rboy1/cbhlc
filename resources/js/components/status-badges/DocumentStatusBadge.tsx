import { Badge } from '@/components/ui/badge';

interface DocumentStatusBadgeProps {
    status: string;
}

function getStatusBadgeVariant(status: string): 'default' | 'secondary' | 'destructive' {
    switch (status) {
        case 'verified':
            return 'default';
        case 'pending':
            return 'secondary';
        case 'rejected':
            return 'destructive';
        default:
            return 'secondary';
    }
}

export function DocumentStatusBadge({ status }: DocumentStatusBadgeProps) {
    return <Badge variant={getStatusBadgeVariant(status)}>{status.charAt(0).toUpperCase() + status.slice(1)}</Badge>;
}
