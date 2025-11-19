/**
 * Convert Persian/Arabic-Indic numbers to English numbers
 * @param {string} str - The string to convert
 * @returns {string} - The converted string
 */
export function convertPersianToEnglish(str) {
    if (!str) return str;
    
    const persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    const arabicIndicDigits = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
    const englishDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

    let result = str.toString();
    
    // Convert Persian digits
    for (let i = 0; i < persianDigits.length; i++) {
        result = result.replace(new RegExp(persianDigits[i], 'g'), englishDigits[i]);
    }
    
    // Convert Arabic-Indic digits
    for (let i = 0; i < arabicIndicDigits.length; i++) {
        result = result.replace(new RegExp(arabicIndicDigits[i], 'g'), englishDigits[i]);
    }
    
    return result;
}

/**
 * Convert English numbers to Persian numbers
 * @param {string} str - The string to convert
 * @returns {string} - The converted string
 */
export function convertEnglishToPersian(str) {
    if (!str) return str;
    
    const englishDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    const persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];

    let result = str.toString();
    
    // Convert English digits to Persian
    for (let i = 0; i < englishDigits.length; i++) {
        result = result.replace(new RegExp(englishDigits[i], 'g'), persianDigits[i]);
    }
    
    return result;
}

/**
 * Format number with Persian locale
 * @param {number} num - The number to format
 * @returns {string} - The formatted number
 */
export function formatNumber(num) {
    if (num === null || num === undefined) return '0';
    return Number(num).toLocaleString('fa-IR');
}

/**
 * Format price with Persian locale and currency
 * @param {number} price - The price to format
 * @returns {string} - The formatted price
 */
export function formatPrice(price) {
    if (price === null || price === undefined) return '0 تومان';
    return `${Number(price).toLocaleString('fa-IR')} تومان`;
}
