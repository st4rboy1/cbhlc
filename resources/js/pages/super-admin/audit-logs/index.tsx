import { Head, Link, router } from '@inertiajs/react';
import { format } from 'date-fns';
import { Download, Eye } from 'lucide-react';
import { useState } from 'react';

import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { DatePicker } from '@/components/ui/date-picker';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useToast } from '@/hooks/use-toast';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type PaginatedData, type User } from '@/types';

interface Activity {
    id: number;
    description: string;
    subject_type: string | null;
    subject_id: number | null;
    log_name: string;
    properties: Record<string, unknown>;
    causer: User | null;
    created_at: string;
}

interface Filters {
    causer_id?: string;
    subject_type?: string;
    log_name?: string;
    description?: string;
    date_from?: string;
    date_to?: string;
}

interface Props {
    activities: PaginatedData<Activity>;
    filters: Filters;
    causers: User[];
    subjectTypes: string[];
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Super Admin', href: '/super-admin/dashboard' },
    { title: 'Audit Logs', href: '/super-admin/audit-logs' },
];

function getActionVariant(description: string): 'default' | 'destructive' | 'secondary' | 'outline' {
    const lowerDesc = description.toLowerCase();
    if (lowerDesc.includes('created')) return 'default';
    if (lowerDesc.includes('updated')) return 'secondary';
    if (lowerDesc.includes('deleted')) return 'destructive';
    return 'outline';
}

function formatModelName(modelType: string | null): string {
    if (!modelType) return '';
    const parts = modelType.split('\\');
    return parts[parts.length - 1];
}

export default function AuditLogsIndex({ activities, filters, causers, subjectTypes }: Props) {
    const { toast } = useToast();
    const [localFilters, setLocalFilters] = useState<Filters>(filters);

    const applyFilters = () => {
        router.get('/super-admin/audit-logs', Object.fromEntries(Object.entries(localFilters).filter(([, v]) => v !== '' && v !== undefined)), {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const clearFilters = () => {
        setLocalFilters({});
        router.get('/super-admin/audit-logs', {}, { preserveState: true });
    };

    const handleExport = () => {
        router.post(
            '/super-admin/audit-logs/export',
            Object.fromEntries(Object.entries(localFilters).filter(([, v]) => v !== '' && v !== undefined)),
            {
                preserveState: true,
                onSuccess: () => {
                    toast({
                        title: 'Success',
                        description: 'Audit logs exported successfully',
                    });
                },
                onError: () => {
                    toast({
                        title: 'Error',
                        description: 'Failed to export audit logs',
                        variant: 'destructive',
                    });
                },
            },
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Audit Logs" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <Heading title="Audit Logs" description="View and filter system activity logs" />
                    <Button onClick={handleExport} variant="outline">
                        <Download className="mr-2 h-4 w-4" />
                        Export CSV
                    </Button>
                </div>

                {/* Filters */}
                <Card>
                    <CardHeader>
                        <CardTitle>Filters</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 md:grid-cols-3">
                            <div className="space-y-2">
                                <Label>User</Label>
                                <Select
                                    value={localFilters.causer_id || ''}
                                    onValueChange={(value) => setLocalFilters({ ...localFilters, causer_id: value || undefined })}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="All users" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">All users</SelectItem>
                                        {causers.map((user) => (
                                            <SelectItem key={user.id} value={user.id.toString()}>
                                                {user.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="space-y-2">
                                <Label>Model Type</Label>
                                <Select
                                    value={localFilters.subject_type || ''}
                                    onValueChange={(value) => setLocalFilters({ ...localFilters, subject_type: value || undefined })}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="All models" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">All models</SelectItem>
                                        {subjectTypes.map((type) => (
                                            <SelectItem key={type} value={type}>
                                                {formatModelName(type)}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="space-y-2">
                                <Label>Action</Label>
                                <Input
                                    placeholder="Search actions..."
                                    value={localFilters.description || ''}
                                    onChange={(e) => setLocalFilters({ ...localFilters, description: e.target.value || undefined })}
                                />
                            </div>

                            <div className="space-y-2">
                                <Label>Date From</Label>
                                <DatePicker
                                    date={localFilters.date_from ? new Date(localFilters.date_from) : undefined}
                                    onSelect={(date) =>
                                        setLocalFilters({ ...localFilters, date_from: date ? format(date, 'yyyy-MM-dd') : undefined })
                                    }
                                />
                            </div>

                            <div className="space-y-2">
                                <Label>Date To</Label>
                                <DatePicker
                                    date={localFilters.date_to ? new Date(localFilters.date_to) : undefined}
                                    onSelect={(date) => setLocalFilters({ ...localFilters, date_to: date ? format(date, 'yyyy-MM-dd') : undefined })}
                                />
                            </div>

                            <div className="flex items-end gap-2">
                                <Button onClick={applyFilters} className="flex-1">
                                    Apply Filters
                                </Button>
                                <Button onClick={clearFilters} variant="outline">
                                    Clear
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Activity Table */}
                <Card>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Timestamp</TableHead>
                                <TableHead>User</TableHead>
                                <TableHead>Action</TableHead>
                                <TableHead>Model</TableHead>
                                <TableHead>Log Name</TableHead>
                                <TableHead className="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {activities.data.length === 0 ? (
                                <TableRow>
                                    <TableCell colSpan={6} className="text-center text-muted-foreground">
                                        No activity logs found
                                    </TableCell>
                                </TableRow>
                            ) : (
                                activities.data.map((activity) => (
                                    <TableRow key={activity.id}>
                                        <TableCell className="text-sm">{format(new Date(activity.created_at), 'MMM d, yyyy HH:mm')}</TableCell>
                                        <TableCell>
                                            <div className="flex flex-col">
                                                <span className="font-medium">{activity.causer?.name || 'System'}</span>
                                                {activity.causer?.email && (
                                                    <span className="text-xs text-muted-foreground">{activity.causer.email}</span>
                                                )}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant={getActionVariant(activity.description)}>{activity.description}</Badge>
                                        </TableCell>
                                        <TableCell>
                                            {activity.subject_type && (
                                                <span className="text-sm">
                                                    {formatModelName(activity.subject_type)} #{activity.subject_id}
                                                </span>
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant="outline">{activity.log_name}</Badge>
                                        </TableCell>
                                        <TableCell className="text-right">
                                            <Link href={`/super-admin/audit-logs/${activity.id}`}>
                                                <Button size="sm" variant="ghost">
                                                    <Eye className="h-4 w-4" />
                                                </Button>
                                            </Link>
                                        </TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>
                </Card>

                {/* Pagination */}
                {activities.data.length > 0 && (
                    <div className="flex items-center justify-between">
                        <div className="text-sm text-muted-foreground">
                            Showing {activities.from} to {activities.to} of {activities.total} results
                        </div>
                        <div className="flex gap-2">
                            {activities.links.map((link, index) => (
                                <Button
                                    key={index}
                                    variant={link.active ? 'default' : 'outline'}
                                    size="sm"
                                    disabled={!link.url}
                                    onClick={() => link.url && router.get(link.url)}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
