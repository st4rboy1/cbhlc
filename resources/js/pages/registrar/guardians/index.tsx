import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { ConfirmationDialog } from '@/components/ui/confirmation-dialog';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { Edit, Eye, PlusCircle, Search, Trash } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';

interface Guardian {
    id: number;
    first_name: string;
    middle_name?: string;
    last_name: string;
    email: string;
    phone: string;
    relationship: string;
    emergency_contact: boolean;
    students_count: number;
    created_at: string;
    updated_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    guardians: {
        data: Guardian[];
        links: PaginationLink[];
        current_page: number;
        last_page: number;
        total: number;
    };
    filters: {
        search?: string;
    };
    stats: {
        total: number;
        with_students: number;
        without_students: number;
        emergency_contacts: number;
    };
}

export default function SuperAdminGuardiansIndex({ guardians, filters, stats }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);
    const [guardianToDelete, setGuardianToDelete] = useState<number | null>(null);

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Registrar', href: '/registrar/dashboard' },
        { title: 'Guardians', href: '/registrar/guardians' },
    ];

    const handleSearch = () => {
        router.get(
            '/registrar/guardians',
            {
                search: search || undefined,
            },
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    };

    const openDeleteDialog = (id: number) => {
        setGuardianToDelete(id);
        setDeleteDialogOpen(true);
    };

    const handleDelete = () => {
        if (guardianToDelete === null) return;

        router.delete(`/registrar/guardians/${guardianToDelete}`, {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Guardian deleted successfully');
                setDeleteDialogOpen(false);
                setGuardianToDelete(null);
            },
            onError: () => {
                toast.error('Failed to delete guardian. Guardian may have students.');
            },
        });
    };

    const getFullName = (guardian: Guardian) => {
        const parts = [guardian.first_name];
        if (guardian.middle_name) parts.push(guardian.middle_name);
        parts.push(guardian.last_name);
        return parts.join(' ');
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Guardians" />
            <div className="container mx-auto px-4 py-6">
                <div className="mb-6 flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Guardians</h1>
                    <Link href="/registrar/guardians/create">
                        <Button>
                            <PlusCircle className="mr-2 h-4 w-4" />
                            Add New Guardian
                        </Button>
                    </Link>
                </div>

                {/* Stats Cards */}
                <div className="mb-6 grid gap-4 md:grid-cols-4">
                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Total Guardians</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.total}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">With Students</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.with_students}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Without Students</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.without_students}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Emergency Contacts</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.emergency_contacts}</div>
                        </CardContent>
                    </Card>
                </div>

                {/* Search */}
                <div className="mb-6 flex gap-4">
                    <Input
                        placeholder="Search by name, email, or phone..."
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
                        className="max-w-md"
                    />
                    <Button onClick={handleSearch} variant="secondary">
                        <Search className="mr-2 h-4 w-4" />
                        Search
                    </Button>
                </div>

                {/* Table */}
                <Card>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Email</TableHead>
                                <TableHead>Phone</TableHead>
                                <TableHead>Relationship</TableHead>
                                <TableHead className="text-center"># of Students</TableHead>
                                <TableHead className="text-center">Emergency Contact</TableHead>
                                <TableHead className="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {guardians.data.length === 0 ? (
                                <TableRow>
                                    <TableCell colSpan={7} className="text-center text-muted-foreground">
                                        No guardians found.
                                    </TableCell>
                                </TableRow>
                            ) : (
                                guardians.data.map((guardian) => (
                                    <TableRow key={guardian.id}>
                                        <TableCell className="font-medium">{getFullName(guardian)}</TableCell>
                                        <TableCell>{guardian.email}</TableCell>
                                        <TableCell>{guardian.phone}</TableCell>
                                        <TableCell className="capitalize">{guardian.relationship.replace('_', ' ')}</TableCell>
                                        <TableCell className="text-center">{guardian.students_count}</TableCell>
                                        <TableCell className="text-center">
                                            {guardian.emergency_contact && <Badge variant="default">Yes</Badge>}
                                        </TableCell>
                                        <TableCell className="text-right">
                                            <div className="flex justify-end gap-2">
                                                <Link href={`/registrar/guardians/${guardian.id}`}>
                                                    <Button size="sm" variant="outline">
                                                        <Eye className="h-4 w-4" />
                                                    </Button>
                                                </Link>
                                                <Link href={`/registrar/guardians/${guardian.id}/edit`}>
                                                    <Button size="sm" variant="outline">
                                                        <Edit className="h-4 w-4" />
                                                    </Button>
                                                </Link>
                                                <Button size="sm" variant="destructive" onClick={() => openDeleteDialog(guardian.id)}>
                                                    <Trash className="mr-2 h-4 w-4" />
                                                    Delete
                                                </Button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>
                </Card>

                {/* Pagination */}
                {guardians.last_page > 1 && (
                    <div className="mt-4 flex justify-center gap-2">
                        {guardians.links.map((link, index) => (
                            <Link
                                key={index}
                                href={link.url || '#'}
                                preserveState
                                preserveScroll
                                className={`rounded px-3 py-1 ${
                                    link.active
                                        ? 'bg-primary text-primary-foreground'
                                        : link.url
                                          ? 'bg-secondary text-secondary-foreground hover:bg-secondary/80'
                                          : 'cursor-not-allowed bg-muted text-muted-foreground'
                                }`}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        ))}
                    </div>
                )}

                <ConfirmationDialog
                    open={deleteDialogOpen}
                    onOpenChange={setDeleteDialogOpen}
                    onConfirm={handleDelete}
                    title="Delete Guardian?"
                    description="Are you sure you want to delete this guardian? This action cannot be undone."
                    confirmText="Delete"
                    variant="destructive"
                />
            </div>
        </AppLayout>
    );
}
