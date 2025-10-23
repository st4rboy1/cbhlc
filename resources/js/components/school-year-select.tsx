import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

interface SchoolYear {
    id: number;
    name: string;
    status: string;
    is_active: boolean;
}

interface SchoolYearSelectProps {
    value: string | number;
    onChange: (value: string) => void;
    schoolYears: SchoolYear[];
    label?: string;
    error?: string;
    disabled?: boolean;
    required?: boolean;
}

export function SchoolYearSelect({
    value,
    onChange,
    schoolYears,
    label = 'School Year',
    error,
    disabled = false,
    required = false,
}: SchoolYearSelectProps) {
    // Filter to show only active and upcoming school years
    const availableSchoolYears = schoolYears.filter((sy) => sy.status === 'active' || sy.status === 'upcoming');

    // Find the active school year for default selection
    const activeSchoolYear = schoolYears.find((sy) => sy.is_active);

    return (
        <div className="space-y-2">
            <Label>
                {label}
                {required && <span className="text-destructive"> *</span>}
            </Label>
            <Select value={value?.toString() || activeSchoolYear?.id.toString()} onValueChange={onChange} disabled={disabled}>
                <SelectTrigger className={error ? 'border-destructive' : ''}>
                    <SelectValue placeholder="Select school year" />
                </SelectTrigger>
                <SelectContent>
                    {availableSchoolYears.map((schoolYear) => (
                        <SelectItem key={schoolYear.id} value={schoolYear.id.toString()}>
                            {schoolYear.name}
                            {schoolYear.is_active && ' (Current)'}
                            {schoolYear.status === 'upcoming' && ' (Upcoming)'}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
            {error && <p className="text-sm text-destructive">{error}</p>}
        </div>
    );
}
