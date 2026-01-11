/**
 * Utilities Module
 * Currency and date formatting utilities
 */
export class PayrollUtils {
    /**
     * Format currency amount
     */
    static formatCurrency(amount) {
        return '₱ ' + amount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    /**
     * Get today's date in 'Mon DD, YYYY' format
     */
    static getFormattedDate() {
        const date = new Date();
        const options = { month: 'short', day: 'numeric', year: 'numeric' };
        return date.toLocaleDateString('en-US', options);
    }
}
