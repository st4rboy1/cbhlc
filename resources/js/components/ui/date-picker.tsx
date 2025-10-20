import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import { format } from 'date-fns';
import { Calendar as CalendarIcon } from 'lucide-react';
import * as React from 'react';

interface DatePickerProps {
    value?: Date;
    onChange?: (date: Date | undefined) => void;
    placeholder?: string;
    className?: string;
    disabled?: boolean;
    error?: boolean;
    id?: string;
    name?: string;
    minAge?: number;
    maxAge?: number;
}

export function DatePicker({
    value,
    onChange,
    placeholder = 'Pick a date',
    className,
    disabled = false,
    error = false,
    id,
    name,
    minAge,
    maxAge,
}: DatePickerProps) {
    const [open, setOpen] = React.useState(false);

    const getDisabledDates = (date: Date) => {
        const today = new Date();
        const minDate = new Date('1900-01-01');

        // Future dates are always disabled
        if (date > today) return true;

        // Dates before 1900 are disabled
        if (date < minDate) return true;

        // If minAge is set, disable dates that would make the person younger than minAge
        if (minAge !== undefined) {
            const maxBirthDate = new Date();
            maxBirthDate.setFullYear(maxBirthDate.getFullYear() - minAge);
            if (date > maxBirthDate) return true;
        }

        // If maxAge is set, disable dates that would make the person older than maxAge
        if (maxAge !== undefined) {
            const minBirthDate = new Date();
            minBirthDate.setFullYear(minBirthDate.getFullYear() - maxAge);
            if (date < minBirthDate) return true;
        }

        return false;
    };

    return (
        <Popover open={open} onOpenChange={setOpen}>
            <PopoverTrigger asChild>
                <Button
                    id={id}
                    name={name}
                    variant="outline"
                    disabled={disabled}
                    className={cn(
                        'w-full justify-start text-left font-normal',
                        !value && 'text-muted-foreground',
                        error && 'border-red-500',
                        className
                    )}
                >
                    <CalendarIcon className="mr-2 h-4 w-4" />
                    {value ? format(value, 'PPP') : <span>{placeholder}</span>}
                </Button>
            </PopoverTrigger>
            <PopoverContent className="w-auto p-0">
                <Calendar
                    mode="single"
                    selected={value}
                    onSelect={(date) => {
                        onChange?.(date);
                        setOpen(false);
                    }}
                    initialFocus
                    disabled={getDisabledDates}
                    defaultMonth={minAge ? new Date(new Date().setFullYear(new Date().getFullYear() - minAge)) : undefined}
                />
            </PopoverContent>
        </Popover>
    );
}