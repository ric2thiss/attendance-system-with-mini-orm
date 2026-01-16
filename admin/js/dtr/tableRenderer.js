/**
 * Table Renderer Module
 * Handles rendering the attendance table
 */

export function initTableRenderer() {
    /**
     * Format time from timestamp
     * @param {string|null} timestamp 
     * @returns {string}
     */
    function formatTime(timestamp) {
        if (!timestamp) return '-';
        const date = new Date(timestamp);
        return date.toLocaleTimeString('en-US', { 
            hour: '2-digit', 
            minute: '2-digit',
            hour12: true 
        });
    }

    /**
     * Format date
     * @param {string} dateStr 
     * @returns {string}
     */
    function formatDate(dateStr) {
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }

    /**
     * Format total hours
     * @param {number} hours 
     * @param {number} minutes 
     * @returns {string}
     */
    function formatTotalHours(hours, minutes) {
        if (hours === 0 && minutes === 0) return '-';
        return `${hours}h ${minutes}m`;
    }

    /**
     * Get status badge class
     * @param {string} status 
     * @returns {string}
     */
    function getStatusBadgeClass(status) {
        if (status === 'Complete') {
            return 'bg-green-100 text-green-800';
        }
        return 'bg-yellow-100 text-yellow-800';
    }

    /**
     * Render attendance table
     * @param {Array} attendanceData 
     */
    function render(attendanceData) {
        const tbody = document.getElementById('attendance-table-body');
        
        if (!attendanceData || attendanceData.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="px-3 py-8 text-center text-gray-500">
                        <p class="text-sm">No attendance records found for the selected date range.</p>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = attendanceData.map(day => {
            const morningIn = day.morning_in ? formatTime(day.morning_in.timestamp) : '-';
            const morningOut = day.morning_out ? formatTime(day.morning_out.timestamp) : '-';
            const afternoonIn = day.afternoon_in ? formatTime(day.afternoon_in.timestamp) : '-';
            const afternoonOut = day.afternoon_out ? formatTime(day.afternoon_out.timestamp) : '-';
            const totalHours = formatTotalHours(day.total_hours || 0, day.total_minutes || 0);
            const status = day.status || 'Incomplete';
            const statusClass = getStatusBadgeClass(status);

            return `
                <tr class="hover:bg-gray-50 transition duration-150">
                    <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-900">${formatDate(day.date)}</td>
                    <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-700">${morningIn}</td>
                    <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-700">${morningOut}</td>
                    <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-700">${afternoonIn}</td>
                    <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-700">${afternoonOut}</td>
                    <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-700 font-medium">${totalHours}</td>
                    <td class="px-3 py-3 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">
                            ${status}
                        </span>
                    </td>
                </tr>
            `;
        }).join('');
    }

    return {
        render
    };
}
