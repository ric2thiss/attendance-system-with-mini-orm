<?php
require_once __DIR__ . "/../bootstrap.php";
require_once __DIR__ . "/../auth/helpers.php";
requireAuth(); // Require authentication - redirects to login if not authenticated

include_once '../shared/components/Sidebar.php';
include_once '../shared/components/Breadcrumb.php';

// Get current user for greeting
$currentUser = currentUser();
$userName = $currentUser ? ($currentUser['full_name'] ?? $currentUser['username']) : 'Guest';

// Pagination and search parameters
$perPage = 10; // Records per page
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

// Fetch data from controller
$employeeController = new EmployeeController();
$data = $employeeController->getPaginatedEmployees($currentPage, $perPage, $searchQuery);

$employees = $data['employees'];
$pagination = $data['pagination'];
$searchQuery = $data['searchQuery'];

$totalRecords = $pagination['totalRecords'];
$totalPages = $pagination['totalPages'];
$startRecord = $pagination['startRecord'];
$endRecord = $pagination['endRecord'];

// For employee table (keeping original structure for compatibility)
$employeesData = ["employees" => $employees];

$residents = (new ResidentController())->getAllResidentNotEmployee();
$departmentLists = (new DepartmentController())->getDepartmentLists();
$positions = (new PositionController())->getAllPosition();
$lastEmployeeId = $employeeController->getLastEmployeeId();

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
        /* Prevent body horizontal scroll */
        body {
            overflow-x: hidden;
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
                        <p class="text-gray-500 text-sm"><?= getGreeting($userName) ?> - Manage all current and past employees in one place.</p>
                    </div>
                </div>
                <?php Breadcrumb([
                    ['label' => 'Dashboard', 'link' => 'dashboard.php'],
                    ['label' => 'Employees', 'link' => 'employees.php']
                ]); ?>
            </header>

            <!-- EMPLOYEE MANAGEMENT SECTION -->
            <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                
                <!-- Controls: Search, Filter, and Add Button -->
                <div class="flex flex-col sm:flex-row justify-between items-center mb-6 space-y-4 sm:space-y-0">
                    
                    <!-- Search Input -->
                    <form method="GET" action="" class="relative w-full sm:w-1/2 lg:w-1/3">
                        <input type="text" 
                            name="search" 
                            placeholder="Search employee name, ID, or position..." 
                            value="<?= htmlspecialchars($searchQuery) ?>"
                            class="w-full py-2 pl-10 pr-4 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <?php if (!empty($searchQuery)): ?>
                        <a href="?" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </a>
                        <?php endif; ?>
                    </form>

                    <!-- Add Employee Button -->
                    <button class="w-full sm:w-auto px-6 py-2 text-white font-semibold rounded-lg btn-primary shadow-md flex items-center justify-center" id="openAddEmployeeModal">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Add New Employee
                    </button>
                </div>

                <!-- Employee Table -->
                <div class="table-container rounded-lg border border-gray-200">
                    <div class="inline-block min-w-full align-middle">
                        <table class="min-w-full divide-y divide-gray-200" style="min-width: 1000px; width: 100%;">
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
                            <?php if (empty($employeesData["employees"])): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    <p class="text-sm">No employees found.</p>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($employeesData["employees"] as $employee): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?= htmlspecialchars($employee->employee_id ?? '') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <?= htmlspecialchars(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? '')) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    BARANGAY
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <?= htmlspecialchars($employee->position_name ?? '') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                     <?= htmlspecialchars($employee->activity_name ? $employee->activity_name : "Office") ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        <button 
                                            class="editBtn inline-flex items-center px-3 py-1.5 text-xs font-medium text-green-700 bg-green-50 rounded-lg hover:bg-green-100 transition-colors focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-1" 
                                            data-id="<?= htmlspecialchars($employee->employee_id ?? '') ?>"
                                            title="Edit Employee">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                            Edit
                                        </button>
                                        <button 
                                            class="delete inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 rounded-lg hover:bg-red-100 transition-colors focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1" 
                                            data-id="<?= htmlspecialchars($employee->employee_id ?? '') ?>"
                                            data-name="<?= htmlspecialchars(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? '')) ?>"
                                            title="Delete Employee">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>

                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-6 flex flex-col sm:flex-row justify-between items-center gap-4 text-sm text-gray-600">
                    <div>
                        Showing <span class="font-medium"><?= $startRecord ?></span> to <span class="font-medium"><?= $endRecord ?></span> of <span class="font-medium"><?= $totalRecords ?></span> records
                        <?php if (!empty($searchQuery)): ?>
                            <span class="text-gray-500">(filtered)</span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($totalPages > 1): ?>
                    <nav class="flex space-x-1" aria-label="Pagination">
                        <?php
                        // Build query string for pagination links
                        $queryString = !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : '';
                        ?>
                        
                        <!-- Previous Button -->
                        <?php if ($currentPage > 1): ?>
                            <a href="?page=<?= $currentPage - 1 ?><?= $queryString ?>" class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-100 transition-colors">
                                Previous
                            </a>
                        <?php else: ?>
                            <span class="px-3 py-2 border border-gray-300 rounded-lg text-gray-400 cursor-not-allowed">Previous</span>
                        <?php endif; ?>
                        
                        <!-- Page Numbers -->
                        <?php
                        $startPage = max(1, $currentPage - 2);
                        $endPage = min($totalPages, $currentPage + 2);
                        
                        // Show first page if not in range
                        if ($startPage > 1): ?>
                            <a href="?page=1<?= $queryString ?>" class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-100 transition-colors">1</a>
                            <?php if ($startPage > 2): ?>
                                <span class="px-3 py-2 text-gray-500">...</span>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <?php if ($i == $currentPage): ?>
                                <span class="px-3 py-2 border border-gray-300 rounded-lg bg-blue-600 text-white font-medium"><?= $i ?></span>
                            <?php else: ?>
                                <a href="?page=<?= $i ?><?= $queryString ?>" class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-100 transition-colors"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <!-- Show last page if not in range -->
                        <?php if ($endPage < $totalPages): ?>
                            <?php if ($endPage < $totalPages - 1): ?>
                                <span class="px-3 py-2 text-gray-500">...</span>
                            <?php endif; ?>
                            <a href="?page=<?= $totalPages ?><?= $queryString ?>" class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-100 transition-colors"><?= $totalPages ?></a>
                        <?php endif; ?>
                        
                        <!-- Next Button -->
                        <?php if ($currentPage < $totalPages): ?>
                            <a href="?page=<?= $currentPage + 1 ?><?= $queryString ?>" class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-100 transition-colors">
                                Next
                            </a>
                        <?php else: ?>
                            <span class="px-3 py-2 border border-gray-300 rounded-lg text-gray-400 cursor-not-allowed">Next</span>
                        <?php endif; ?>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>


            
                <!--Modal : Create Employee-->

                <div id="addEmployeeModal" class="fixed modal inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity"></div>

                    <div class="flex items-center justify-center min-h-screen p-4 sm:p-6">
                        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg transition-all transform sm:my-8">

                            <div class="flex items-start justify-between p-5 border-b border-gray-200">
                                <h3 class="text-xl font-semibold text-gray-800" id="modal-title">
                                    Add New Employee
                                </h3>
                                <button type="button" class="text-gray-400 hover:text-gray-600 focus:outline-none" onclick="document.getElementById('addEmployeeModal').classList.add('hidden')">
                                    <span class="sr-only">Close modal</span>
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>

                            <div class="p-6 space-y-4">
                                <p class="text-sm text-gray-500">Fill out the details below to add a new employee to the directory.</p>
                                
                                <form class="space-y-4">
                                    <div>
                                        <label for="employee_id" class="block text-sm font-medium text-gray-700">Employee Id</label>
                                        <input type="text" name="employee_id" id="employee_id" required 
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="<?= $lastEmployeeId ? 'Last ID: ' . htmlspecialchars($lastEmployeeId) : 'Enter employee ID' ?>">
                                        <?php if ($lastEmployeeId): ?>
                                            <p class="mt-1 text-xs text-gray-500">Last created employee ID: <span class="font-semibold text-gray-700"><?= htmlspecialchars($lastEmployeeId) ?></span></p>
                                        <?php endif; ?>
                                    </div>

                                    <div>
                                        <label for="resident_id" class="block text-sm font-medium text-gray-700">Choose from Residents</label>
                                        <select id="resident_id" name="resident_id" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                                            <option selected disabled>Select Resident</option>
                                            <?php foreach ($residents as $resident): ?>
                                                <option value="<?= $resident->resident_id ?>"><?= $resident->first_name ?> <?= $resident->last_name ?></option>
                                            <?php endforeach ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="department_id" class="block text-sm font-medium text-gray-700">Department</label>
                                        <select id="department_id" name="department_id" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                                            <option selected disabled>Select Department</option>
                                            <?php foreach($departmentLists as $departmentList):?>
                                                <option value="<?=$departmentList->department_id?>"><?=$departmentList->department_name?></option>
                                            <?php endforeach ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="position_id" class="block text-sm font-medium text-gray-700">Position</label>
                                        <select id="position_id" name="position_id" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                                            <option selected disabled>Select Position</option>
                                             <?php foreach($positions as $position):?>
                                                <option value="<?=$position->position_id;?>"><?=$position->position_name;?></option>
                                            <?php endforeach ?>
                                        </select>
                                    </div>

                                    <div>
                                        <label for="hired_date" class="block text-sm font-medium text-gray-700">Hired Date</label>
                                        <input type="date" name="hired_date" id="hired_date" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                </form>
                            </div>

                            <div class="flex flex-col sm:flex-row justify-end p-5 space-y-3 sm:space-y-0 sm:space-x-3 border-t border-gray-200">
                                <button type="button" class="w-full sm:w-auto px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none" onclick="document.getElementById('addEmployeeModal').classList.add('hidden')">
                                    Cancel
                                </button>
                                <button type="submit" id="addEmployeeBtn" class="w-full sm:w-auto px-4 py-2 text-sm font-medium text-white rounded-lg btn-primary shadow-md hover:shadow-lg transition-colors">
                                    Save Employee
                                </button>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- End modal -->

                <!-- Modal : Edit Employee -->

                 <div id="editEmployeeModal" class="fixed modal inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity"></div>

                    <div class="flex items-center justify-center min-h-screen p-4 sm:p-6">
                        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg transition-all transform sm:my-8">

                            <div class="flex items-start justify-between p-5 border-b border-gray-200">
                                <h3 class="text-xl font-semibold text-gray-800" id="modal-title">
                                    Employee Record
                                </h3>
                                <button type="button" class="text-gray-400 hover:text-gray-600 focus:outline-none" onclick="document.getElementById('editEmployeeModal').classList.add('hidden')">
                                    <span class="sr-only">Close modal</span>
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>

                            <div class="p-6 space-y-4">
                                <p class="text-sm text-gray-500">Employee Profile</p>
                                <div class="content">
                                    <div>
                                        <label for="employee_id" class="block text-sm font-medium text-gray-700">Employee Id</label>
                                        <input type="text" name="employee_id" id="edit_modal_employee_id" disabled class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div class="flex gap-2 mt-2">
                                        <div>
                                            <label for="employee_id" class="block text-sm font-medium text-gray-700">First Name</label>
                                            <input type="text" name="employee_id" id="edit_modal_employee_id" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        <div>
                                            <label for="employee_id" class="block text-sm font-medium text-gray-700">Last Name</label>
                                            <input type="text" name="employee_id" id="edit_modal_employee_id" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                    </div>
                                </div>

                                
                                <!-- <form class="space-y-4">
                                    <div>
                                        <label for="employee_id" class="block text-sm font-medium text-gray-700">Employee Id</label>
                                        <input type="text" name="employee_id" id="employee_id" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>

                                    <div>
                                        <label for="resident_id" class="block text-sm font-medium text-gray-700">Choose from Residents</label>
                                        <select id="resident_id" name="resident_id" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                                            <option selected disabled>Select Resident</option>
                                            <?php foreach ($residents as $resident): ?>
                                                <option value="<?= $resident->resident_id ?>"><?= $resident->first_name ?> <?= $resident->last_name ?></option>
                                            <?php endforeach ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="department_id" class="block text-sm font-medium text-gray-700">Department</label>
                                        <select id="department_id" name="department_id" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                                            <option selected disabled>Select Department</option>
                                            <?php foreach($departmentLists as $departmentList):?>
                                                <option value="<?=$departmentList->department_id?>"><?=$departmentList->department_name?></option>
                                            <?php endforeach ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="position_id" class="block text-sm font-medium text-gray-700">Position</label>
                                        <select id="position_id" name="position_id" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                                            <option selected disabled>Select Position</option>
                                             <?php foreach($positions as $position):?>
                                                <option value="<?=$position->position_id;?>"><?=$position->position_name;?></option>
                                            <?php endforeach ?>
                                        </select>
                                    </div>

                                    <div>
                                        <label for="hired_date" class="block text-sm font-medium text-gray-700">Hired Date</label>
                                        <input type="date" name="hired_date" id="hired_date" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div> -->
                                </form>
                            </div>

                            <div class="flex flex-col sm:flex-row justify-end p-5 space-y-3 sm:space-y-0 sm:space-x-3 border-t border-gray-200">
                                <button type="button" class="w-full sm:w-auto px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none" onclick="document.getElementById('editEmployeeModal').classList.add('hidden')">
                                    Cancel
                                </button>
                                <button type="submit" id="addEmployeeBtn" class="w-full sm:w-auto px-4 py-2 text-sm font-medium text-white rounded-lg btn-primary shadow-md hover:shadow-lg transition-colors">
                                    Save Employee
                                </button>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- End modal -->

        </main>
    </div>

    <!-- Pass PHP config values to JavaScript via meta tags -->
    <meta name="employees-api-url" content="<?php echo htmlspecialchars(API_ENDPOINT_EMPLOYEES_STORE); ?>">
    
    <!-- Modular JavaScript Entry Point -->
    <script type="module" 
            data-employees-api-url="<?php echo htmlspecialchars(API_ENDPOINT_EMPLOYEES_STORE); ?>"
            src="./js/employees/main.js"></script>
</body>
</html>
