/**
 * Employees Page Main Entry Point
 * Initializes all modules for the employees page
 */
import { EmployeeModal } from './modal.js';
import { EmployeeFormHandler } from './formHandler.js';
import { initSidebar } from '../shared/sidebar.js';

// Get configuration from data attributes on the script tag or meta tags
function getConfig() {
    const currentScript = document.currentScript;
    let employeesApiUrl = currentScript?.dataset?.employeesApiUrl || '';
    
    // Fallback: try to get from meta tags if not in script data attributes
    if (!employeesApiUrl) {
        const metaApi = document.querySelector('meta[name="employees-api-url"]');
        if (metaApi) employeesApiUrl = metaApi.content;
    }
    
    return { employeesApiUrl };
}

const config = getConfig();

// Initialize modules
let modal;
let formHandler;

/**
 * Initialize all modules
 */
function init() {
    // Initialize modal
    modal = new EmployeeModal('addEmployeeModal');
    modal.init();

    // Initialize form handler
    if (config.employeesApiUrl) {
        formHandler = new EmployeeFormHandler(config.employeesApiUrl);
        formHandler.init();
    }

    // Initialize sidebar toggle
    initSidebar();
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    // DOM is already ready
    init();
}
