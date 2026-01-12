
<?php
require_once __DIR__ . "/../bootstrap.php";
require_once __DIR__ . "/../auth/helpers.php";
requireAuth(); // Require authentication - redirects to login if not authenticated

$getdata = (new AttendanceController())->index();

// Get total counts for residents, employees, and visitors
$db = (new Database())->connect();
$residentRepository = new ResidentRepository($db);
$employeeRepository = new EmployeeRepository($db);
$verificationLogRepository = new VerificationLogRepository($db);
$totalResidents = $residentRepository->count();
$totalEmployees = $employeeRepository->getEmployeeCount();

// Get visitor counts (default: all time, will be filtered via JavaScript/AJAX)
$totalVisitors = $verificationLogRepository->count();
$totalResidentVisitors = 0; // Will be calculated via AJAX based on filter
$totalNonResidentVisitors = 0; // Will be calculated via AJAX based on filter
$totalWalkin = 0; // Will be calculated via AJAX based on filter
$totalOnlineAppointment = 0; // Will be calculated via AJAX based on filter

// print_r($getdata["attendancesTodayCount"]);
include_once '../shared/components/Sidebar.php';
include_once '../shared/components/Breadcrumb.php';

// Get current user for greeting
$currentUser = currentUser();
$userName = $currentUser ? ($currentUser['full_name'] ?? $currentUser['username']) : 'Guest';
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

        <?=Sidebar("Dashboard", null)?>

        <!-- 2. MAIN CONTENT AREA -->
        <main class="flex-1 md:ml-64 p-6 transition-all duration-300">

            <!-- Top Header Bar -->
            <header class="mb-6">
                <div class="flex justify-between items-center mb-1">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-800">Dashboard Overview</h1>
                        <p class="text-gray-500 text-sm"><?= getGreeting($userName) ?></p>
                    </div>
                    <p class="text-sm text-gray-500" id="current-date">September 28, 2025</p>
                </div>
                <?php Breadcrumb([['label' => 'Dashboard', 'link' => 'dashboard.php']]); ?>
            </header>

            <!-- DASHBOARD GRID LAYOUT -->
            <div class="space-y-6">

                <!-- TOP ROW: Realtime Insight, Total Residents, Total Employees -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    
                    <!-- Realtime Insight Card (UNCHANGED) -->
                    <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100">
                        <h2 class="font-semibold text-gray-800 mb-4">Realtime Insight</h2>
                        
                        <!-- Connection Status Indicator -->
                        <div class="mb-4 flex items-center space-x-2 text-sm">
                            <span id="ws-status-indicator" class="inline-block w-3 h-3 rounded-full bg-gray-400"></span>
                            <span id="ws-status-text" class="text-gray-600">Connecting...</span>
                        </div>
                        
                        <!-- Clock & Insight (Dynamic weather icon) -->
                        <div class="flex items-center space-x-2">
                            <!-- Weather/Clock Icon (Dynamic based on weather) -->
                            <div id="weather-icon" class="w-6 h-6">
                                <!-- Default sun icon - will be replaced by JavaScript -->
                                <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            </div>
                            <span class="text-4xl font-extrabold text-gray-900" id="realtime-clock">
                                10:20 : 28 AM
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 mb-6 mt-1">Realtime Insight</p>

                        <!-- Today's Date (Separated the "Today:" label) -->
                        <p class="text-base text-gray-500">Today:</p>
                        <p class="text-lg font-bold text-gray-700 mb-2" id="today-date-insight">
                            28th September 2025
                        </p>

                        <!-- Weather Forecast Section -->
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <p class="text-base text-gray-500 mb-2">Weather Forecast:</p>
                            <div id="weather-info" class="text-sm text-gray-600">
                                <div class="flex items-center space-x-2 mb-1">
                                    <span id="weather-condition" class="font-medium">Loading...</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span id="weather-temperature" class="text-lg font-semibold text-gray-800">-</span>
                                    <span id="weather-details" class="text-xs text-gray-500">-</span>
                                </div>
                            </div>
                        </div>

                        <!-- Attendance Button (Dark Blue/Slate Style) -->
                        <button onclick="window.location.href='biometrics://identify'" class="w-full py-3 btn-dark hover:bg-gray-700 text-white font-semibold rounded-lg transition-colors shadow-md text-lg flex items-center justify-center mt-8">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Attendance Now
                        </button>
                    </div>

                    <!-- Total Residents Container -->
                    <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100 flex flex-col justify-between">
                        <div>
                            <h2 class="font-semibold text-gray-800 mb-4">Total Residents</h2>
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-5xl font-bold text-blue-600" id="total-residents-main-count"><?= $totalResidents ?></span>
                                <svg class="w-12 h-12 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <p class="text-sm text-gray-500 mb-4">Total visitors based on selected filter</p>
                            
                            <!-- Total Visitors Row (2-column layout) -->
                            <div class="pt-4 pb-4 border-t border-b border-gray-200">
                                <!-- Visitor Filter Dropdown -->
                                <div class="mb-3 pb-3 border-b border-gray-200">
                                    <select id="visitor-filter-dropdown" class="w-full bg-white text-gray-800 border border-gray-300 rounded-lg px-3 py-1.5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-purple-300">
                                        <option value="today">Today</option>
                                        <option value="week">This Week</option>
                                        <option value="month" selected>This Month</option>
                                        <option value="year">This Year</option>
                                    </select>
                                </div>
                                
                                <!-- Total Visitors Row -->
                                <div class="flex items-center justify-between mb-3 pb-3 border-b border-gray-200">
                                    <span class="text-sm font-medium text-gray-700">Total Visitors</span>
                                    <span class="text-xl font-bold text-purple-600" id="total-visitors-count"><?= $totalVisitors ?></span>
                                </div>
                                
                                <!-- Total Resident Visitors Row -->
                                <div class="flex items-center justify-between mb-2 pt-2 pb-2 border-b border-gray-200">
                                    <span class="text-sm font-medium text-gray-600">Total Resident Visitors</span>
                                    <span class="text-lg font-bold text-orange-600" id="resident-visitors-count"><?= $totalResidentVisitors ?></span>
                                </div>
                                
                                <!-- Total Non-Resident Visitors Row -->
                                <div class="flex items-center justify-between mb-2 pt-2 pb-2 border-b border-gray-200">
                                    <span class="text-sm font-medium text-gray-600">Total Non-Resident Visitors</span>
                                    <span class="text-lg font-bold text-red-600" id="non-resident-visitors-count"><?= $totalNonResidentVisitors ?></span>
                                </div>
                                
                                <!-- Total Walk-in Row -->
                                <div class="flex items-center justify-between mb-2 pt-2 pb-2 border-b border-gray-200">
                                    <span class="text-sm font-medium text-gray-600">Total Walk-in</span>
                                    <span class="text-lg font-bold text-pink-600" id="walkin-count">0</span>
                                </div>
                                
                                <!-- Total Online Appointment Row -->
                                <div class="flex items-center justify-between pt-2 pb-2 border-b border-gray-200">
                                    <span class="text-sm font-medium text-gray-600">Total Online Appointment</span>
                                    <span class="text-lg font-bold text-teal-600" id="online-appointment-count">0</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Employees Container -->
                    <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100 flex flex-col justify-between">
                        <div>
                            <h2 class="font-semibold text-gray-800 mb-4">Total Employees</h2>
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-5xl font-bold text-indigo-600" id="total-employees-main-count"><?= $totalEmployees ?></span>
                                <svg class="w-12 h-12 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <p class="text-sm text-gray-500 mb-4">Total present based on selected filter</p>
                            
                            <!-- Employee Attendance Statistics Row (2-column layout) -->
                            <div class="pt-4 pb-4 border-t border-b border-gray-200">
                                <!-- Employee Attendance Filter Dropdown -->
                                <div class="mb-3 pb-3 border-b border-gray-200">
                                    <select id="employee-attendance-filter-dropdown" class="w-full bg-white text-gray-800 border border-gray-300 rounded-lg px-3 py-1.5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-300">
                                        <option value="today">Today</option>
                                        <option value="week">This Week</option>
                                        <option value="month" selected>This Month</option>
                                        <option value="year">This Year</option>
                                    </select>
                                </div>
                                
                                <!-- Total Present Row -->
                                <div class="flex items-center justify-between mb-3 pb-3 border-b border-gray-200">
                                    <span class="text-sm font-medium text-gray-700">Total Present</span>
                                    <span class="text-xl font-bold text-green-600" id="total-present-count">0</span>
                                </div>
                                
                                <!-- Total Absent Row -->
                                <div class="flex items-center justify-between mb-2 pt-2 pb-2 border-b border-gray-200">
                                    <span class="text-sm font-medium text-gray-600">Total Absent</span>
                                    <span class="text-lg font-bold text-red-600" id="total-absent-count">0</span>
                                </div>
                                
                                <!-- Total Late Row -->
                                <div class="flex items-center justify-between mb-2 pt-2 pb-2 border-b border-gray-200">
                                    <span class="text-sm font-medium text-gray-600">Total Late</span>
                                    <span class="text-lg font-bold text-yellow-600" id="total-late-count">0</span>
                                </div>
                                
                                <!-- Total Over-Time Row -->
                                <div class="flex items-center justify-between pt-2 pb-2 border-b border-gray-200">
                                    <span class="text-sm font-medium text-gray-600">Total Over-Time</span>
                                    <span class="text-lg font-bold text-indigo-600" id="total-overtime-count">0</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- CHARTS SECTION: Employee Attendance Metrics and Visitor & Traffic Metrics -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    <!-- Employee Attendance Metrics Chart -->
                    <div class="bg-white rounded-xl shadow-lg border border-gray-100">
                        <!-- Header with Filter Dropdown -->
                        <div class="metric-header-employee p-4 rounded-t-xl text-white font-bold text-lg flex items-center justify-between">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20v-2c0-.656-.126-1.283-.356-1.857M9 20H7l-1-1v-6a1 1 0 011-1h10a1 1 0 011 1v6l-1 1h-2"></path>
                                </svg>
                                Employee Attendance Metrics
                            </div>
                            <select id="attendance-filter" class="bg-white text-gray-800 border border-gray-300 rounded-lg px-3 py-1.5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-300">
                                <option value="today">Today</option>
                                <option value="week">This Week</option>
                                <option value="month" selected>This Month</option>
                                <option value="year">This Year</option>
                            </select>
                        </div>
                        <div class="p-6 h-96">
                            <canvas id="employeeAttendanceChart"></canvas>
                        </div>
                    </div>

                    <!-- Visitor & Traffic Metrics Chart -->
                    <div class="bg-white rounded-xl shadow-lg border border-gray-100">
                        <!-- Header with Filter Dropdown -->
                        <div class="metric-header-visitor p-4 rounded-t-xl text-white font-bold text-lg flex items-center justify-between">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                                Visitor & Traffic Metrics
                            </div>
                            <select id="visitor-filter" class="bg-white text-gray-800 border border-gray-300 rounded-lg px-3 py-1.5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-green-300">
                                <option value="today">Today</option>
                                <option value="week">This Week</option>
                                <option value="month" selected>This Month</option>
                                <option value="year">This Year</option>
                            </select>
                        </div>
                        <div class="p-6 h-96">
                            <canvas id="visitorTrafficChart"></canvas>
                        </div>
                    </div>

                </div>

            </div>

        </main>
    </div>

    <!-- JavaScript Module for Dashboard -->
    <script>
        // Set WebSocket URL as global variable for main.js module
        window.WEBSOCKET_URL = "<?php echo WEBSOCKET_URL; ?>";
    </script>
    <script type="module" src="js/dashboard/main.js"></script>
</body>
</html>
