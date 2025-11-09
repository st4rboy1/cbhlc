import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { Head, useForm } from '@inertiajs/react';
import { useEffect } from 'react';
import { toast } from 'sonner';

export default function SettingCreate() {
    const { data, setData, post, processing, errors, wasSuccessful } = useForm({
        key: '',
        value: '',
    });

    useEffect(() => {
        if (wasSuccessful) toast.success('Setting created successfully.');
    }, [wasSuccessful]);

    return (
        <AppLayout
            breadcrumbs={[
                { title: 'Admin', href: '/admin/dashboard' },
                { title: 'Settings', href: '/admin/settings' },
                { title: 'Create', href: '#' },
            ]}
        >
            <Head title="Create Setting" />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">Create Setting</h1>
                <Card className="mx-auto max-w-2xl">
                    <CardHeader>
                        <CardTitle>Setting Information</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form
                            onSubmit={(e) => {
                                e.preventDefault();
                                post(route('admin.settings.store'));
                            }}
                            className="space-y-4"
                        >
                            <div>
                                <Label htmlFor="key">Setting Key</Label>
                                <Input
                                    id="key"
                                    type="text"
                                    value={data.key}
                                    onChange={(e) => setData('key', e.target.value)}
                                    className={errors.key ? 'border-red-500' : ''}
                                    placeholder="e.g., school_name, payment_location"
                                />
                                {errors.key && <p className="text-sm text-red-500">{errors.key}</p>}
                                <p className="mt-1 text-xs text-muted-foreground">Use lowercase with underscores (e.g., payment_hours)</p>
                            </div>

                            <div>
                                <Label htmlFor="value">Setting Value</Label>
                                <Textarea
                                    id="value"
                                    value={data.value}
                                    onChange={(e) => setData('value', e.target.value)}
                                    className={errors.value ? 'border-red-500' : ''}
                                    placeholder="Enter the setting value..."
                                    rows={5}
                                />
                                {errors.value && <p className="text-sm text-red-500">{errors.value}</p>}
                            </div>

                            <div className="flex gap-4">
                                <Button type="submit" disabled={processing}>
                                    Create Setting
                                </Button>
                                <Button type="button" variant="outline" onClick={() => window.history.back()}>
                                    Cancel
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
