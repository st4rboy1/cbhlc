import { Input } from '@/components/ui/input';
import { Search } from 'lucide-react';

interface EnrollmentFiltersProps {
    statusFilter: string;
    onStatusFilterChange: (status: string) => void;
    searchQuery: string;
    onSearchQueryChange: (query: string) => void;
    statusCounts: {
        all: number;
        pending: number;
        approved: number;
        rejected: number;
    };
}

export function EnrollmentFilters({ statusFilter, onStatusFilterChange, searchQuery, onSearchQueryChange, statusCounts }: EnrollmentFiltersProps) {
    const filters = [
        { label: 'All', value: 'all', count: statusCounts.all },
        { label: 'Pending', value: 'pending', count: statusCounts.pending },
        { label: 'Approved', value: 'approved', count: statusCounts.approved },
        { label: 'Rejected', value: 'rejected', count: statusCounts.rejected },
    ];

    return (
        <div className="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
            <div className="flex gap-2 overflow-x-auto pb-2 sm:pb-0">
                {filters.map((filter) => (
                    <button
                        key={filter.value}
                        onClick={() => onStatusFilterChange(filter.value)}
                        className={`rounded-md px-4 py-2 text-sm font-medium whitespace-nowrap transition-colors ${
                            statusFilter === filter.value
                                ? 'bg-secondary text-secondary-foreground'
                                : 'text-muted-foreground hover:bg-secondary/50 hover:text-foreground'
                        }`}
                    >
                        {filter.label}
                        <span className="ml-2 text-xs opacity-70">({filter.count})</span>
                    </button>
                ))}
            </div>

            <div className="relative w-full sm:w-64">
                <Search className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                <Input
                    type="text"
                    placeholder="Search enrollments..."
                    value={searchQuery}
                    onChange={(e) => onSearchQueryChange(e.target.value)}
                    className="border-border bg-card pl-9"
                />
            </div>
        </div>
    );
}
