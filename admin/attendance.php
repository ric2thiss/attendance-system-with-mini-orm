<?php
require_once __DIR__ . "/../bootstrap.php";
include_once '../shared/components/Sidebar.php';

$attendances = Attendance::query()->table("attendances AS a")
    ->select("a.id AS attendance_id, a.employee_id, CONCAT(r.first_name, ' ', r.last_name) AS full_name, a.timestamp AS attendance_time, a.window")
    ->join("employees AS e", "a.employee_id", "=", " e.employee_id")
    ->join("residents AS r", "e.resident_id", "=", "r.resident_id")
    ->orderBy("a.timestamp", "DESC")
    ->get();

// var_dump($attendances);

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

        /* Custom darker button color for the new Attendance Now style */
        .btn-dark {
            background-color: #374151; /* A deep slate color */
        }
        .btn-dark:hover {
            background-color: #1f2937;
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
                        <p class="text-gray-500 text-sm">Good morning, Juan</p>
                    </div>
                    <p class="text-sm text-gray-500" id="current-date">September 28, 2025</p>
                </div>
                <!-- Breadcrumb -->
                <p class="text-xs text-gray-500 mt-2">Home / Attendance Logging</p>

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
                        
                        <!-- Clock & Insight (Added sun icon and adjusted text style) -->
                        <div class="flex items-center space-x-2">
                            <!-- Sun/Clock Icon (Stylized to match yellow accents) -->
                            <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            <span class="text-4xl font-extrabold text-gray-900" id="realtime-clock">
                                10:20 : 28 AM
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 mb-6 mt-1">Realtime Insight</p>

                        <!-- Today's Date (Separated the "Today:" label) -->
                        <p class="text-base text-gray-500">Today:</p>
                        <p class="text-lg font-bold text-gray-700 mb-6" id="today-date-insight">
                            28th September 2025
                        </p>

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
                        <div class="flex flex-shrink-0 space-x-4 mb-4 md:mb-0 md:mr-6">
                            <!-- <div class="w-24 h-24 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400 text-xs flex-col p-2"> -->
                                <!-- Mock Fingerprint Icon -->
                                <!-- <svg class="w-8 h-8 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.314-2.686 6-6 6s-6-2.686-6-6 2.686-6 6-6c3.314 0 6 2.686 6 6zm0 0V3m0 0V1M6 17v4m0 0H3m3 0h6m-6-4H3m9 4v4m0 0H9m3 0h3"></path></svg>
                                FINGERPRINT
                            </div> -->
                            <div class="w-24 h-100 bg-gray-200 rounded-lg flex items-center justify-center text-gray-400 text-xs">
                                <img src="./logo.png" alt="">
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
                        <div class="flex mb-4 space-x-3">
                            <div class="relative flex-grow">
                                <input type="text" id="search-employee-record" placeholder="Search Employee Name"
                                    class="w-full p-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 transition">
                            </div>
                            <button class="px-4 py-2 bg-gray-800 hover:bg-gray-900 text-white font-medium rounded-lg transition-colors shadow-md text-sm">
                                Search
                            </button>
                        </div>
                        
                        <!-- Table Wrapper for Horizontal Scroll on small screens -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date / Time</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <!-- Sample Log 1 -->
                                     <?php foreach($attendances as $attendance):?>
                                    <tr class="hover:bg-gray-50 transition duration-150">
                                        <td class="px-3 py-3 whitespace-nowrap text-sm font-medium text-gray-900"><?=$attendance->employee_id?></td>
                                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-700"><?=$attendance->full_name?></td>
                                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500"><?=$attendance->attendance_time?></td>
                                        <td class="px-3 py-3 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800"><?=$attendance->window?></span>
                                        </td>
                                    </tr>
                                    <?php endforeach ?>
                                    
                                    <!-- Sample Log 2 -->
                                    <!-- <tr class="hover:bg-gray-50 transition duration-150">
                                        <td class="px-3 py-3 whitespace-nowrap text-sm font-medium text-gray-900">EMP002</td>
                                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-700">John Smith</td>
                                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500">2025-09-28 05:00 PM</td>
                                        <td class="px-3 py-3 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Time Out</span>
                                        </td>
                                    </tr> -->

                                    <!-- Sample Log 3 -->
                                    <!-- <tr class="hover:bg-gray-50 transition duration-150">
                                        <td class="px-3 py-3 whitespace-nowrap text-sm font-medium text-gray-900">EMP003</td>
                                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-700">Urey G. Gorge</td>
                                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500">2025-09-28 08:05 AM</td>
                                        <td class="px-3 py-3 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Time In</span>
                                        </td>
                                    </tr> -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination (Mockup) -->
                        <div class="mt-6 flex justify-end items-center text-sm text-gray-600">
                            <p>Showing 1 - 10 of 100 records</p>
                        </div>
                    </div>
                </div>

            </div>

        </main>
    </div>

    <!-- JavaScript for Date, Time, and Sidebar Toggle -->
    <script>
        // Data table 

       // ✅ Connect to WebSocket server
        const socket = new WebSocket("ws://localhost:8080");

        // --- Handle connection open ---
        socket.onopen = () => {
        console.log("✅ Connected to WebSocket server");
        };

        // --- Handle incoming messages ---
        socket.onmessage = (event) => {
        try {
            const data = JSON.parse(event.data);
            if (!data) return;

            const { lastAttendee, lastAttendeeEmployee, lastAttendeeResident } = data;
            if (!lastAttendee) return;

            // --- Elements ---
            const timeInEl = document.getElementById("time_in");
            const timeOutEl = document.getElementById("time_out");
            const roleEl = document.getElementById("role");
            const empIdEl = document.getElementById("employee_id");
            const nameEl = document.getElementById("name");
            const windowEl = document.getElementById("window");

            // --- Format time ---
            const date = new Date(lastAttendee.created_at.replace(" ", "T"));
            const formattedTime = date.toLocaleTimeString([], {
            hour: "2-digit",
            minute: "2-digit",
            second: "2-digit",
            });

            // --- Display Employee Info ---
            roleEl.textContent = lastAttendeeEmployee?.position || "N/A";
            empIdEl.textContent = lastAttendee.employee_id || "Unknown";

            const firstName = lastAttendeeResident?.first_name || "";
            const lastName = lastAttendeeResident?.last_name || "";
            nameEl.textContent = `${firstName} ${lastName}`.trim() || "Unnamed";

            // --- Display Attendance Window ---
            const windowMap = {
                morning_in: "Morning In",
                morning_out: "Morning Out",
                afternoon_in: "Afternoon In",
                afternoon_out: "Afternoon Out",
            };

            const windowText = windowMap[lastAttendee.window] || "Unknown Window";
            windowEl.textContent = windowText;

            // --- Display Time In / Out ---
            if (windowText.includes("In")) {
                timeInEl.textContent = formattedTime;
                timeOutEl.textContent = "-";
            } else if (windowText.includes("Out")) {
                timeOutEl.textContent = formattedTime;
                timeInEl.textContent = "-";
            } else {
                timeInEl.textContent = "-";
                timeOutEl.textContent = "-";
            }

        } catch (error) {
            console.error("❌ Error parsing WebSocket message:", error);
        }
        };

        // --- Handle disconnection ---
        socket.onclose = () => {
            console.log("❌ Disconnected from WebSocket server");
        };



        // --- 1. Update Header Date ---
        function updateHeaderDate() {
            const now = new Date();
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            const dateElement = document.getElementById('current-date');
            if(dateElement) {
                dateElement.textContent = now.toLocaleDateString('en-US', options);
            }
        }
        updateHeaderDate();

        // --- 2. Update Realtime Clock and Today's Date ---
        function updateRealtimeClock() {
            const now = new Date();
            
            // Clock
            const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
            const clockElement = document.getElementById('realtime-clock');
            
            // Format time: HH:MM : SS AM/PM
            const timeParts = now.toLocaleTimeString('en-US', timeOptions).split(' ');
            const time = timeParts[0];
            const ampm = timeParts[1];
            // Replace the colon in HH:MM with a space-colon-space, and add SS with space-colon-space
            const formattedTime = time.replace(/:/g, ' : ') + ' ' + ampm;

            if (clockElement) {
                // Example: 10:20 : 28 AM
                clockElement.textContent = formattedTime;
            }

            // Today's Date (Format: 28th September 2025)
            const dateOptions = { day: 'numeric', month: 'long', year: 'numeric' };
            const day = now.getDate();
            let daySuffix;

            if (day > 3 && day < 21) { // 11th to 19th
                daySuffix = 'th';
            } else {
                switch (day % 10) {
                    case 1:  daySuffix = "st"; break;
                    case 2:  daySuffix = "nd"; break;
                    case 3:  daySuffix = "rd"; break;
                    default: daySuffix = "th";
                }
            }

            // Create the date string in the format: "28th September 2025"
            const monthYear = now.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
            const dateString = day + daySuffix + ' ' + monthYear;
            
            const dateInsightElement = document.getElementById('today-date-insight');
            if (dateInsightElement) {
                dateInsightElement.textContent = dateString;
            }
        }
        
        // Update the clock every second
        setInterval(updateRealtimeClock, 1000);
        updateRealtimeClock(); // Initial call


        // --- 3. Mobile Sidebar Toggle Logic ---
        const sidebar = document.getElementById('sidebar');
        const toggleButton = document.getElementById('sidebar-toggle');
        const mainContent = document.querySelector('main');

        toggleButton.addEventListener('click', () => {
            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('translate-x-0');
                // Dim the main content when the sidebar is open on mobile
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
    </script>
</body>
</html>
