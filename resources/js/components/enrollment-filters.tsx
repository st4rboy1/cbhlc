import { Input } from '@/components/ui/input';
import { router } from '@inertiajs/react';
import { Search } from 'lucide-react';
import { useEffect, useState } from 'react';
import { useDebouncedCallback } from 'use-debounce';

interface EnrollmentFiltersProps {
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

export function EnrollmentFilters({ filters, statusCounts }: EnrollmentFiltersProps) {
    const [search, setSearch] = useState(filters.search || '');
    const status = filters.status || 'all';

    const debounced = useDebouncedCallback((value) => {
        router.get(
            '/admin/enrollments',
            { search: value, status },
            {
                preserveState: true,
                replace: true,
            },
        );
    }, 300);

    useEffect(() => {
        setSearch(filters.search || '');
    }, [filters.search]);

    const handleStatusChange = (newStatus: string) => {
        router.get(
            '/admin/enrollments',
            { search, status: newStatus },
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    const filterOptions = [
        { label: 'All', value: 'all', count: statusCounts.all },
        { label: 'Pending', value: 'pending', count: statusCounts.pending },
        { label: 'Approved', value: 'approved', count: statusCounts.approved },
        { label: 'Rejected', value: 'rejected', count: statusCounts.rejected },
        { label: 'Enrolled', value: 'enrolled', count: statusCounts.enrolled },
        { label: 'Completed', value: 'completed', count: statusCounts.completed },
    ];

    return (
        <div className="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
            <div className="flex gap-2 overflow-x-auto pb-2 sm:pb-0">
                {filterOptions.map((filter) => (
                    <button
                        key={filter.value}
                        onClick={() => handleStatusChange(filter.value)}
                        className={`rounded-md px-4 py-2 text-sm font-medium whitespace-nowrap transition-colors ${
                            status === filter.value
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
                    value={search}
                    onChange={(e) => {
                        setSearch(e.target.value);
                        debounced(e.target.value);
                    }}
                    className="border-border bg-card pl-9"
                />
            </div>
        </div>
    );
}
