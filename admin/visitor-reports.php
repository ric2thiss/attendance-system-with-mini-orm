<?php
require_once __DIR__ . "/../bootstrap.php";
require_once __DIR__ . "/../auth/helpers.php";
requireAuth(); // Require authentication - redirects to login if not authenticated

include_once '../shared/components/Sidebar.php';
include_once '../shared/components/Breadcrumb.php';

// Get current user for greeting
$currentUser = currentUser();
$userName = $currentUser ? ($currentUser['full_name'] ?? $currentUser['username']) : 'Guest';

// Get date range parameters (default to current month)
$fromDate = isset($_GET['from']) ? trim($_GET['from']) : date('Y-m-01');
$toDate = isset($_GET['to']) ? trim($_GET['to']) : date('Y-m-t');
$reportType = isset($_GET['type']) ? trim($_GET['type']) : 'total-visitors';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visitor Reports</title>
    <link rel="stylesheet" href="../utils/styles/global.css">
    <!-- Load Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Load Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        /* Prevent body horizontal scroll */
        body {
            overflow-x: hidden;
        }
    </style>
</head>
<body>

    <!-- Main Container -->
    <div class="flex min-h-screen">

        <?=Sidebar("Visitor Reports", null)?>

        <!-- 2. MAIN CONTENT AREA -->
        <main class="flex-1 md:ml-64 p-6 transition-all duration-300">

            <!-- Top Header Bar -->
            <header class="mb-6">
                <div class="flex justify-between items-center mb-1">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-800">Visitor Reports</h1>
                        <p class="text-gray-500 text-sm"><?= getGreeting($userName) ?></p>
                    </div>
                    <div>
                        <button id="exportReportBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow-md transition-colors">
                            <svg class="w-5 h-5 inline mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            Export Report
                        </button>
                    </div>
                </div>
                <?php Breadcrumb([
                    ['label' => 'Dashboard', 'link' => 'dashboard.php'],
                    ['label' => 'Visitor Reports', 'link' => 'visitor-reports.php']
                ]); ?>
            </header>

            <!-- REPORT FILTERS AND CONTROLS -->
            <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100 mb-8 flex flex-col md:flex-row gap-4 items-end">
                
                <!-- Report Type Dropdown -->
                <div class="w-full md:w-1/3">
                    <label for="reportType" class="block text-sm font-medium text-gray-700 mb-1">Select Report Type</label>
                    <select id="reportType" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-lg border">
                        <option value="total-visitors" <?= $reportType === 'total-visitors' ? 'selected' : '' ?>>Total Visitors</option>
                        <option value="services-availed" <?= $reportType === 'services-availed' ? 'selected' : '' ?>>Services Availed by Visitors</option>
                        <option value="visitor-types" <?= $reportType === 'visitor-types' ? 'selected' : '' ?>>Types of Visitors (Residents/Non-Residents)</option>
                        <option value="appointment-types" <?= $reportType === 'appointment-types' ? 'selected' : '' ?>>Appointment Types (Online/Walk-in)</option>
                        <option value="gender-distribution" <?= $reportType === 'gender-distribution' ? 'selected' : '' ?>>Gender Distribution</option>
                        <option value="age-services" <?= $reportType === 'age-services' ? 'selected' : '' ?>>Age Groups & Services Availed</option>
                    </select>
                </div>

                <!-- Month Filter -->
                <div class="w-full md:w-1/4">
                    <label for="monthFilter" class="block text-sm font-medium text-gray-700 mb-1">Month</label>
                    <input type="month" id="monthFilter" value="<?= date('Y-m') ?>" class="mt-1 block w-full pl-3 pr-3 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-lg border">
                </div>

                <!-- Date Range (Start) -->
                <div class="w-full md:w-1/4">
                    <label for="startDate" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input type="date" id="startDate" value="<?= htmlspecialchars($fromDate) ?>" class="mt-1 block w-full pl-3 pr-3 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-lg border">
                </div>

                <!-- Date Range (End) -->
                <div class="w-full md:w-1/4">
                    <label for="endDate" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                    <input type="date" id="endDate" value="<?= htmlspecialchars($toDate) ?>" class="mt-1 block w-full pl-3 pr-3 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-lg border">
                </div>

                <!-- Run Report Button -->
                <div class="w-full md:w-1/6">
                    <button id="runReportBtn" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow-md transition-colors">
                        Run Report
                    </button>
                </div>
            </div>

            <!-- Loading Indicator -->
            <div id="loadingIndicator" class="bg-white p-6 rounded-xl shadow-lg border border-gray-100 mb-8 text-center hidden">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <p class="mt-2 text-gray-600">Loading report data...</p>
            </div>

            <!-- CHART VISUALIZATION SECTION -->
            <div id="chartSection" class="bg-white p-6 rounded-xl shadow-lg border border-gray-100 mb-8">
                <h2 id="chartTitle" class="text-xl font-semibold text-gray-800 mb-4">Report Data</h2>
                <div id="chartContainer" class="w-full overflow-x-auto" style="position: relative; height: 400px;">
                    <canvas id="reportChart"></canvas>
                </div>
            </div>

            <!-- RAW DATA TABLE SECTION -->
            <div id="tableSection" class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Detailed Report Data</h2>

                <!-- Table Header -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr id="tableHeader">
                                <!-- Headers will be populated by JavaScript -->
                            </tr>
                        </thead>
                        <tbody id="tableBody" class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                    <p class="text-sm">Select a report type and click "Run Report" to view data.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    <!-- JavaScript Module for Visitor Reports -->
    <script type="module" src="js/visitor-reports/main.js"></script>

</body>
</html>
