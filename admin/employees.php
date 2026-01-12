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

// Get filters
$filters = [];
if (isset($_GET['department_id']) && !empty($_GET['department_id'])) {
    $filters['department_id'] = intval($_GET['department_id']);
}
if (isset($_GET['position_id']) && !empty($_GET['position_id'])) {
    $filters['position_id'] = intval($_GET['position_id']);
}

// Fetch data from controller
$employeeController = new EmployeeController();
$data = $employeeController->getPaginatedEmployees($currentPage, $perPage, $searchQuery, $filters);

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
                    
                    <!-- Search Input, Search Button, and Filter -->
                    <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                        <form method="GET" action="" class="relative flex-1 sm:w-96 lg:w-[500px] flex items-center gap-2" id="searchForm">
                            <div class="relative flex-1">
                                <input type="text" 
                                    name="search" 
                                    id="searchInput"
                                    placeholder="Search employee name, ID, or position..." 
                                    value="<?= htmlspecialchars($searchQuery) ?>"
                                    class="w-full py-2 pl-10 pr-10 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                <?php if (!empty($searchQuery)): ?>
                                <a href="?" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </a>
                                <?php endif; ?>
                            </div>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors whitespace-nowrap">
                                Search
                            </button>
                            <!-- Preserve filter parameters -->
                            <?php if (isset($_GET['department_id']) && !empty($_GET['department_id'])): ?>
                                <input type="hidden" name="department_id" value="<?= htmlspecialchars($_GET['department_id']) ?>">
                            <?php endif; ?>
                            <?php if (isset($_GET['position_id']) && !empty($_GET['position_id'])): ?>
                                <input type="hidden" name="position_id" value="<?= htmlspecialchars($_GET['position_id']) ?>">
                            <?php endif; ?>
                        </form>
                        <!-- Filter Button -->
                        <button type="button" id="filterButton" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors flex items-center gap-2 whitespace-nowrap">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                            </svg>
                            Filters
                        </button>
                    </div>

                    <!-- Add Employee Button -->
                    <a href="employees/create.php" class="w-full sm:w-auto px-6 py-2 text-white font-semibold rounded-lg btn-primary shadow-md flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Add New Employee
                    </a>
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
                                    <?= htmlspecialchars($employee->department_name ?? 'N/A') ?>
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
                                        <a href="employees/edit.php?id=<?= htmlspecialchars($employee->employee_id ?? '') ?>"
                                            class="editBtn inline-flex items-center px-3 py-1.5 text-xs font-medium text-green-700 bg-green-50 rounded-lg hover:bg-green-100 transition-colors focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-1" 
                                            title="Edit Employee">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                            Edit
                                        </a>
                                        <button 
                                            class="deleteBtn inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 rounded-lg hover:bg-red-100 transition-colors focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1" 
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
                        <?php if (!empty($searchQuery) || !empty($filters)): ?>
                            <span class="text-gray-500">(filtered)</span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($totalPages > 1): ?>
                    <nav class="flex space-x-1" aria-label="Pagination">
                        <?php
                        // Build query string for pagination links
                        $queryParams = [];
                        if (!empty($searchQuery)) {
                            $queryParams[] = 'search=' . urlencode($searchQuery);
                        }
                        if (!empty($filters['department_id'])) {
                            $queryParams[] = 'department_id=' . $filters['department_id'];
                        }
                        if (!empty($filters['position_id'])) {
                            $queryParams[] = 'position_id=' . $filters['position_id'];
                        }
                        $queryString = !empty($queryParams) ? '&' . implode('&', $queryParams) : '';
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


                <!-- Delete Confirmation Modal -->
                <div id="deleteEmployeeModal" class="fixed modal inset-0 z-50 hidden overflow-y-auto" aria-labelledby="delete-modal-title" role="dialog" aria-modal="true">
                    <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity"></div>

                    <div class="flex items-center justify-center min-h-screen p-4 sm:p-6">
                        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md transition-all transform sm:my-8">
                            <div class="p-6">
                                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4">
                                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-800 text-center mb-2" id="delete-modal-title">
                                    Delete Employee
                                </h3>
                                <p class="text-sm text-gray-600 text-center mb-6">
                                    Are you sure you want to delete <span class="font-semibold text-gray-900" id="delete-employee-name"></span>?<br>
                                    This action cannot be undone.
                                </p>
                                <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3">
                                    <button type="button" 
                                        id="cancelDeleteBtn"
                                        class="w-full sm:w-auto px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none">
                                        Cancel
                                    </button>
                                    <button type="button" 
                                        id="confirmDeleteBtn"
                                        class="w-full sm:w-auto px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 focus:outline-none transition-colors">
                                        Delete Employee
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Delete Modal -->

                <!-- Filter Modal -->
                <div id="filterModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="filter-modal-title" role="dialog" aria-modal="true">
                    <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" id="filterModalBackdrop" aria-hidden="true"></div>

                    <div class="flex items-center justify-center min-h-screen p-4 sm:p-6">
                        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md transition-all transform sm:my-8" id="filterModalContent">
                            <form method="GET" action="">
                                <div class="flex items-center justify-between p-5 border-b border-gray-200">
                                    <h3 class="text-xl font-semibold text-gray-900" id="filter-modal-title">
                                        Filter Employees
                                    </h3>
                                    <button type="button" id="closeFilterModal" class="text-gray-400 hover:text-gray-600 focus:outline-none p-1 rounded-full hover:bg-gray-100 transition">
                                        <span class="sr-only">Close modal</span>
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </div>

                                <div class="p-6 space-y-4">
                                    <!-- Preserve search query -->
                                    <?php if (!empty($searchQuery)): ?>
                                        <input type="hidden" name="search" value="<?= htmlspecialchars($searchQuery) ?>">
                                    <?php endif; ?>

                                    <!-- Department Filter -->
                                    <div>
                                        <label for="filter_department_id" class="block text-sm font-medium text-gray-700 mb-2">
                                            Department
                                        </label>
                                        <select name="department_id" id="filter_department_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">All Departments</option>
                                            <?php foreach($departmentLists as $departmentList):?>
                                                <option value="<?=$departmentList->department_id?>" <?= (isset($filters['department_id']) && $filters['department_id'] == $departmentList->department_id) ? 'selected' : '' ?>><?=$departmentList->department_name?></option>
                                            <?php endforeach ?>
                                        </select>
                                    </div>

                                    <!-- Position Filter -->
                                    <div>
                                        <label for="filter_position_id" class="block text-sm font-medium text-gray-700 mb-2">
                                            Position
                                        </label>
                                        <select name="position_id" id="filter_position_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">All Positions</option>
                                            <?php foreach($positions as $position):?>
                                                <option value="<?=$position->position_id?>" <?= (isset($filters['position_id']) && $filters['position_id'] == $position->position_id) ? 'selected' : '' ?>><?=$position->position_name?></option>
                                            <?php endforeach ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="flex flex-col sm:flex-row justify-end p-5 space-y-3 sm:space-y-0 sm:space-x-3 border-t border-gray-200 bg-gray-50 rounded-b-xl">
                                    <a href="?" class="w-full sm:w-auto px-6 py-2 text-center text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 transition">
                                        Clear Filters
                                    </a>
                                    <button type="submit" class="w-full sm:w-auto px-6 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                                        Apply Filters
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- End Filter Modal -->

        </main>
    </div>

    <!-- Modular JavaScript Entry Point -->
    <script type="module" src="./js/employees/main.js"></script>
    <script>
        // Delete functionality
        let employeeToDelete = null;
        const deleteModal = document.getElementById('deleteEmployeeModal');
        const deleteEmployeeName = document.getElementById('delete-employee-name');
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');

        // Handle delete button clicks
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('deleteBtn') || e.target.closest('.deleteBtn')) {
                const btn = e.target.classList.contains('deleteBtn') ? e.target : e.target.closest('.deleteBtn');
                const employeeId = btn.dataset.id;
                const employeeName = btn.dataset.name || 'this employee';
                
                employeeToDelete = employeeId;
                deleteEmployeeName.textContent = employeeName;
                deleteModal.classList.remove('hidden');
            }
        });

        // Cancel delete
        cancelDeleteBtn.addEventListener('click', () => {
            deleteModal.classList.add('hidden');
            employeeToDelete = null;
        });

        // Close modal on backdrop click
        deleteModal.addEventListener('click', (e) => {
            if (e.target.id === 'deleteEmployeeModal') {
                deleteModal.classList.add('hidden');
                employeeToDelete = null;
            }
        });

        // Confirm delete
        confirmDeleteBtn.addEventListener('click', async () => {
            if (!employeeToDelete) return;

            const btn = confirmDeleteBtn;
            const originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Deleting...';

            try {
                const response = await fetch(`../api/employees/delete.php?id=${encodeURIComponent(employeeToDelete)}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });

                const result = await response.json();

                if (result.success) {
                    // Show success message
                    alert(result.message || 'Employee deleted successfully');
                    // Reload page
                    window.location.reload();
                } else {
                    alert(result.error || 'Failed to delete employee. Please try again.');
                    btn.disabled = false;
                    btn.textContent = originalText;
                }
            } catch (error) {
                console.error('Delete error:', error);
                alert('An error occurred while deleting the employee. Please try again.');
                btn.disabled = false;
                btn.textContent = originalText;
            }
        });

        // Close on ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !deleteModal.classList.contains('hidden')) {
                deleteModal.classList.add('hidden');
                employeeToDelete = null;
            }
        });

        // Filter Modal functionality
        const filterModal = document.getElementById('filterModal');
        const filterButton = document.getElementById('filterButton');
        const closeFilterModal = document.getElementById('closeFilterModal');
        const filterModalBackdrop = document.getElementById('filterModalBackdrop');

        // Open filter modal
        if (filterButton) {
            filterButton.addEventListener('click', () => {
                filterModal.classList.remove('hidden');
            });
        }

        // Close filter modal
        if (closeFilterModal) {
            closeFilterModal.addEventListener('click', () => {
                filterModal.classList.add('hidden');
            });
        }

        // Close on backdrop click
        if (filterModalBackdrop) {
            filterModalBackdrop.addEventListener('click', () => {
                filterModal.classList.add('hidden');
            });
        }

        // Close on ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !filterModal.classList.contains('hidden')) {
                filterModal.classList.add('hidden');
            }
        });
    </script>
</body>
</html>
