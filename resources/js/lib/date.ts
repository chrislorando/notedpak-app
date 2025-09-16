import { DateValue, parseDate } from '@internationalized/date';

export function dateValueToString(dateValue: DateValue | undefined): string | null {
    return dateValue ? `${dateValue.year}-${String(dateValue.month).padStart(2, '0')}-${String(dateValue.day).padStart(2, '0')}` : null;
}

export function stringToDateValue(dateString: string | null): DateValue | undefined {
    return dateString ? parseDate(dateString) : undefined;
}
