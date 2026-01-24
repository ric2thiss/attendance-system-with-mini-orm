/**
 * Visitor Reports Main Entry Point
 * Handles report generation, chart rendering, and table updates using Chart.js
 */

import { initSidebar } from '../shared/sidebar.js';
import getBaseUrl from '../shared/baseUrl.js';

// Initialize sidebar
initSidebar();

// API endpoint for visitor reports
const API_ENDPOINT = `${getBaseUrl()}/api/reports/visitor-reports.php`;

// Get DOM elements
const reportTypeSelect = document.getElementById('reportType');
const startDateInput = document.getElementById('startDate');
const endDateInput = document.getElementById('endDate');
const runReportBtn = document.getElementById('runReportBtn');
const exportReportBtn = document.getElementById('exportReportBtn');
const loadingIndicator = document.getElementById('loadingIndicator');
const chartSection = document.getElementById('chartSection');
const chartTitle = document.getElementById('chartTitle');
const tableSection = document.getElementById('tableSection');
const tableHeader = document.getElementById('tableHeader');
const tableBody = document.getElementById('tableBody');

let currentChart = null;
let currentData = null;
let currentConfig = null;

// Chart.js color palette
const colors = {
    primary: '#3b82f6',
    secondary: '#8b5cf6',
    success: '#10b981',
    warning: '#f59e0b',
    danger: '#ef4444',
    info: '#06b6d4',
    purple: '#a855f7',
    pink: '#ec4899',
    teal: '#14b8a6',
    orange: '#f97316',
    gray: '#6b7280'
};

const colorPalette = [
    colors.primary,
    colors.secondary,
    colors.success,
    colors.warning,
    colors.danger,
    colors.info,
    colors.purple,
    colors.pink,
    colors.teal,
    colors.orange
];

