/**
 * Format a price value with commas and decimal places
 * @param {string|number} price - The price value to format
 * @returns {string} Formatted price string (e.g., "10,000.00")
 */
export const formatPrice = (price) => {
    if (!price) return '0.00';
    const numericPrice = parseFloat(price);
    return numericPrice.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
};

/**
 * Format a number with commas (for general number formatting)
 * @param {string|number} value - The value to format
 * @returns {string} Formatted number string (e.g., "10,000")
 */
export const formatNumber = (value) => {
    if (!value) return '0';
    const numericValue = parseFloat(value);
    return numericValue.toLocaleString('en-US');
};

/**
 * Format number with commas as user types (for input fields)
 * @param {string} value - The input value to format
 * @returns {string} Formatted number string with commas (e.g., "10,000")
 */
export const formatNumberWithCommas = (value) => {
    // Remove all non-numeric characters except decimal point
    const numericValue = value.replace(/[^\d.]/g, '');
    
    // Split by decimal point
    const parts = numericValue.split('.');
    
    // Format the integer part with commas
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    
    // Rejoin with decimal point
    return parts.join('.');
};

/**
 * Parse formatted number back to numeric value (removes commas)
 * @param {string} value - The formatted value to parse
 * @returns {string} Numeric value without commas
 */
export const parseFormattedNumber = (value) => {
    return value.replace(/,/g, '');
};
