import { type ClassValue, clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export const addOneMonth = (dateString: string): string => {
        const date = new Date(dateString);
        date.setMonth(date.getMonth() + 1);
        return date.toISOString().split('T')[0];
    };
