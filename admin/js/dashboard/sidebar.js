/**
 * Sidebar Toggle Module
 * Handles mobile sidebar toggle functionality
 */

/**
 * Initialize sidebar toggle functionality
 */
export function initSidebar() {
    const sidebar = document.getElementById('sidebar');
    const toggleButton = document.getElementById('sidebar-toggle');
    const mainContent = document.querySelector('main');

    if (!sidebar || !toggleButton || !mainContent) {
        return;
    }

    toggleButton.addEventListener('click', () => {
        if (sidebar.classList.contains('-translate-x-full')) {
            sidebar.classList.remove('-translate-x-full');
            sidebar.classList.add('translate-x-0');
            mainContent.classList.add('opacity-50', 'pointer-events-none');
        } else {
            sidebar.classList.remove('translate-x-0');
            sidebar.classList.add('-translate-x-full');
            mainContent.classList.remove('opacity-50', 'pointer-events-none');
        }
    });

    // Close sidebar if main content is clicked on mobile
    mainContent.addEventListener('click', () => {
        if (window.innerWidth < 768 && sidebar.classList.contains('translate-x-0')) {
            sidebar.classList.remove('translate-x-0');
            sidebar.classList.add('-translate-x-full');
            mainContent.classList.remove('opacity-50', 'pointer-events-none');
        }
    });
}
