/**
 * Chart Data Generation Module
 * Generates chart data based on filter type (today, week, month, year)
 */

/**
 * Generate chart data labels and values based on filter type
 * @param {string} filterType - Filter type: 'today', 'week', 'month', 'year'
 * @param {string} chartType - Chart type (not currently used but kept for future use)
 * @returns {Object} Object containing labels, presentData, absentData, and visitorData arrays
 */
export function getChartData(filterType, chartType) {
    let labels = [];
    let presentData = [];
    let absentData = [];
    let visitorData = [];

    if (filterType === 'today') {
        // Today's data by hours
        labels = Array.from({length: 24}, (_, i) => {
            const hour = i.toString().padStart(2, '0');
            return `${hour}:00`;
        });
        presentData = Array.from({length: 24}, () => Math.floor(Math.random() * 20) + 10);
        absentData = Array.from({length: 24}, () => Math.floor(Math.random() * 5));
        visitorData = Array.from({length: 24}, () => Math.floor(Math.random() * 30) + 15);
    } else if (filterType === 'week') {
        // This week's data by days
        const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        labels = days;
        presentData = [25, 28, 30, 27, 29, 32, 26];
        absentData = [5, 3, 2, 4, 2, 1, 3];
        visitorData = [80, 92, 105, 88, 95, 110, 85];
    } else if (filterType === 'month') {
        // This month's data by weeks
        labels = ['Week 1', 'Week 2', 'Week 3', 'Week 4'];
        presentData = [120, 135, 128, 142];
        absentData = [15, 10, 12, 8];
        visitorData = [350, 420, 380, 450];
    } else if (filterType === 'year') {
        // This year's data by months
        labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        presentData = [250, 260, 275, 280, 285, 290, 280, 295, 290, 300, 285, 275];
        absentData = [30, 25, 20, 15, 10, 8, 12, 5, 10, 8, 12, 15];
        visitorData = [270, 290, 300, 290, 310, 280, 290, 285, 305, 320, 295, 280];
    }

    return { labels, presentData, absentData, visitorData };
}
