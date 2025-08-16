/**
 * Utility functions for date formatting and manipulation
 */

/**
 * Format a date string for HTML date input (YYYY-MM-DD format)
 * @param dateString - Date string from backend (can be ISO string, timestamp, etc.)
 * @returns Formatted date string for HTML date input or empty string if invalid
 */
export const formatDateForInput = (dateString: string | null | undefined): string => {
    if (!dateString) return '';
    
    try {
        const date = new Date(dateString);
        
        // Check if date is valid
        if (isNaN(date.getTime())) {
            console.warn('Invalid date format:', dateString);
            return '';
        }
        
        return date.toISOString().split('T')[0];
    } catch (error) {
        console.warn('Error formatting date for input:', dateString, error);
        return '';
    }
};

/**
 * Format a date string for display (human-readable format)
 * @param dateString - Date string from backend
 * @param options - Intl.DateTimeFormat options
 * @returns Formatted date string for display
 */
export const formatDateForDisplay = (
    dateString: string | null | undefined,
    options: Intl.DateTimeFormatOptions = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    }
): string => {
    if (!dateString) return '';
    
    try {
        const date = new Date(dateString);
        
        if (isNaN(date.getTime())) {
            return '';
        }
        
        return date.toLocaleDateString('en-US', options);
    } catch (error) {
        console.warn('Error formatting date for display:', dateString, error);
        return '';
    }
};

/**
 * Check if a date string represents a valid date
 * @param dateString - Date string to validate
 * @returns True if valid date, false otherwise
 */
export const isValidDate = (dateString: string | null | undefined): boolean => {
    if (!dateString) return false;
    
    try {
        const date = new Date(dateString);
        return !isNaN(date.getTime());
    } catch (error) {
        return false;
    }
};

/**
 * Get today's date in YYYY-MM-DD format for HTML date inputs
 * @returns Today's date formatted for HTML date input
 */
export const getTodayForInput = (): string => {
    return new Date().toISOString().split('T')[0];
};

/**
 * Compare two date strings
 * @param date1 - First date string
 * @param date2 - Second date string
 * @returns -1 if date1 < date2, 0 if equal, 1 if date1 > date2, null if either is invalid
 */
export const compareDates = (
    date1: string | null | undefined, 
    date2: string | null | undefined
): number | null => {
    if (!isValidDate(date1) || !isValidDate(date2)) {
        return null;
    }
    
    const d1 = new Date(date1!).getTime();
    const d2 = new Date(date2!).getTime();
    
    if (d1 < d2) return -1;
    if (d1 > d2) return 1;
    return 0;
};