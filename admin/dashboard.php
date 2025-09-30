
<?php
require_once __DIR__ . "/../bootstrap.php";
$getdata = (new AttendanceController())->index();

// print_r($getdata["attendancesTodayCount"]);
include_once '../shared/components/Sidebar.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Overview</title>
    <link rel="stylesheet" href="../utils/styles/global.css">
    <!-- Load Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Load Chart.js for graphs -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <!-- Use Inter font family -->
    <style>

        /* Custom darker button color for Attendance Now */
        .btn-dark {
            background-color: #374151; /* A deep slate color */
        }
        .btn-dark:hover {
            background-color: #1f2937;
        }
        /* Style for the container headers */
        .metric-header-employee {
            background-color: #4f46e5; /* Indigo-600 */
        }
        .metric-header-visitor {
            background-color: #22c55e; /* Green-500 */
        }
    </style>
</head>
<body>

    <!-- Main Container -->
    <div class="flex min-h-screen">

        <?=Sidebar("Dashboard", null, "./Login_logo1.png")?>

        <!-- 2. MAIN CONTENT AREA -->
        <main class="flex-1 md:ml-64 p-6 transition-all duration-300">

            <!-- Top Header Bar -->
            <header class="mb-6">
                <div class="flex justify-between items-center mb-1">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-800">Dashboard Overview</h1>
                        <p class="text-gray-500 text-sm">Good morning, Juan</p>
                    </div>
                    <p class="text-sm text-gray-500" id="current-date">September 28, 2025</p>
                </div>
            </header>

            <!-- DASHBOARD GRID LAYOUT -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

                <!-- Realtime Insight Card (Col Span 12 on small, 3 on medium/large) -->
                <div class="md:col-span-12 lg:col-span-3">
                    <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100">
                        <h2 class="font-semibold text-gray-800 mb-4">Realtime Insight</h2>

                        <!-- Clock & Insight -->
                        <div class="flex items-center space-x-2">
                            <!-- Sun/Clock Icon -->
                            <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            <span class="text-4xl font-extrabold text-gray-900" id="realtime-clock">
                                10:28 : 40 AM
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 mb-6 mt-1">Realtime Insight</p>

                        <!-- Today's Date -->
                        <p class="text-base text-gray-500">Today:</p>
                        <p class="text-lg font-bold text-gray-700 mb-6" id="today-date-insight">
                            28th September 2025
                        </p>

                        <!-- Attendance Button -->
                        <button onclick="window.location.href='biometrics://identify'" class="w-full py-3 btn-dark hover:bg-gray-700 text-white font-semibold rounded-lg transition-colors shadow-md text-lg flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Attendance Now
                        </button>
                    </div>
                </div>

                <!-- Metrics Area (Col Span 12 on small, 9 on medium/large) -->
                <div class="md:col-span-12 lg:col-span-9 space-y-6">

                    <!-- EMPLOYEE STATS CONTAINER -->
                    <div class="bg-white rounded-xl shadow-lg border border-gray-100">
                        <!-- Header for Employees -->
                        <div class="metric-header-employee p-3 rounded-t-xl text-white font-bold text-lg flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20v-2c0-.656-.126-1.283-.356-1.857M9 20H7l-1-1v-6a1 1 0 011-1h10a1 1 0 011 1v6l-1 1h-2"></path></svg>
                            Employee Performance Metrics
                        </div>

                        <!-- Metrics Grid - UPDATED to xl:grid-cols-5 -->
                        <div class="p-4 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 xl:grid-cols-5 gap-4">
                            
                            <!-- Total Employees -->
                            <div class="bg-gray-50 p-4 rounded-lg flex flex-col justify-between">
                                <div class="flex justify-between items-center">
                                    <span class="text-3xl font-bold text-blue-600">32</span>
                                    <svg class="w-8 h-8 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-gray-500 mt-2">Total Employees</p>
                                <p class="text-xs text-green-500">10% Less than yesterday</p>
                            </div>

                            <!-- Presents (Time In) -->
                            <div class="bg-gray-50 p-4 rounded-lg flex flex-col justify-between">
                                <div class="flex justify-between items-center">
                                    <span class="text-3xl font-bold text-green-600"><?=$getdata["attendancesTodayCount"]?></span>
                                    <svg class="w-8 h-8 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-gray-500 mt-2">Present</p>
                                <p class="text-xs text-green-500">10% Less than yesterday</p>
                            </div>

                            <!-- Absents -->
                            <div class="bg-gray-50 p-4 rounded-lg flex flex-col justify-between">
                                <div class="flex justify-between items-center">
                                    <span class="text-3xl font-bold text-red-600">1</span>
                                    <svg class="w-8 h-8 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 0012 21.659v-2.359a7 7 0 01-6.707-8.086L3.929 7.071A9 9 0 0018.364 18.364zM16 12a4 4 0 11-8 0 4 4 0 018 0zM12 21.659A9 9 0 005.636 18.364M18.364 5.636A9 9 0 0012 2.341v2.359a7 7 0 016.707 8.086l-1.336 1.336z"></path>
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-gray-500 mt-2">Absent</p>
                                <p class="text-xs text-red-500">10% More than yesterday</p>
                            </div>

                            <!-- Lates -->
                            <div class="bg-gray-50 p-4 rounded-lg flex flex-col justify-between">
                                <div class="flex justify-between items-center">
                                    <span class="text-3xl font-bold text-yellow-600">3</span>
                                    <svg class="w-8 h-8 text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-gray-500 mt-2">Late</p>
                                <p class="text-xs text-red-500">50% More than yesterday</p>
                            </div>

                            <!-- Over-time -->
                            <div class="bg-gray-50 p-4 rounded-lg flex flex-col justify-between">
                                <div class="flex justify-between items-center">
                                    <span class="text-3xl font-bold text-indigo-600">4</span>
                                    <svg class="w-8 h-8 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-gray-500 mt-2">Over-time</p>
                                <p class="text-xs text-green-500">20% Less than yesterday</p>
                            </div>
                        </div>
                    </div>

                    <!-- VISITOR STATS CONTAINER -->
                    <div class="bg-white rounded-xl shadow-lg border border-gray-100">
                        <!-- Header for Visitors -->
                        <div class="metric-header-visitor p-3 rounded-t-xl text-white font-bold text-lg flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                            Visitor & Traffic Metrics
                        </div>

                        <!-- Metrics Grid - UPDATED to xl:grid-cols-5 -->
                        <div class="p-4 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 xl:grid-cols-5 gap-4">

                            <!-- Total Visitors -->
                            <div class="bg-gray-50 p-4 rounded-lg flex flex-col justify-between">
                                <div class="flex justify-between items-center">
                                    <span class="text-3xl font-bold text-purple-600">96</span>
                                    <svg class="w-8 h-8 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-gray-500 mt-2">Total Visitors</p>
                                <p class="text-xs text-green-500">In a minute</p>
                            </div>

                            <!-- Walk-in -->
                            <div class="bg-gray-50 p-4 rounded-lg flex flex-col justify-between">
                                <div class="flex justify-between items-center">
                                    <span class="text-3xl font-bold text-pink-600">12</span>
                                    <svg class="w-8 h-8 text-pink-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-gray-500 mt-2">Walk-in</p>
                                <p class="text-xs text-green-500">10% Less than yesterday</p>
                            </div>

                            <!-- Online -->
                            <div class="bg-gray-50 p-4 rounded-lg flex flex-col justify-between">
                                <div class="flex justify-between items-center">
                                    <span class="text-3xl font-bold text-teal-600">12</span>
                                    <svg class="w-8 h-8 text-teal-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-gray-500 mt-2">Online</p>
                                <p class="text-xs text-green-500">10% Less than yesterday</p>
                            </div>

                            <!-- Resident Visitors -->
                            <div class="bg-gray-50 p-4 rounded-lg flex flex-col justify-between">
                                <div class="flex justify-between items-center">
                                    <span class="text-3xl font-bold text-orange-600">12</span>
                                    <svg class="w-8 h-8 text-orange-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zM12 14c-1.33 0-4-.67-4-2.22V11h8v.78c0 1.55-2.67 2.22-4 2.22zm0-4a2 2 0 100-4 2 2 0 000 4z"></path>
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-gray-500 mt-2">Resident Visitors</p>
                                <p class="text-xs text-green-500">10% Less than yesterday</p>
                            </div>

                            <!-- Non-Resident Visitors -->
                            <div class="bg-gray-50 p-4 rounded-lg flex flex-col justify-between">
                                <div class="flex justify-between items-center">
                                    <span class="text-3xl font-bold text-red-600">12</span>
                                    <svg class="w-8 h-8 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 17l-2 2m0 0l-2-2m2 2V3m4 12V3m0 14h.01M16 12v5m0 0l-2-2m2 2l2-2m0 0l-2 2m2-2h.01"></path>
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-gray-500 mt-2">Non-Resident Visitors</p>
                                <p class="text-xs text-green-500">10% Less than yesterday</p>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- GRAPHS SECTION -->
                <div class="md:col-span-12 lg:col-span-6">
                    <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100 h-96">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">Monthly Attendance Overview</h2>
                        <canvas id="monthlyAttendanceChart"></canvas>
                    </div>
                </div>

                <div class="md:col-span-12 lg:col-span-6">
                    <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100 h-96">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">Monthly Visitor Logs Overview</h2>
                        <canvas id="monthlyVisitorChart"></canvas>
                    </div>
                </div>

            </div>

        </main>
    </div>

    <!-- JavaScript for Date, Time, Charts, and Sidebar Toggle -->
    <script>
        // --- 1. Utility Functions ---
        function getDaySuffix(day) {
            if (day > 3 && day < 21) return 'th';
            switch (day % 10) {
                case 1:  return "st";
                case 2:  return "nd";
                case 3:  return "rd";
                default: return "th";
            }
        }

        // --- 2. Update Clock and Dates ---
        function updateDatesAndClock() {
            const now = new Date();
            
            // Header Date (e.g., September 28, 2025)
            const headerDateElement = document.getElementById('current-date');
            if (headerDateElement) {
                headerDateElement.textContent = now.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
            }

            // Realtime Clock (e.g., 10:28 : 40 AM)
            const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
            const clockElement = document.getElementById('realtime-clock');
            const timeParts = now.toLocaleTimeString('en-US', timeOptions).split(' ');
            const time = timeParts[0];
            const ampm = timeParts[1];
            // Format to HH:MM : SS AM/PM
            const formattedTime = time.replace(/:/g, ' : ') + ' ' + ampm; 

            if (clockElement) {
                clockElement.textContent = formattedTime;
            }

            // Today's Date Insight (e.g., 28th September 2025)
            const day = now.getDate();
            const daySuffix = getDaySuffix(day);
            const monthYear = now.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
            const dateString = day + daySuffix + ' ' + monthYear;
            
            const dateInsightElement = document.getElementById('today-date-insight');
            if (dateInsightElement) {
                dateInsightElement.textContent = dateString;
            }
        }
        
        // Update the clock every second
        setInterval(updateDatesAndClock, 1000);
        updateDatesAndClock(); // Initial call

        // --- 3. Chart Initialization ---
        function initializeCharts() {
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep'];

            // Monthly Attendance Overview Chart
            const attendanceCtx = document.getElementById('monthlyAttendanceChart').getContext('2d');
            new Chart(attendanceCtx, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [
                        {
                            label: 'Present',
                            data: [250, 260, 275, 280, 285, 290, 280, 295, 290],
                            borderColor: '#3b82f6', // Blue
                            backgroundColor: 'rgba(59, 130, 246, 0.2)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 0
                        },
                        {
                            label: 'Absent',
                            data: [30, 25, 20, 15, 10, 8, 12, 5, 10],
                            borderColor: '#ef4444', // Red
                            backgroundColor: 'rgba(239, 68, 68, 0.2)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 0
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
                                usePointStyle: true
                            }
                        },
                        title: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Monthly Visitor Logs Overview Chart
            const visitorCtx = document.getElementById('monthlyVisitorChart').getContext('2d');
            new Chart(visitorCtx, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [
                        {
                            label: 'Total Visitors',
                            data: [270, 290, 300, 290, 310, 280, 290, 285, 305],
                            borderColor: '#a855f7', // Purple
                            backgroundColor: 'rgba(168, 85, 247, 0.3)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 0
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false // Hide legend if only one line
                        },
                        title: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            min: 250 // Set a more relevant min for visitor count
                        }
                    }
                }
            });
        }

        // --- 4. Mobile Sidebar Toggle Logic ---
        const sidebar = document.getElementById('sidebar');
        const toggleButton = document.getElementById('sidebar-toggle');
        const mainContent = document.querySelector('main');

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

        // Initialize charts when the window loads
        window.onload = initializeCharts;
    </script>
</body>
</html>
