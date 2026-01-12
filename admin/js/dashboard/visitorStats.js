/**
 * Visitor Statistics Module
 * Handles fetching and updating visitor statistics based on filter
 */

/**
 * Update visitor counts based on filter
 * @param {string} filter - Filter type: 'today', 'week', 'month', 'year'
 */
export async function updateVisitorCounts(filter) {
    try {
        // Using modular endpoint: ../api/visitors/stats.php?filter=${filter}
        const response = await fetch(`../api/visitors/stats.php?filter=${filter}`);
        const data = await response.json();

        if (data.success) {
            // Update the main Total Residents count with Total Visitors
            const totalResidentsMainCount = document.getElementById('total-residents-main-count');
            if (totalResidentsMainCount) {
                totalResidentsMainCount.textContent = data.total_visitors || 0;
            }
            
            // Update detailed counts
            document.getElementById('total-visitors-count').textContent = data.total_visitors || 0;
            document.getElementById('resident-visitors-count').textContent = data.resident_visitors || 0;
            document.getElementById('non-resident-visitors-count').textContent = data.non_resident_visitors || 0;
            document.getElementById('walkin-count').textContent = data.walkin || 0;
            document.getElementById('online-appointment-count').textContent = data.online_appointment || 0;
        } else {
            console.error('Error fetching visitor stats:', data.error);
        }
    } catch (error) {
        console.error('Error updating visitor counts:', error);
    }
}

/**
 * Initialize visitor statistics with event listener
 */
export function initVisitorStats() {
    const visitorFilterDropdown = document.getElementById('visitor-filter-dropdown');
    if (visitorFilterDropdown) {
        visitorFilterDropdown.addEventListener('change', (e) => {
            updateVisitorCounts(e.target.value);
        });
        // Initialize with default filter
        updateVisitorCounts('month');
    }
}
