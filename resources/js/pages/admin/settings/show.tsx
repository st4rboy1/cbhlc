import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Calendar, Key } from 'lucide-react';

interface Setting {
    id: number;
    key: string;
    value: string | null;
    created_at: string;
    updated_at: string;
}

interface Props {
    setting: Setting;
}

export default function SettingShow({ setting }: Props) {
    const handleDelete = () => {
        if (confirm('Are you sure you want to delete this setting? This action cannot be undone.')) {
            router.delete(`/admin/settings/${setting.id}`);
        }
    };

    return (
        <AppLayout
            breadcrumbs={[
                { title: 'Admin', href: '/admin/dashboard' },
                { title: 'Settings', href: '/admin/settings' },
                { title: setting.key, href: '#' },
            ]}
        >
            <Head title={setting.key} />
            <div className="px-4 py-6">
                <div className="mb-6 flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Link href="/admin/settings">
                            <Button variant="outline" size="sm">
                                <ArrowLeft className="mr-2 h-4 w-4" />
                                Back to Settings
                            </Button>
                        </Link>
                        <div>
                            <h1 className="font-mono text-2xl font-bold">{setting.key}</h1>
                            <p className="text-sm text-muted-foreground">System Setting</p>
                        </div>
                    </div>
                    <div className="flex gap-2">
                        <Link href={`/admin/settings/${setting.id}/edit`}>
                            <Button variant="outline">Edit Setting</Button>
                        </Link>
                        <Button variant="destructive" onClick={handleDelete}>
                            Delete Setting
                        </Button>
                    </div>
                </div>

                <div className="grid gap-4 md:grid-cols-2">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Setting Key</CardTitle>
                            <Key className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="font-mono text-2xl font-bold">{setting.key}</div>
                            <p className="text-xs text-muted-foreground">Configuration identifier</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Last Updated</CardTitle>
                            <Calendar className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{new Date(setting.updated_at).toLocaleDateString()}</div>
                            <p className="text-xs text-muted-foreground">{new Date(setting.updated_at).toLocaleTimeString()}</p>
                        </CardContent>
                    </Card>
                </div>

                <Card className="mt-4">
                    <CardHeader>
                        <CardTitle>Setting Value</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {setting.value ? (
                            <div className="rounded-md bg-muted p-4">
                                <p className="text-sm whitespace-pre-wrap">{setting.value}</p>
                            </div>
                        ) : (
                            <Badge variant="outline" className="text-muted-foreground">
                                No value set
                            </Badge>
                        )}
                    </CardContent>
                </Card>

                <Card className="mt-4">
                    <CardHeader>
                        <CardTitle>Metadata</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-2">
                        <div>
                            <span className="font-medium">Created:</span> {new Date(setting.created_at).toLocaleString()}
                        </div>
                        <div>
                            <span className="font-medium">Last Modified:</span> {new Date(setting.updated_at).toLocaleString()}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
