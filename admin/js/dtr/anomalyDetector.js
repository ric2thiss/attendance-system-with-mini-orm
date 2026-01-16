/**
 * Anomaly Detector Module
 * Handles rendering anomalies table
 */

export function initAnomalyDetector() {
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
     * Render anomalies table
     * @param {Array} anomalies 
     */
    function render(anomalies) {
        const tbody = document.getElementById('anomalies-table-body');
        const section = document.getElementById('anomalies-section');

        if (!anomalies || anomalies.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="2" class="px-3 py-8 text-center text-gray-500">
                        <p class="text-sm">No anomalies detected. All attendance records are complete.</p>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = anomalies.map(anomaly => {
            const date = formatDate(anomaly.date);
            const anomalyList = anomaly.anomalies.map(a => `<li class="mb-1">• ${a}</li>`).join('');

            return `
                <tr class="hover:bg-gray-50 transition duration-150">
                    <td class="px-3 py-3 whitespace-nowrap text-sm font-medium text-gray-900">${date}</td>
                    <td class="px-3 py-3 text-sm text-gray-700">
                        <ul class="list-none">
                            ${anomalyList}
                        </ul>
                    </td>
                </tr>
            `;
        }).join('');
    }

    return {
        render
    };
}
