import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

interface SchoolYear {
    id: number;
    name: string;
    status: string;
}

interface SchoolYearFilterProps {
    value: string;
    onChange: (value: string) => void;
    schoolYears: SchoolYear[];
    placeholder?: string;
    showAllOption?: boolean;
}

export function SchoolYearFilter({ value, onChange, schoolYears, placeholder = 'All School Years', showAllOption = true }: SchoolYearFilterProps) {
    return (
        <Select value={value} onValueChange={onChange}>
            <SelectTrigger>
                <SelectValue placeholder={placeholder} />
            </SelectTrigger>
            <SelectContent>
                {showAllOption && <SelectItem value="all">All School Years</SelectItem>}
                {schoolYears.map((sy) => (
                    <SelectItem key={sy.id} value={sy.id.toString()}>
                        S.Y. {sy.name}
                        {sy.status === 'active' && <span className="ml-2 text-xs text-green-600">(Active)</span>}
                    </SelectItem>
                ))}
            </SelectContent>
        </Select>
    );
}
