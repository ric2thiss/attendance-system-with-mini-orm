<?php
require_once __DIR__ . "/../bootstrap.php";
require_once __DIR__ . "/../auth/helpers.php";
requireAuth(); // Require authentication - redirects to login if not authenticated

include_once '../shared/components/Sidebar.php';
include_once '../shared/components/Breadcrumb.php';

// Get current user for greeting
$currentUser = currentUser();
$userName = $currentUser ? ($currentUser['full_name'] ?? $currentUser['username']) : 'Guest';

// Get pagination and search parameters
$currentPage = isset($_GET['page']) ? $_GET['page'] : 1;
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$perPage = 10; // Records per page

// Get data from controller
$attendanceController = new AttendanceController();
$data = $attendanceController->getPaginatedAttendances($currentPage, $perPage, $searchQuery);

// Extract data for view
$attendances = $data['attendances'];
$pagination = $data['pagination'];
$searchQuery = $data['searchQuery'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Attendance</title>
    <link rel="stylesheet" href="../utils/styles/global.css">
    <!-- Load Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Use Inter font family -->
    <style>
        /* Prevent body horizontal scroll */
        body {
            overflow-x: hidden;
        }
        /* Custom darker button color for the new Attendance Now style */
        .btn-dark {
            background-color: #374151; /* A deep slate color */
        }
        .btn-dark:hover {
            background-color: #1f2937;
        }
        /* Table styles */
        .table-header {
            background-color: #e5e7eb; /* Light gray for table header */
        }
        /* Scrollable table container */
        .table-container {
            overflow-x: auto;
            overflow-y: visible;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e0 #f7fafc;
            width: 100%;
            position: relative;
        }
        .table-container::-webkit-scrollbar {
            height: 8px;
        }
        .table-container::-webkit-scrollbar-track {
            background: #f7fafc;
            border-radius: 4px;
        }
        .table-container::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 4px;
        }
        .table-container::-webkit-scrollbar-thumb:hover {
            background: #a0aec0;
        }
        /* Add border to each table row */
        tbody tr {
            border-bottom: 1px solid #e5e7eb;
        }
        tbody tr:last-child {
            border-bottom: none;
        }
        /* Ensure main content doesn't overflow */
        main {
            overflow-x: hidden;
            max-width: 100%;
        }
        /* Toast Notification Styles */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            max-width: 400px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            transform: translateX(400px);
            opacity: 0;
            transition: all 0.3s ease-in-out;
        }
        .toast.show {
            transform: translateX(0);
            opacity: 1;
        }
        .toast.hide {
            transform: translateX(400px);
            opacity: 0;
        }
        .toast-icon {
            flex-shrink: 0;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .toast-icon.success {
            background: #10b981;
        }
        .toast-icon.error {
            background: #ef4444;
        }
        .toast.success {
            border-left: 4px solid #10b981;
        }
        .toast.error {
            border-left: 4px solid #ef4444;
        }
        .toast-content {
            flex: 1;
        }
        .toast-title {
            font-weight: 600;
            color: #111827;
            margin-bottom: 4px;
        }
        .toast-message {
            font-size: 14px;
            color: #6b7280;
        }
    </style>
</head>
<body>

    <!-- Main Container -->
    <div class="flex min-h-screen">

        
        <?=Sidebar("Attendance Logs", null)?>

        <!-- 2. MAIN CONTENT AREA -->
        <main class="flex-1 md:ml-64 p-6 transition-all duration-300">

            <!-- Top Header Bar -->
            <header class="mb-6">
                <div class="flex justify-between items-center mb-1">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-800">Employee Attendance</h1>
                        <p class="text-gray-500 text-sm"><?= getGreeting($userName) ?></p>
                    </div>
                    <p class="text-sm text-gray-500" id="current-date">September 28, 2025</p>
                </div>
                <?php Breadcrumb([
                    ['label' => 'Dashboard', 'link' => 'dashboard.php'],
                    ['label' => 'Attendance Logs', 'link' => 'attendance.php']
                ]); ?>

                <!-- Top Action Buttons (Attendance Now & Export) -->
                <div class="flex justify-end space-x-3 mt-4">
                    <button onclick="window.location.href='biometrics://identify'" class="flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors shadow-md text-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Attendance Now
                    </button>
                    <button class="flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg transition-colors shadow-md text-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Export Attendance Logs
                    </button>
                </div>
            </header>
            
            <!-- NEW TWO-COLUMN CONTENT GRID -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- LEFT COLUMN (Realtime Insight & Filter) - Takes 1/3 width on desktop -->
                <div class="lg:col-span-1 space-y-6">
                    
                    <!-- Realtime Insight Card (UPDATED SECTION) -->
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
                        <button onclick="window.location.href='biometrics://identify'" class="w-full py-3 btn-dark hover:bg-gray-700 text-white font-semibold rounded-lg transition-colors shadow-md text-lg flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Attendance Now
                        </button>
                    </div>

                    <!-- Filter Card -->
                    <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100">
                        <h2 class="font-semibold text-gray-800 mb-4">Filter</h2>
                        
                        <div class="space-y-4">
                            <!-- From Date -->
                            <div>
                                <label for="filter-from" class="block text-sm font-medium text-gray-500 mb-1">From</label>
                                <div class="relative">
                                    <input type="text" id="filter-from" placeholder="mm/dd/yyyy"
                                        class="w-full p-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 transition pr-10">
                                    <svg class="w-4 h-4 text-gray-400 absolute right-3 top-1/2 transform -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                            </div>

                            <!-- To Date -->
                            <div>
                                <label for="filter-to" class="block text-sm font-medium text-gray-500 mb-1">To</label>
                                <div class="relative">
                                    <input type="text" id="filter-to" placeholder="mm/dd/yyyy"
                                        class="w-full p-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 transition pr-10">
                                    <svg class="w-4 h-4 text-gray-400 absolute right-3 top-1/2 transform -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                            </div>

                            <button class="w-full py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg transition-colors shadow-sm text-sm">
                                Apply Filter
                            </button>
                        </div>
                    </div>

                </div>
                
                <!-- RIGHT COLUMN (Recent Log & Attendance Records) - Takes 2/3 width on desktop -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Recent Log/Details Card (Combined Top Right section) -->
                    <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100 flex flex-col md:flex-row items-stretch">
                        
                        <!-- Fingerprint/Photo Area -->
                        <div class="flex flex-shrink-0 mb-4 md:mb-0 md:mr-6 items-start">
                            <div class="w-36 h-36 rounded-lg overflow-hidden bg-gray-100 flex items-center justify-center">
                                <img src="./logo.png" alt="" id="employee_photo" class="w-full h-full object-cover">
                            </div>
                        </div>

                        <!-- Employee Details -->
                        <div class="flex-grow grid grid-cols-2 gap-x-6 gap-y-2 border-t md:border-t-0 pt-4 md:pt-0">
                            
                            <div class="col-span-2">
                                <h3 class="text-xl font-bold text-gray-900" id="name">Gorge, Urey G.</h3>
                                <p class="text-sm text-gray-500">Employee</p>
                            </div>

                            <div class="col-span-1">
                                <p class="text-xs text-gray-500 uppercase">Role</p>
                                <p class="font-medium text-gray-700" id="role">asd</p>
                            </div>
                            <div class="col-span-1">
                                <p class="text-xs text-gray-500 uppercase">Employee ID</p>
                                <p class="font-medium text-gray-700" id="employee_id"></p>
                            </div>

                            <div class="col-span-1">
                                <p class="text-xs text-gray-500 uppercase">Time In</p>
                                <p class="font-medium text-gray-700" id="time_in">8:05 AM</p>
                            </div>
                            <div class="col-span-1">
                                <p class="text-xs text-gray-500 uppercase">Time Out</p>
                                <p class="font-medium text-gray-700" id="time_out">-</p>
                            </div>

                            <!-- Morning In Status -->
                            <div class="col-span-2 mt-2">
                                <span class="inline-flex items-center text-green-600 font-semibold text-sm">
                                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span id="window"></span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Attendance Records Table Card -->
                    <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">Attendances Records</h2>
                        
                        <!-- Search & Search Button (Updated layout) -->
                        <form method="GET" action="" class="mb-4">
                            <div class="relative w-full sm:w-1/2 lg:w-1/3">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                <input type="text" 
                                    id="search-employee-record" 
                                    name="search" 
                                    placeholder="Search employee name, ID, or status..."
                                    value="<?= htmlspecialchars($searchQuery) ?>"
                                    class="w-full py-2 pl-10 pr-10 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                <?php if (!empty($searchQuery)): ?>
                                <button type="button" onclick="window.location.href='?'" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                                <?php endif; ?>
                            </div>
                        </form>
                        
                        <!-- Table Wrapper for Horizontal Scroll on small screens -->
                        <div class="table-container rounded-lg border border-gray-200">
                            <div class="inline-block min-w-full align-middle">
                                <table class="min-w-full divide-y divide-gray-200" style="min-width: 800px;">
                                <thead class="table-header">
                                    <tr>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date / Time</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="attendance-table-body" class="bg-white divide-y divide-gray-200">
                                    <?php if (empty($attendances)): ?>
                                    <tr id="no-records-row">
                                        <td colspan="4" class="px-3 py-8 text-center text-gray-500">
                                            <p class="text-sm">No attendance records found.</p>
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach($attendances as $attendance):?>
                                    <tr class="hover:bg-gray-50 transition duration-150">
                                        <td class="px-3 py-3 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($attendance->employee_id ?? '') ?></td>
                                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-700"><?= htmlspecialchars($attendance->full_name ?? '') ?></td>
                                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($attendance->attendance_time ?? '') ?></td>
                                        <td class="px-3 py-3 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800"><?= htmlspecialchars($attendance->window ?? '') ?></span>
                                        </td>
                                    </tr>
                                    <?php endforeach ?>
                                    <?php endif; ?>
                                
                                </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6 flex flex-col sm:flex-row justify-between items-center gap-4 text-sm text-gray-600">
                            <div>
                                Showing <span class="font-medium"><?= $pagination['startRecord'] ?></span> to <span class="font-medium"><?= $pagination['endRecord'] ?></span> of <span class="font-medium"><?= $pagination['totalRecords'] ?></span> records
                                <?php if (!empty($searchQuery)): ?>
                                    <span class="text-gray-500">(filtered)</span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($pagination['totalPages'] > 1): ?>
                            <nav class="flex space-x-1" aria-label="Pagination">
                                <!-- Previous Button -->
                                <?php 
                                // Build query string for pagination links
                                $queryString = !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : '';
                                ?>
                                <?php if ($pagination['currentPage'] > 1): ?>
                                    <a href="?page=<?= $pagination['currentPage'] - 1 ?><?= $queryString ?>" class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-100 transition-colors">
                                        Previous
                                    </a>
                                <?php else: ?>
                                    <span class="px-3 py-2 border border-gray-300 rounded-lg text-gray-400 cursor-not-allowed">Previous</span>
                                <?php endif; ?>
                                
                                <!-- Page Numbers -->
                                <?php
                                $startPage = max(1, $pagination['currentPage'] - 2);
                                $endPage = min($pagination['totalPages'], $pagination['currentPage'] + 2);
                                
                                // Show first page if not in range
                                if ($startPage > 1): ?>
                                    <a href="?page=1<?= $queryString ?>" class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-100 transition-colors">1</a>
                                    <?php if ($startPage > 2): ?>
                                        <span class="px-3 py-2 text-gray-500">...</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                    <?php if ($i == $pagination['currentPage']): ?>
                                        <span class="px-3 py-2 border border-gray-300 rounded-lg bg-blue-600 text-white font-medium"><?= $i ?></span>
                                    <?php else: ?>
                                        <a href="?page=<?= $i ?><?= $queryString ?>" class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-100 transition-colors"><?= $i ?></a>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                
                                <!-- Show last page if not in range -->
                                <?php if ($endPage < $pagination['totalPages']): ?>
                                    <?php if ($endPage < $pagination['totalPages'] - 1): ?>
                                        <span class="px-3 py-2 text-gray-500">...</span>
                                    <?php endif; ?>
                                    <a href="?page=<?= $pagination['totalPages'] ?><?= $queryString ?>" class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-100 transition-colors"><?= $pagination['totalPages'] ?></a>
                                <?php endif; ?>
                                
                                <!-- Next Button -->
                                <?php if ($pagination['currentPage'] < $pagination['totalPages']): ?>
                                    <a href="?page=<?= $pagination['currentPage'] + 1 ?><?= $queryString ?>" class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-100 transition-colors">
                                        Next
                                    </a>
                                <?php else: ?>
                                    <span class="px-3 py-2 border border-gray-300 rounded-lg text-gray-400 cursor-not-allowed">Next</span>
                                <?php endif; ?>
                            </nav>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>

        </main>
    </div>

    <!-- Toast Notification (Success/Error) -->
    <div id="attendance-toast" class="toast">
        <div class="toast-icon success">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        <div class="toast-content">
            <div class="toast-title" id="toast-title">Attendance Logged Successfully</div>
            <div class="toast-message" id="toast-message">New attendance record has been added.</div>
        </div>
    </div>

    <!-- Pass PHP config values to JavaScript via meta tags -->
    <meta name="websocket-url" content="<?php echo htmlspecialchars(WEBSOCKET_URL); ?>">
    <meta name="attendance-api-url" content="<?php echo htmlspecialchars(API_ENDPOINT_ATTENDANCES); ?>">
    
    <!-- Modular JavaScript Entry Point -->
    <script type="module" 
            data-websocket-url="<?php echo htmlspecialchars(WEBSOCKET_URL); ?>"
            data-attendance-api-url="<?php echo htmlspecialchars(API_ENDPOINT_ATTENDANCES); ?>"
            src="./js/attendance/main.js"></script>

</body>
</html>