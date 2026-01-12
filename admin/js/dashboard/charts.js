/**
 * Chart Initialization and Management Module
 * Handles Chart.js initialization and updates for Employee Attendance and Visitor Traffic charts
 */

import { getChartData } from './chartData.js';

let employeeAttendanceChart = null;
let visitorTrafficChart = null;

/**
 * Initialize Employee Attendance Chart
 * @param {string} filterType - Filter type: 'today', 'week', 'month', 'year'
 */
export async function initializeEmployeeAttendanceChart(filterType = 'month') {
    const ctx = document.getElementById('employeeAttendanceChart');
    if (!ctx) return;

    const chartData = await getChartData(filterType, 'attendance');
    
    // Destroy existing chart if it exists
    if (employeeAttendanceChart) {
        employeeAttendanceChart.destroy();
    }

    employeeAttendanceChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [
                {
                    label: 'Present',
                    data: chartData.presentData,
                    borderColor: '#3b82f6', // Blue
                    backgroundColor: 'rgba(59, 130, 246, 0.2)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointHoverRadius: 5
                },
                {
                    label: 'Absent',
                    data: chartData.absentData,
                    borderColor: '#ef4444', // Red
                    backgroundColor: 'rgba(239, 68, 68, 0.2)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointHoverRadius: 5
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 15
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            }
        }
    });
}

/**
 * Initialize Visitor Traffic Chart
 * @param {string} filterType - Filter type: 'today', 'week', 'month', 'year'
 */
export async function initializeVisitorTrafficChart(filterType = 'month') {
    const ctx = document.getElementById('visitorTrafficChart');
    if (!ctx) return;

    const chartData = await getChartData(filterType, 'visitor');
    
    // Destroy existing chart if it exists
    if (visitorTrafficChart) {
        visitorTrafficChart.destroy();
    }

    visitorTrafficChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [
                {
                    label: 'Total Visitors',
                    data: chartData.visitorData,
                    borderColor: '#22c55e', // Green
                    backgroundColor: 'rgba(34, 197, 94, 0.2)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointHoverRadius: 5
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 15
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            }
        }
    });
}

/**
 * Initialize all charts with event listeners for filter dropdowns
 */
export async function initializeCharts() {
    const defaultFilter = 'month';
    await initializeEmployeeAttendanceChart(defaultFilter);
    await initializeVisitorTrafficChart(defaultFilter);

    // Add event listeners for filter dropdowns
    const attendanceFilter = document.getElementById('attendance-filter');
    const visitorFilter = document.getElementById('visitor-filter');

    if (attendanceFilter) {
        attendanceFilter.addEventListener('change', async (e) => {
            await initializeEmployeeAttendanceChart(e.target.value);
        });
    }

    if (visitorFilter) {
        visitorFilter.addEventListener('change', async (e) => {
            await initializeVisitorTrafficChart(e.target.value);
        });
    }
}
