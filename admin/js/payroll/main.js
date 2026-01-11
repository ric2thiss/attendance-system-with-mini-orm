/**
 * Payroll Page Main Entry Point
 * Initializes all modules for the payroll page
 */
import { PayrunProcessor } from './payrunProcessor.js';
import { CardUpdater } from './cardUpdater.js';
import { TableRenderer } from './tableRenderer.js';
import { PayrollModal } from './modal.js';
import { PayrollUtils } from './utils.js';
import { initSidebar } from '../shared/sidebar.js';

// Initialize modules
let payrunProcessor;
let cardUpdater;
let tableRenderer;
let modal;

/**
 * Handle process payrun button click
 */
function processNewPayrun() {
    // Generate new payrun data
    const newPayrunData = payrunProcessor.processPayrun();

    // Update summary cards
    cardUpdater.updateSummaryCards(newPayrunData);

    // Add new row to table
    tableRenderer.addNewRow(newPayrunData);

    // Show success modal
    const message = `Payroll for ${newPayrunData.payrunDate} (Net: ${PayrollUtils.formatCurrency(newPayrunData.netTotal)}) processed.`;
    modal.show(message);
}

/**
 * Initialize all modules
 */
function init() {
    // Initialize payrun processor with initial values
    payrunProcessor = new PayrunProcessor(725400, 850500, 125100, 15);

    // Initialize card updater
    cardUpdater = new CardUpdater();

    // Initialize table renderer
    tableRenderer = new TableRenderer();

    // Initialize modal
    modal = new PayrollModal();
    modal.init();

    // Initialize sidebar toggle
    initSidebar();

    // Event listener for process payrun button
    const processPayrunButton = document.getElementById('processPayrunButton');
    if (processPayrunButton) {
        processPayrunButton.addEventListener('click', processNewPayrun);
    }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    // DOM is already ready
    init();
}