// Report type configuration
const reportConfigs = {
    'total-visitors': {
        title: 'Total Visitors Over Time',
        chartType: 'line',
        tableHeaders: ['Date', 'Total Visitors'],
        getChartData: (data) => ({
            labels: data.map(d => {
                const date = new Date(d.date);
                return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            }),
            datasets: [{
                label: 'Visitors',
                data: data.map(d => d.count),
                borderColor: colors.primary,
                backgroundColor: colors.primary + '20',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        }),
        getTableRow: (row) => [
            new Date(row.date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }),
            row.count
        ]
    },
    'services-availed': {
        title: 'Services Availed by Visitors',
        chartType: 'bar',
        tableHeaders: ['Service', 'Count'],
        getChartData: (data) => ({
            labels: data.map(d => d.service),
            datasets: [{
                label: 'Visitors',
                data: data.map(d => d.count),
                backgroundColor: colorPalette.slice(0, data.length),
                borderColor: colorPalette.slice(0, data.length).map(c => c + 'CC'),
                borderWidth: 1
            }]
        }),
        getTableRow: (row) => [row.service, row.count]
    },
    'visitor-types': {
        title: 'Types of Visitors (Residents vs Non-Residents)',
        chartType: 'doughnut',
        tableHeaders: ['Visitor Type', 'Count'],
        getChartData: (data) => ({
            labels: data.map(d => d.type),
            datasets: [{
                data: data.map(d => d.count),
                backgroundColor: [colors.success, colors.warning],
                borderColor: '#ffffff',
                borderWidth: 2
            }]
        }),
        getTableRow: (row) => [row.type, row.count]
    },
    'appointment-types': {
        title: 'Appointment Types (Online vs Walk-in)',
        chartType: 'pie',
        tableHeaders: ['Appointment Type', 'Count'],
        getChartData: (data) => ({
            labels: data.map(d => d.type),
            datasets: [{
                data: data.map(d => d.count),
                backgroundColor: [colors.purple, colors.pink],
                borderColor: '#ffffff',
                borderWidth: 2
            }]
        }),
        getTableRow: (row) => [row.type, row.count]
    },
    'gender-distribution': {
        title: 'Gender Distribution of Visitors',
        chartType: 'bar',
        tableHeaders: ['Gender', 'Count'],
        getChartData: (data) => ({
            labels: data.map(d => d.gender),
            datasets: [{
                label: 'Visitors',
                data: data.map(d => d.count),
                backgroundColor: colors.primary + '80',
                borderColor: colors.primary,
                borderWidth: 1
            }]
        }),
        getTableRow: (row) => [row.gender, row.count]
    },
    'age-services': {
        title: 'Age Groups & Services Availed',
        chartType: 'bar',
        tableHeaders: ['Age Group', 'Service', 'Count'],
        getChartData: (data) => {
            // Get all unique services
            const allServices = new Set();
            data.forEach(group => {
                group.services.forEach(s => allServices.add(s.service));
            });
            const services = Array.from(allServices);
            
            // Get all age groups
            const ageGroups = data.map(d => d.age_group);
            
            // Create datasets for each service
            const datasets = services.map((service, idx) => ({
                label: service,
                data: ageGroups.map(ageGroup => {
                    const group = data.find(d => d.age_group === ageGroup);
                    const serviceData = group?.services.find(s => s.service === service);
                    return serviceData ? serviceData.count : 0;
                }),
                backgroundColor: colorPalette[idx % colorPalette.length] + '80',
                borderColor: colorPalette[idx % colorPalette.length],
                borderWidth: 1
            }));
            
            return {
                labels: ageGroups,
                datasets: datasets
            };
        },
        getTableRow: (row) => {
            // Flatten the nested structure for table
            const rows = [];
            row.services.forEach(service => {
                rows.push([row.age_group, service.service, service.count]);
            });
            return rows;
        }
    }
};

/**
 * Fetch report data from API
 */
async function fetchReportData(type, fromDate, toDate) {
    const url = `${API_ENDPOINT}?type=${encodeURIComponent(type)}&from=${encodeURIComponent(fromDate)}&to=${encodeURIComponent(toDate)}`;

    try {
        const response = await fetch(url);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error fetching report data:', error);
        throw error;
    }
}

/**
 * Render chart using Chart.js
 */
function renderChart(data, config) {
    // Destroy existing chart if it exists
    if (currentChart) {
        currentChart.destroy();
        currentChart = null;
    }

    if (!data || data.length === 0) {
        const canvas = document.getElementById('reportChart');
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        canvas.parentElement.innerHTML = '<p class="text-center text-gray-500 py-8">No data available for the selected period.</p>';
        return;
    }

    const canvas = document.getElementById('reportChart');
    if (!canvas) {
        console.error('Canvas element not found');
        return;
    }

    const chartData = config.getChartData(data);
    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                enabled: true
            }
        },
        scales: {}
    };

    // Special handling for grouped bar chart (age-services)
    if (config.chartType === 'bar' && data[0]?.services) {
        chartOptions.scales = {
            x: {
                stacked: false,
            },
            y: {
                beginAtZero: true,
                stacked: false,
                ticks: {
                    stepSize: 1
                }
            }
        };
    } else if (config.chartType === 'bar') {
        // Regular bar chart options
        chartOptions.scales = {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        };
    }

    currentChart = new Chart(canvas, {
        type: config.chartType,
        data: chartData,
        options: chartOptions
    });
}

/**
 * Render data table
 */
