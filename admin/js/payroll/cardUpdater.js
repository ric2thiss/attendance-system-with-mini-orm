/**
 * Card Updater Module
 * Handles summary card updates
 */
import { PayrollUtils } from './utils.js';

export class CardUpdater {
    constructor() {
        this.totalNetPayEl = document.getElementById('totalNetPay');
        this.employeesCountEl = document.getElementById('employeesCount');
        this.totalGrossPayEl = document.getElementById('totalGrossPay');
        this.grossPayChangeEl = document.getElementById('grossPayChange');
        this.totalDeductionsEl = document.getElementById('totalDeductions');
        this.deductionsChangeEl = document.getElementById('deductionsChange');
        
        this.lastGrossTotal = 850500;
        this.lastDeductionsTotal = 125100;
    }

    /**
     * Update summary cards with new payrun data
     */
    updateSummaryCards(data) {
        // Update Net Pay Card
        if (this.totalNetPayEl) {
            this.totalNetPayEl.textContent = PayrollUtils.formatCurrency(data.netTotal);
        }
        if (this.employeesCountEl) {
            this.employeesCountEl.textContent = `For ${data.employeesPaid} active employees`;
        }
        
        // Update Gross Pay Card
        if (this.totalGrossPayEl) {
            this.totalGrossPayEl.textContent = PayrollUtils.formatCurrency(data.grossTotal);
        }
        if (this.grossPayChangeEl && this.lastGrossTotal > 0) {
            const grossChange = (((data.grossTotal - this.lastGrossTotal) / this.lastGrossTotal) * 100).toFixed(1);
            this.grossPayChangeEl.textContent = `${grossChange > 0 ? '+' : ''}${grossChange}% vs. previous month`;
            this.grossPayChangeEl.classList.toggle('text-green-500', grossChange >= 0);
            this.grossPayChangeEl.classList.toggle('text-red-500', grossChange < 0);
        }

        // Update Deductions Card
        if (this.totalDeductionsEl) {
            this.totalDeductionsEl.textContent = PayrollUtils.formatCurrency(data.deductionsTotal);
        }
        if (this.deductionsChangeEl && this.lastDeductionsTotal > 0) {
            const deductionsChange = (((data.deductionsTotal - this.lastDeductionsTotal) / this.lastDeductionsTotal) * 100).toFixed(1);
            this.deductionsChangeEl.textContent = `${deductionsChange > 0 ? '+' : ''}${deductionsChange}% vs. previous month`;
            this.deductionsChangeEl.classList.toggle('text-red-500', deductionsChange >= 0);
            this.deductionsChangeEl.classList.toggle('text-green-500', deductionsChange < 0);
        }

        // Update stored values for next calculation
        this.lastGrossTotal = data.grossTotal;
        this.lastDeductionsTotal = data.deductionsTotal;
    }
}
