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
}: DatePickerProps) {
    const [open, setOpen] = React.useState(false);

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
                    disabled={(date) => date > new Date() || date < new Date('1900-01-01')}
                />
            </PopoverContent>
        </Popover>
    );
}