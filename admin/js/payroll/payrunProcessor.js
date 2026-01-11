/**
 * Payrun Processor Module
 * Handles payrun calculation logic
 */
import { PayrollUtils } from './utils.js';

export class PayrunProcessor {
    constructor(initialNetTotal = 725400, initialGrossTotal = 850500, initialDeductionsTotal = 125100, initialEmployeeCount = 15) {
        this.lastNetTotal = initialNetTotal;
        this.lastGrossTotal = initialGrossTotal;
        this.lastDeductionsTotal = initialDeductionsTotal;
        this.employeeCount = initialEmployeeCount;
    }

    /**
     * Process a new payrun
     */
    processPayrun() {
        const payrunDate = PayrollUtils.getFormattedDate();
        
        // Increment the employee count slightly for variation
        this.employeeCount = Math.min(this.employeeCount + Math.floor(Math.random() * 2), 20);

        // Simulate slight growth in totals (Net Pay between +1% and +5%)
        const netIncreaseFactor = 1 + (Math.random() * 0.04 + 0.01); // 1.01 to 1.05
        const newNetTotal = Math.round(this.lastNetTotal * netIncreaseFactor / 100) * 100; // Round to nearest 100
        
        // Gross and Deductions are derived from new Net Total to maintain consistency
        const newGrossTotal = Math.round(newNetTotal * 1.18 / 100) * 100; // Gross is approx Net * 1.18
        const newDeductionsTotal = newGrossTotal - newNetTotal;

        const newPayrunData = {
            payrunDate: payrunDate,
            periodCovered: `New Period - ${payrunDate}`,
            employeesPaid: this.employeeCount,
            netTotal: newNetTotal,
            grossTotal: newGrossTotal,
            deductionsTotal: newDeductionsTotal,
            status: 'Completed',
        };

        // Update the "last" totals for the next run
        this.lastNetTotal = newNetTotal;
        this.lastGrossTotal = newGrossTotal;
        this.lastDeductionsTotal = newDeductionsTotal;

        return newPayrunData;
    }
}