function renderTable(data, config) {
    // Clear existing table content
    tableHeader.innerHTML = '';
    tableBody.innerHTML = '';

    if (!data || data.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="${config.tableHeaders.length}" class="px-6 py-8 text-center text-gray-500">
                    <p class="text-sm">No data available for the selected period.</p>
                </td>
            </tr>
        `;
        return;
    }

    // Render headers
    config.tableHeaders.forEach(header => {
        const th = document.createElement('th');
        th.className = 'px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider';
        th.textContent = header;
        tableHeader.appendChild(th);
    });

    // Render rows
    const reportType = reportTypeSelect.value;
    if (reportType === 'age-services') {
        // Special handling for nested age-services data
        data.forEach(group => {
            group.services.forEach((service, idx) => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-gray-50';
                
                const cells = [group.age_group, service.service, service.count];
                if (idx > 0) {
                    // Don't repeat age group for subsequent services
                    cells[0] = '';
                }
                
                cells.forEach(cell => {
                    const td = document.createElement('td');
                    td.className = 'px-6 py-4 whitespace-nowrap text-sm text-gray-900';
                    td.textContent = cell;
                    tr.appendChild(td);
                });

                tableBody.appendChild(tr);
            });
        });
    } else {
        data.forEach(row => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-gray-50';
            
            config.getTableRow(row).forEach(cell => {
                const td = document.createElement('td');
                td.className = 'px-6 py-4 whitespace-nowrap text-sm text-gray-900';
                td.textContent = cell;
                tr.appendChild(td);
            });

            tableBody.appendChild(tr);
        });
    }
}

/**
 * Update report display
 */
async function updateReport() {
    const type = reportTypeSelect.value;
    const fromDate = startDateInput.value;
    const toDate = endDateInput.value;

    if (!fromDate || !toDate) {
        alert('Please select both start and end dates.');
        return;
    }

    if (new Date(fromDate) > new Date(toDate)) {
        alert('Start date must be before end date.');
        return;
    }

    // Show loading indicator
    loadingIndicator.classList.remove('hidden');
    chartSection.classList.add('hidden');
    tableSection.classList.add('hidden');

    try {
        const response = await fetchReportData(type, fromDate, toDate);
        
        if (!response.success || !response.data) {
            throw new Error(response.error || 'Failed to fetch report data');
        }

        currentData = response.data;
        currentConfig = reportConfigs[type];

        if (!currentConfig) {
            throw new Error('Unknown report type');
        }

        // Update chart title with date range
        const fromDateStr = new Date(fromDate).toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
        const toDateStr = new Date(toDate).toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
        chartTitle.textContent = `${currentConfig.title} (${fromDateStr} - ${toDateStr})`;

        // Show sections first (before rendering chart so container has width)
        chartSection.classList.remove('hidden');
        tableSection.classList.remove('hidden');

        // Render table immediately
        renderTable(response.data, currentConfig);

        // Render chart after a brief delay to ensure container has width
        requestAnimationFrame(() => {
            renderChart(response.data, currentConfig);
        });

    } catch (error) {
        console.error('Error updating report:', error);
        alert('Error loading report data: ' + error.message);
        const canvas = document.getElementById('reportChart');
        if (canvas && canvas.parentElement) {
            canvas.parentElement.innerHTML = `<p class="text-center text-red-500 py-8">Error: ${error.message}</p>`;
        }
        tableBody.innerHTML = `<tr><td colspan="5" class="px-6 py-8 text-center text-red-500">Error: ${error.message}</td></tr>`;
    } finally {
        loadingIndicator.classList.add('hidden');
    }
}

/**
 * Export report data
 */
function exportReport() {
    if (!currentData || !currentConfig) {
        alert('Please generate a report first.');
        return;
    }

    // Create CSV content
    const headers = currentConfig.tableHeaders.join(',');
    let rows = [];
    
    const reportType = reportTypeSelect.value;
    if (reportType === 'age-services') {
        // Special handling for nested age-services data
        currentData.forEach(group => {
            group.services.forEach((service, idx) => {
                const row = [group.age_group, service.service, service.count];
                if (idx > 0) {
                    row[0] = ''; // Don't repeat age group
                }
                rows.push(row.map(cell => `"${cell}"`).join(','));
            });
        });
    } else {
        rows = currentData.map(row => {
            return currentConfig.getTableRow(row).map(cell => `"${cell}"`).join(',');
        });
    }
    
    const csv = [headers, ...rows].join('\n');

    // Create download link
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `visitor_report_${reportTypeSelect.value}_${startDateInput.value}_${endDateInput.value}.csv`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}

// Event listeners
if (runReportBtn) {
    runReportBtn.addEventListener('click', updateReport);
}

if (exportReportBtn) {
    exportReportBtn.addEventListener('click', exportReport);
}

if (reportTypeSelect) {
    reportTypeSelect.addEventListener('change', updateReport);
}

// Auto-run report on page load if dates are set
document.addEventListener('DOMContentLoaded', () => {
    if (startDateInput.value && endDateInput.value) {
        updateReport();
    }
});

// Handle window resize for chart
let resizeTimer;
window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
        if (currentData && currentChart && reportTypeSelect.value) {
            const config = reportConfigs[reportTypeSelect.value];
            if (config) {
                renderChart(currentData, config);
            }
        }
    }, 250);
});
