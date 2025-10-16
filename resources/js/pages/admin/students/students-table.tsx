import { useState } from 'react';

type Student = {
    id: number;
    name: string;
    grade: string;
    status: string;
};

type StudentsTableProps = {
    students: Student[];
};

export function StudentsTable({ students }: StudentsTableProps) {
    const [sortKey, setSortKey] = useState<keyof Student>('id');
    const [sortOrder, setSortOrder] = useState<'asc' | 'desc'>('asc');

    const handleSort = (key: keyof Student) => {
        if (sortKey === key) {
            setSortOrder(sortOrder === 'asc' ? 'desc' : 'asc');
        } else {
            setSortKey(key);
            setSortOrder('asc');
        }
    };

    const sortedStudents = [...students].sort((a, b) => {
        const aValue = a[sortKey];
        const bValue = b[sortKey];

        if (aValue < bValue) return sortOrder === 'asc' ? -1 : 1;
        if (aValue > bValue) return sortOrder === 'asc' ? 1 : -1;
        return 0;
    });

    return (
        <div className="overflow-hidden rounded-lg border border-border">
            <table className="w-full">
                <thead>
                    <tr className="border-b border-border bg-muted/50">
                        <th
                            className="cursor-pointer px-4 py-3 text-left text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
                            onClick={() => handleSort('id')}
                        >
                            ID {sortKey === 'id' && (sortOrder === 'asc' ? '↑' : '↓')}
                        </th>
                        <th
                            className="cursor-pointer px-4 py-3 text-left text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
                            onClick={() => handleSort('name')}
                        >
                            Name {sortKey === 'name' && (sortOrder === 'asc' ? '↑' : '↓')}
                        </th>
                        <th
                            className="cursor-pointer px-4 py-3 text-left text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
                            onClick={() => handleSort('grade')}
                        >
                            Grade {sortKey === 'grade' && (sortOrder === 'asc' ? '↑' : '↓')}
                        </th>
                        <th
                            className="cursor-pointer px-4 py-3 text-left text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
                            onClick={() => handleSort('status')}
                        >
                            Status {sortKey === 'status' && (sortOrder === 'asc' ? '↑' : '↓')}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    {sortedStudents.map((student, index) => (
                        <tr
                            key={student.id}
                            className={`border-b border-border transition-colors last:border-0 hover:bg-muted/30 ${
                                index % 2 === 0 ? 'bg-background' : 'bg-muted/10'
                            }`}
                        >
                            <td className="px-4 py-3 text-sm text-foreground">{student.id}</td>
                            <td className="px-4 py-3 text-sm font-medium text-foreground">{student.name}</td>
                            <td className="px-4 py-3 text-sm text-foreground">{student.grade}</td>
                            <td className="px-4 py-3">
                                <span
                                    className={`inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ${
                                        student.status === 'active' ? 'bg-chart-2/10 text-chart-2' : 'bg-muted text-muted-foreground'
                                    }`}
                                >
                                    {student.status}
                                </span>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
