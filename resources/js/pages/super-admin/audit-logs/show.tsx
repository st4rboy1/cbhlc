import { Head, Link } from '@inertiajs/react';
import { format } from 'date-fns';
import { ArrowLeft, Clock, Eye, User } from 'lucide-react';

import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type User as UserType } from '@/types';

interface Activity {
    id: number;
    description: string;
    subject_type: string | null;
    subject_id: number | null;
    log_name: string;
    properties: {
        attributes?: Record<string, unknown>;
        old?: Record<string, unknown>;
        [key: string]: unknown;
    };
    causer: UserType | null;
    created_at: string;
}

interface Props {
    activity: Activity;
    relatedActivities: Activity[];
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Super Admin', href: '/super-admin/dashboard' },
    { title: 'Audit Logs', href: '/super-admin/audit-logs' },
    { title: 'Activity Details', href: '#' },
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

function formatPropertyValue(value: unknown): string {
    if (value === null || value === undefined) return 'null';
    if (typeof value === 'boolean') return value ? 'true' : 'false';
    if (typeof value === 'object') return JSON.stringify(value, null, 2);
    return String(value);
}

export default function AuditLogsShow({ activity, relatedActivities }: Props) {
    const hasChanges = activity.properties.old && activity.properties.attributes;
    const changedFields = hasChanges
        ? Object.keys(activity.properties.attributes || {}).filter(
              (key) => JSON.stringify(activity.properties.attributes?.[key]) !== JSON.stringify(activity.properties.old?.[key]),
          )
        : [];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Activity #${activity.id}`} />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <Heading title="Activity Details" description={`Viewing activity log #${activity.id}`} />
                    <Link href="/super-admin/audit-logs">
                        <Button variant="outline">
                            <ArrowLeft className="mr-2 h-4 w-4" />
                            Back to List
                        </Button>
                    </Link>
                </div>

                {/* Main Activity Details */}
                <Card>
                    <CardHeader>
                        <div className="flex items-start justify-between">
                            <div>
                                <CardTitle>Activity Information</CardTitle>
                                <p className="mt-1 text-sm text-muted-foreground">
                                    {format(new Date(activity.created_at), "MMMM d, yyyy 'at' h:mm:ss a")}
                                </p>
                            </div>
                            <Badge variant={getActionVariant(activity.description)}>{activity.description}</Badge>
                        </div>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="grid gap-4 md:grid-cols-2">
                            <div>
                                <h4 className="mb-2 flex items-center text-sm font-medium">
                                    <User className="mr-2 h-4 w-4" />
                                    User
                                </h4>
                                <div className="rounded-md border p-3">
                                    <p className="font-medium">{activity.causer?.name || 'System'}</p>
                                    {activity.causer?.email && <p className="text-sm text-muted-foreground">{activity.causer.email}</p>}
                                </div>
                            </div>

                            <div>
                                <h4 className="mb-2 flex items-center text-sm font-medium">
                                    <Eye className="mr-2 h-4 w-4" />
                                    Subject
                                </h4>
                                <div className="rounded-md border p-3">
                                    {activity.subject_type ? (
                                        <>
                                            <p className="font-medium">{formatModelName(activity.subject_type)}</p>
                                            <p className="text-sm text-muted-foreground">ID: #{activity.subject_id}</p>
                                        </>
                                    ) : (
                                        <p className="text-muted-foreground">No subject</p>
                                    )}
                                </div>
                            </div>

                            <div>
                                <h4 className="mb-2 flex items-center text-sm font-medium">
                                    <Clock className="mr-2 h-4 w-4" />
                                    Log Name
                                </h4>
                                <div className="rounded-md border p-3">
                                    <Badge variant="outline">{activity.log_name}</Badge>
                                </div>
                            </div>

                            <div>
                                <h4 className="mb-2 text-sm font-medium">Activity ID</h4>
                                <div className="rounded-md border p-3">
                                    <p className="font-mono text-sm">#{activity.id}</p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Changes Comparison */}
                {hasChanges && changedFields.length > 0 && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Changes</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {changedFields.map((field) => (
                                    <div key={field} className="rounded-md border">
                                        <div className="border-b bg-muted px-4 py-2">
                                            <h5 className="font-mono text-sm font-medium">{field}</h5>
                                        </div>
                                        <div className="grid md:grid-cols-2">
                                            <div className="border-r p-4">
                                                <p className="mb-2 text-xs font-medium text-muted-foreground">BEFORE</p>
                                                <pre className="overflow-x-auto rounded bg-muted p-2 text-xs">
                                                    {formatPropertyValue(activity.properties.old?.[field])}
                                                </pre>
                                            </div>
                                            <div className="p-4">
                                                <p className="mb-2 text-xs font-medium text-muted-foreground">AFTER</p>
                                                <pre className="overflow-x-auto rounded bg-muted p-2 text-xs">
                                                    {formatPropertyValue(activity.properties.attributes?.[field])}
                                                </pre>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Full Properties */}
                {!hasChanges && Object.keys(activity.properties).length > 0 && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Properties</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <pre className="overflow-x-auto rounded-md bg-muted p-4 text-xs">{JSON.stringify(activity.properties, null, 2)}</pre>
                        </CardContent>
                    </Card>
                )}

                {/* Related Activities */}
                {relatedActivities.length > 0 && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Related Activities</CardTitle>
                            <p className="text-sm text-muted-foreground">
                                Recent activities on the same {formatModelName(activity.subject_type)} (
                                {activity.subject_type ? `#${activity.subject_id}` : ''})
                            </p>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Timestamp</TableHead>
                                        <TableHead>User</TableHead>
                                        <TableHead>Action</TableHead>
                                        <TableHead className="text-right">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {relatedActivities.map((relatedActivity) => (
                                        <TableRow key={relatedActivity.id}>
                                            <TableCell className="text-sm">
                                                {format(new Date(relatedActivity.created_at), 'MMM d, yyyy HH:mm')}
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex flex-col">
                                                    <span className="font-medium">{relatedActivity.causer?.name || 'System'}</span>
                                                    {relatedActivity.causer?.email && (
                                                        <span className="text-xs text-muted-foreground">{relatedActivity.causer.email}</span>
                                                    )}
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <Badge variant={getActionVariant(relatedActivity.description)}>{relatedActivity.description}</Badge>
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <Link href={`/super-admin/audit-logs/${relatedActivity.id}`}>
                                                    <Button size="sm" variant="ghost">
                                                        <Eye className="h-4 w-4" />
                                                    </Button>
                                                </Link>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                )}

                {relatedActivities.length === 0 && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Related Activities</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p className="text-center text-sm text-muted-foreground">No related activities found</p>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}
