import { SchoolYearSelect } from '@/components/school-year-select';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';

interface GradeLevelFee {
    id: number;
    grade_level: string;
    school_year: string;
    school_year_id: number;
    tuition_fee_cents: number;
    miscellaneous_fee_cents: number;
    laboratory_fee_cents: number;
    library_fee_cents: number;
    sports_fee_cents: number;
    payment_terms: string;
    is_active: boolean;
}

interface FormData {
    grade_level: string;
    school_year_id: string;
    tuition_fee: string;
    miscellaneous_fee: string;
    laboratory_fee: string;
    library_fee: string;
    sports_fee: string;
    payment_terms: string;
    is_active: boolean;
}

interface SchoolYear {
    id: number;
    name: string;
    status: string;
    is_active: boolean;
}

interface Props {
    fee: GradeLevelFee;
    gradeLevels?: string[];
    schoolYears: SchoolYear[];
}

export default function SuperAdminGradeLevelFeesEdit({ fee, gradeLevels = [], schoolYears }: Props) {
    const { data, setData, put, processing, errors } = useForm<FormData>({
        grade_level: fee.grade_level,
        school_year_id: fee.school_year_id?.toString() || '',
        tuition_fee: (fee.tuition_fee_cents / 100).toFixed(2),
        miscellaneous_fee: (fee.miscellaneous_fee_cents / 100).toFixed(2),
        laboratory_fee: (fee.laboratory_fee_cents / 100).toFixed(2),
        library_fee: (fee.library_fee_cents / 100).toFixed(2),
        sports_fee: (fee.sports_fee_cents / 100).toFixed(2),
        payment_terms: fee.payment_terms,
        is_active: fee.is_active,
    });

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Super Admin', href: '/super-admin/dashboard' },
        { title: 'Grade Levelfees', href: '/super-admin/grade-level-fees' },
    ];

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/super-admin/grade-level-fees/${fee.id}`);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Edit Grade Level Fee" />
            <div className="container mx-auto max-w-3xl px-4 py-6">
                <div className="mb-6 flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Edit Grade Level Fee</h1>
                    <Link href="/super-admin/grade-level-fees">
                        <Button variant="outline">
                            <ArrowLeft className="mr-2 h-4 w-4" />
                            Back to List
                        </Button>
                    </Link>
                </div>

                <form onSubmit={handleSubmit}>
                    <Card>
                        <CardHeader>
                            <CardTitle>Fee Details</CardTitle>
                            <CardDescription>
                                Update the fee structure for {fee.grade_level} - {fee.school_year}.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="grade_level">Grade Level</Label>
                                    <Select value={data.grade_level} onValueChange={(value) => setData('grade_level', value)}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select grade level" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {gradeLevels.map((level) => (
                                                <SelectItem key={level} value={level}>
                                                    {level}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.grade_level && <p className="text-sm text-red-600">{errors.grade_level}</p>}
                                </div>

                                {/* School Year */}
                                <SchoolYearSelect
                                    value={data.school_year_id}
                                    onChange={(value) => setData('school_year_id', value)}
                                    schoolYears={schoolYears}
                                    error={errors.school_year_id}
                                    required
                                />
                            </div>

                            <div className="grid gap-4 md:grid-cols-3">
                                <div className="space-y-2">
                                    <Label htmlFor="tuition_fee">Tuition Fee</Label>
                                    <Input
                                        id="tuition_fee"
                                        type="number"
                                        step="0.01"
                                        placeholder="0.00"
                                        value={data.tuition_fee}
                                        onChange={(e) => setData('tuition_fee', e.target.value)}
                                    />
                                    {errors.tuition_fee && <p className="text-sm text-red-600">{errors.tuition_fee}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="miscellaneous_fee">Miscellaneous Fee</Label>
                                    <Input
                                        id="miscellaneous_fee"
                                        type="number"
                                        step="0.01"
                                        placeholder="0.00"
                                        value={data.miscellaneous_fee}
                                        onChange={(e) => setData('miscellaneous_fee', e.target.value)}
                                    />
                                    {errors.miscellaneous_fee && <p className="text-sm text-red-600">{errors.miscellaneous_fee}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="laboratory_fee">Laboratory Fee</Label>
                                    <Input
                                        id="laboratory_fee"
                                        type="number"
                                        step="0.01"
                                        placeholder="0.00"
                                        value={data.laboratory_fee}
                                        onChange={(e) => setData('laboratory_fee', e.target.value)}
                                    />
                                    {errors.laboratory_fee && <p className="text-sm text-red-600">{errors.laboratory_fee}</p>}
                                </div>
                            </div>

                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="library_fee">Library Fee</Label>
                                    <Input
                                        id="library_fee"
                                        type="number"
                                        step="0.01"
                                        placeholder="0.00"
                                        value={data.library_fee}
                                        onChange={(e) => setData('library_fee', e.target.value)}
                                    />
                                    {errors.library_fee && <p className="text-sm text-red-600">{errors.library_fee}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="sports_fee">Sports Fee</Label>
                                    <Input
                                        id="sports_fee"
                                        type="number"
                                        step="0.01"
                                        placeholder="0.00"
                                        value={data.sports_fee}
                                        onChange={(e) => setData('sports_fee', e.target.value)}
                                    />
                                    {errors.sports_fee && <p className="text-sm text-red-600">{errors.sports_fee}</p>}
                                </div>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="payment_terms">Payment Terms</Label>
                                <Select value={data.payment_terms} onValueChange={(value) => setData('payment_terms', value)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select payment terms" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="ANNUAL">Annual</SelectItem>
                                        <SelectItem value="SEMESTRAL">Semestral</SelectItem>
                                        <SelectItem value="QUARTERLY">Quarterly</SelectItem>
                                        <SelectItem value="MONTHLY">Monthly</SelectItem>
                                    </SelectContent>
                                </Select>
                                {errors.payment_terms && <p className="text-sm text-red-600">{errors.payment_terms}</p>}
                            </div>

                            <div className="flex items-center space-x-2">
                                <Checkbox
                                    id="is_active"
                                    checked={data.is_active}
                                    onCheckedChange={(checked: boolean) => setData('is_active', checked)}
                                />
                                <Label htmlFor="is_active" className="cursor-pointer">
                                    Active
                                </Label>
                            </div>
                        </CardContent>
                    </Card>

                    <div className="mt-6 flex justify-end gap-4">
                        <Link href="/super-admin/grade-level-fees">
                            <Button type="button" variant="outline">
                                Cancel
                            </Button>
                        </Link>
                        <Button type="submit" disabled={processing}>
                            <Save className="mr-2 h-4 w-4" />
                            Update Fee Structure
                        </Button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
