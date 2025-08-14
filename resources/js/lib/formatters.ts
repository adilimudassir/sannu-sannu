/**
 * Utility functions for formatting currency, dates, and other common data types
 */

/**
 * Format currency amounts in Nigerian Naira (NGN)
 * @param amount - The amount to format
 * @param locale - The locale to use for formatting (defaults to 'en-NG')
 * @returns Formatted currency string
 */
export function formatCurrency(amount: number, locale: string = 'en-NG'): string {
    return new Intl.NumberFormat(locale, {
        style: 'currency',
        currency: 'NGN',
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(amount);
}

/**
 * Format date strings in a consistent format
 * @param dateString - The date string to format
 * @param options - Intl.DateTimeFormatOptions for customization
 * @returns Formatted date string
 */
export function formatDate(
    dateString: string, 
    options: Intl.DateTimeFormatOptions = {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    }
): string {
    return new Date(dateString).toLocaleDateString('en-NG', options);
}

/**
 * Format date strings in a short format (for compact displays)
 * @param dateString - The date string to format
 * @returns Formatted date string in short format
 */
export function formatDateShort(dateString: string): string {
    return new Date(dateString).toLocaleDateString('en-NG', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

/**
 * Format numbers with appropriate thousand separators
 * @param num - The number to format
 * @param locale - The locale to use for formatting (defaults to 'en-NG')
 * @returns Formatted number string
 */
export function formatNumber(num: number, locale: string = 'en-NG'): string {
    return new Intl.NumberFormat(locale).format(num);
}

/**
 * Format percentage values
 * @param value - The percentage value (0-100)
 * @param decimals - Number of decimal places (defaults to 1)
 * @returns Formatted percentage string
 */
export function formatPercentage(value: number, decimals: number = 1): string {
    return `${value.toFixed(decimals)}%`;
}

/**
 * Truncate text to a specified length with ellipsis
 * @param text - The text to truncate
 * @param maxLength - Maximum length before truncation
 * @returns Truncated text with ellipsis if needed
 */
export function truncateText(text: string, maxLength: number): string {
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength).trim() + '...';
}

/**
 * Format file sizes in human-readable format
 * @param bytes - Size in bytes
 * @returns Formatted file size string
 */
export function formatFileSize(bytes: number): string {
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    if (bytes === 0) return '0 Bytes';
    
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    const size = bytes / Math.pow(1024, i);
    
    return `${size.toFixed(i === 0 ? 0 : 1)} ${sizes[i]}`;
}

/**
 * Calculate and format time remaining until a date
 * @param endDate - The end date string
 * @returns Formatted time remaining string
 */
export function formatTimeRemaining(endDate: string): string {
    const now = new Date();
    const end = new Date(endDate);
    const diffTime = end.getTime() - now.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    if (diffDays < 0) return 'Ended';
    if (diffDays === 0) return 'Ends today';
    if (diffDays === 1) return '1 day left';
    if (diffDays < 30) return `${diffDays} days left`;
    
    const diffMonths = Math.floor(diffDays / 30);
    if (diffMonths === 1) return '1 month left';
    if (diffMonths < 12) return `${diffMonths} months left`;
    
    const diffYears = Math.floor(diffMonths / 12);
    return diffYears === 1 ? '1 year left' : `${diffYears} years left`;
}