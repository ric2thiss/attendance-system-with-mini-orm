<?php
require_once __DIR__ . "/../bootstrap.php";
include_once '../shared/components/Sidebar.php';

// For employee table
$employeesData = (new EmployeeController())->getAllEmployees();
// $employeeCurrentActivities = (new EmployeeController())->getEmployeeCurrentActivity();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Directory</title>
    <!-- Load global css -->
    <link rel="stylesheet" href="../utils/styles/global.css">
    <!-- Load Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Use Inter font family and custom styles from the dashboard -->
    <style>
        /* Style for the action buttons */
        .btn-primary {
            background-color: #007bff; /* Primary blue */
            transition: background-color 0.2s;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        /* Table styles */
        .table-header {
            background-color: #e5e7eb; /* Light gray for table header */
        }
    </style>
</head>
<body>

    <!-- Main Container -->
    <div class="flex min-h-screen">

        <?=Sidebar("Employees", null)?>

        <!-- 2. MAIN CONTENT AREA -->
        <main class="flex-1 md:ml-64 p-6 transition-all duration-300">

            <!-- Top Header Bar -->
            <header class="mb-6">
                <div class="flex justify-between items-center mb-1">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-800">Employee Directory</h1>
                        <p class="text-gray-500 text-sm">Manage all current and past employees in one place.</p>
                    </div>
                </div>
            </header>

            <!-- EMPLOYEE MANAGEMENT SECTION -->
            <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100">
                
                <!-- Controls: Search, Filter, and Add Button -->
                <div class="flex flex-col sm:flex-row justify-between items-center mb-6 space-y-4 sm:space-y-0">
                    
                    <!-- Search Input -->
                    <div class="relative w-full sm:w-1/2 lg:w-1/3">
                        <input type="text" placeholder="Search employee name, ID, or department..." class="w-full py-2 pl-10 pr-4 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>

                    <!-- Add Employee Button -->
                    <button class="w-full sm:w-auto px-6 py-2 text-white font-semibold rounded-lg btn-primary shadow-md flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Add New Employee
                    </button>
                </div>

                <!-- Employee Table -->
                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="table-header">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Employee ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Department</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Position</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($employeesData["employees"] as $employee): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?= $employee->employee_id ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <?= $employee->first_name . ' ' . $employee->last_name ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    BARANGAY
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <?= $employee->position ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                     <?= $employee->activity_name ? $employee->activity_name : "Office"; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium space-x-2">
                                    <button class="text-indigo-600 hover:text-indigo-900 transition-colors">Edit</button>
                                    <button class="text-red-600 hover:text-red-900 transition-colors">Delete</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>

                    </table>
                </div>

                <!-- Pagination Placeholder -->
                <div class="mt-4 flex justify-between items-center text-sm text-gray-600">
                    <div>
                        Showing <span class="font-medium">1</span> to <span class="font-medium">10</span> of <span class="font-medium">32</span> results
                    </div>
                    <nav class="flex space-x-1" aria-label="Pagination">
                        <a href="#" class="p-2 border rounded-lg hover:bg-gray-100">Previous</a>
                        <a href="#" class="p-2 border rounded-lg bg-blue-600 text-white hover:bg-blue-700">1</a>
                        <a href="#" class="p-2 border rounded-lg hover:bg-gray-100">2</a>
                        <a href="#" class="p-2 border rounded-lg hover:bg-gray-100">3</a>
                        <a href="#" class="p-2 border rounded-lg hover:bg-gray-100">Next</a>
                    </nav>
                </div>
            </div>


        </main>
    </div>

    <!-- JavaScript for Sidebar Toggle -->
    <script>
        // --- Mobile Sidebar Toggle Logic ---
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
    </script>
</body>
</html>
