/**
 * Employees Page Main Entry Point
 * Initializes all modules for the employees page
 */
import { initSidebar } from '../shared/sidebar.js';

/**
 * Initialize all modules
 */
function init() {
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
