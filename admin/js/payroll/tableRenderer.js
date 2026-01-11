/**
 * Table Renderer Module
 * Handles table updates for payrun records
 */
import { PayrollUtils } from './utils.js';

export class TableRenderer {
    constructor() {
        this.tableBody = document.getElementById('payrunsTableBody');
    }

    /**
     * Add new row to payrun table
     */
    addNewRow(data) {
        if (!this.tableBody) return;

        const newRowHTML = `
            <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${data.payrunDate}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${data.periodCovered}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${data.employeesPaid}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">${PayrollUtils.formatCurrency(data.netTotal)}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">${data.status}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <a href="#" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                    <a href="#" class="text-gray-600 hover:text-gray-900">Export</a>
                </td>
            </tr>
        `;

        // Prepend the new row to the table body
        this.tableBody.insertAdjacentHTML('afterbegin', newRowHTML);

        // Remove the 'Pending' row if it exists
        const pendingRow = document.getElementById('pending-row');
        if (pendingRow) {
            pendingRow.remove();
        }
    }
}
