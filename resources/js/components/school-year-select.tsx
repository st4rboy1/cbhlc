import { FormControl, FormDescription, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { Control, FieldPath, FieldValues } from 'react-hook-form';

interface SchoolYear {
    id: number;
    name: string;
    status: string;
    is_active: boolean;
}

interface SchoolYearSelectProps<TFieldValues extends FieldValues> {
    control: Control<TFieldValues>;
    name: FieldPath<TFieldValues>;
    label?: string;
    description?: string;
    schoolYears: SchoolYear[];
    disabled?: boolean;
    required?: boolean;
}

export function SchoolYearSelect<TFieldValues extends FieldValues>({
    control,
    name,
    label = 'School Year',
    description,
    schoolYears,
    disabled = false,
    required = false,
}: SchoolYearSelectProps<TFieldValues>) {
    // Filter to show only active and upcoming school years
    const availableSchoolYears = schoolYears.filter((sy) => sy.status === 'active' || sy.status === 'upcoming');

    // Find the active school year for default selection
    const activeSchoolYear = schoolYears.find((sy) => sy.is_active);

    return (
        <FormField
            control={control}
            name={name}
            render={({ field }) => (
                <FormItem>
                    <FormLabel>
                        {label}
                        {required && <span className="text-destructive"> *</span>}
                    </FormLabel>
                    <Select
                        onValueChange={(value) => field.onChange(Number(value))}
                        value={field.value?.toString()}
                        disabled={disabled}
                        defaultValue={activeSchoolYear?.id.toString()}
                    >
                        <FormControl>
                            <SelectTrigger>
                                <SelectValue placeholder="Select school year" />
                            </SelectTrigger>
                        </FormControl>
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
                    {description && <FormDescription>{description}</FormDescription>}
                    <FormMessage />
                </FormItem>
            )}
        />
    );
}
