<?php
require_once __DIR__ . "/../bootstrap.php";
require_once __DIR__ . "/../auth/helpers.php";
requireAuth(); // Require authentication - redirects to login if not authenticated

include_once '../shared/components/Sidebar.php';
include_once '../shared/components/Breadcrumb.php';

// Get current user for greeting
$currentUser = currentUser();
$userName = $currentUser ? ($currentUser['full_name'] ?? $currentUser['username']) : 'Guest';

// Get pagination, search, and filter parameters
$currentPage = isset($_GET['page']) ? intval($_GET['page']) : 1;
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$perPage = 10; // Records per page

// Get filters
$filters = [];
if (isset($_GET['status_type']) && !empty($_GET['status_type'])) {
    $filters['status_type'] = $_GET['status_type'];
}
if (isset($_GET['is_active']) && $_GET['is_active'] !== '') {
    $filters['is_active'] = intval($_GET['is_active']);
}

// Get data from controller
$residentController = new ResidentController();
$data = $residentController->getPaginatedResidents($currentPage, $perPage, $searchQuery, $filters);

// Extract data for view
$residents = $data['residents'];
$pagination = $data['pagination'];
$searchQuery = $data['searchQuery'];

// $employeeCurrentActivities = (new EmployeeController())->getEmployeeCurrentActivity();
// $departmentLists = (new DepartmentController())->getDepartmentLists();

// $positions = (new PositionController())->getAllPosition();

// foreach($positions as $position){
//     echo $position->position_name;
// }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident Directory</title>
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
        /* Add border to each table row */
        tbody tr {
            border-bottom: 1px solid #e5e7eb;
        }
        tbody tr:last-child {
            border-bottom: none;
        }
        /* Prevent body horizontal scroll */
        body {
            overflow-x: hidden;
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

        <?=Sidebar("Residents", null)?>

        <!-- 2. MAIN CONTENT AREA -->
        <main class="flex-1 md:ml-64 p-6 transition-all duration-300">

            <!-- Top Header Bar -->
            <header class="mb-6">
                <div class="flex justify-between items-center mb-1">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-800">Resident Directory</h1>
                        <p class="text-gray-500 text-sm"><?= getGreeting($userName) ?> - Manage all current and past resident in one place.</p>
                    </div>
                </div>
                <?php Breadcrumb([
                    ['label' => 'Dashboard', 'link' => 'dashboard.php'],
                    ['label' => 'Residents', 'link' => 'residents.php']
                ]); ?>
            </header>

            <!-- RESIDENT MANAGEMENT SECTION -->
            <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100">
                
                <!-- Controls: Search, Filter, and Add Button -->
                <div class="flex flex-col sm:flex-row justify-between items-center mb-6 space-y-4 sm:space-y-0">
                    
                    <!-- Search Input, Search Button, and Filter -->
                    <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                        <form method="GET" action="" class="relative flex-1 sm:w-96 lg:w-[500px] flex items-center gap-2" id="searchForm">
                            <div class="relative flex-1">
                                <input type="text" 
                                    name="search" 
                                    id="searchInput"
                                    placeholder="Search resident name or ID..." 
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
                            <?php if (isset($_GET['status_type']) && !empty($_GET['status_type'])): ?>
                                <input type="hidden" name="status_type" value="<?= htmlspecialchars($_GET['status_type']) ?>">
                            <?php endif; ?>
                            <?php if (isset($_GET['is_active']) && $_GET['is_active'] !== ''): ?>
                                <input type="hidden" name="is_active" value="<?= htmlspecialchars($_GET['is_active']) ?>">
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
                    <a href="residents/create.php" class="w-full sm:w-auto px-6 py-2 text-white font-semibold rounded-lg btn-primary shadow-md flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Register New Resident
                    </a>
                </div>

                <!-- Resident Table -->
                <div class="table-container rounded-lg border border-gray-200">
                    <div class="inline-block min-w-full align-middle">
                        <table class="min-w-full divide-y divide-gray-200" style="min-width: 1200px;">
                        <thead class="table-header">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Resident ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">First Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Middle Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Last Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Age</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Address</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Life Status</th>

                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider">Action</th>

                            </tr>
                        </thead>

                        <tbody>
                            <?php if (empty($residents)): ?>
                            <tr>
                                <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                                    <p class="text-sm">No residents found.</p>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($residents as $resident): ?>
                                <!-- Main Resident Row -->
                                <tr class="bg-white hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($resident['resident_id'] ?? '') ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($resident['first_name'] ?? '') ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($resident['middle_name'] ?? '') ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($resident['last_name'] ?? '') ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        <?php 
                                            if (!empty($resident['birthdate'])) {
                                                $dob = new DateTime($resident['birthdate']);
                                                $today = new DateTime(date("Y-m-d"));
                                                echo $today->diff($dob)->y;
                                            } else {
                                                echo '-';
                                            }
                                        ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        <?= ucfirst($resident['barangay'] ?? '') ?><?= isset($resident['municipality_city']) ? ', ' . ucfirst($resident['municipality_city']) : '' ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        <?php 
                                        // Display status_type from resident_status table
                                        // status_type indicates what type of resident: 'Senior Citizen', 'PWD', 'Solo Parent', 'Indigent', 'Other'
                                        echo !empty($resident['status_type']) ? htmlspecialchars($resident['status_type']) : '-';
                                        ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        <?php 
                                        // Display is_active from resident_status table
                                        // is_active indicates if resident is alive (1) or deceased (0)
                                        if (isset($resident['is_active'])) {
                                            if ($resident['is_active'] == 1) {
                                                echo '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Alive</span>';
                                            } else {
                                                echo '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Deceased</span>';
                                            }
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center space-x-2">
                                            <a 
                                                href="residents/view.php?id=<?= htmlspecialchars($resident['resident_id'] ?? '') ?>"
                                                class="view inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1" 
                                                title="View Details">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                View
                                            </a>
                                            <a 
                                                href="residents/edit.php?id=<?= htmlspecialchars($resident['resident_id'] ?? '') ?>"
                                                class="edit-link inline-flex items-center px-3 py-1.5 text-xs font-medium text-green-700 bg-green-50 rounded-lg hover:bg-green-100 transition-colors focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-1"
                                                title="Edit Resident"
                                                style="text-decoration: none; cursor: pointer;"
                                                onclick="window.location.href=this.href; return false;">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                                Edit
                                            </a>
                                            <button 
                                                class="delete inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 rounded-lg hover:bg-red-100 transition-colors focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1" 
                                                data-id="<?= htmlspecialchars($resident['resident_id'] ?? '') ?>"
                                                data-name="<?= htmlspecialchars(($resident['first_name'] ?? '') . ' ' . ($resident['last_name'] ?? '')) ?>"
                                                title="Delete Resident">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Relatives Row -->
                                <!-- <?php if (!empty($resident['relatives'])): ?>
                                    <tr class="bg-gray-50">
                                        <td colspan="10" class="px-10 py-3 text-sm text-gray-700">
                                            <strong>Relatives:</strong>
                                            <ul class="list-disc ml-5">
                                                <?php foreach ($resident['relatives'] as $relative): ?>
                                                    <li>
                                                        <?= $relative['first_name'] ?> <?= $relative['last_name'] ?> 
                                                        (<?= ucfirst($relative['relationship_type']) ?>)
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </td>
                                    </tr>
                                <?php endif; ?> -->
                            <?php endforeach; ?>
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
                        $queryParams = [];
                        if (!empty($searchQuery)) {
                            $queryParams[] = 'search=' . urlencode($searchQuery);
                        }
                        if (!empty($filters['status_type'])) {
                            $queryParams[] = 'status_type=' . urlencode($filters['status_type']);
                        }
                        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
                            $queryParams[] = 'is_active=' . $filters['is_active'];
                        }
                        $queryString = !empty($queryParams) ? '&' . implode('&', $queryParams) : '';
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


            
                <!--Modal : Show Resident-->

                <div id="ShowResidentModal" class="fixed modal inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" id="showResidentModalBackdrop" aria-hidden="true"></div>

                    <div class="flex items-center justify-center min-h-screen p-4 sm:p-6">
                        <div class="bg-white rounded-xl shadow-2xl w-full max-w-7xl transition-all transform sm:my-8">

                            <div class="flex items-center justify-between p-5 border-b border-gray-200">
                                <h3 class="text-2xl font-bold text-gray-900" id="modal-title">
                                    <span class="text-blue-600">Information</span>
                                    <span class="text-gray-500 font-medium ml-2 text-base">of <span id="header_resident_name"></span></span>
                                </h3>
                                <button type="button" id="closeShowResidentModal" class="text-gray-400 hover:text-gray-600 focus:outline-none p-1 rounded-full hover:bg-gray-100 transition">
                                    <span class="sr-only">Close modal</span>
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>

                            <div class="p-6">
                                <!-- <p class="text-base text-gray-600 mb-6 border-b pb-4">
                                    Review the existing **Resident Details** on the left, then fill in the specific **Employee Registration** fields on the right.
                                </p> -->

                                <div class="grid grid-cols-1 gap-8">
                                    
                                    <div class="lg:col-span-2 space-y-6 p-4 border rounded-lg bg-gray-50">
                                        <h2 class="text-xl font-semibold text-gray-800 border-b pb-2">✅ Resident Profile Details</h2>
                                        
                                        <div class="flex flex-col sm:flex-row gap-6">
                                            <div class="flex-shrink-0">
                                                <img id="resident_photo" 
                                                    src="../utils/img/logo.png" 
                                                    alt="Resident profile picture" 
                                                    class="w-48 h-48 object-cover rounded-xl shadow-lg border-4 border-white ring-2 ring-blue-500"
                                                    onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'192\' height=\'192\'%3E%3Crect width=\'192\' height=\'192\' fill=\'%23e5e7eb\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' dominant-baseline=\'middle\' text-anchor=\'middle\' fill=\'%239ca3af\' font-size=\'14\'%3ENo Photo%3C/text%3E%3C/svg%3E'"
                                                />
                                                <div class="mt-2 text-center">
                                                    <span id="resident_status" class="inline-block px-3 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800"></span>
                                                </div>
                                            </div>
                                            
                                            <div class="flex-grow grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                                                <div class="col-span-1">
                                                    <h3 class="text-lg font-bold text-gray-900 mb-2">Personal Information</h3>
                                                    <p class="text-sm text-gray-700"><span class="font-medium">Name:</span> <span id="name"></span></p> 
                                                    <p class="text-sm text-gray-700"><span class="font-medium">ID:</span> <span id="id"></span></p>
                                                    <p class="text-sm text-gray-700"><span class="font-medium">PhilSys No.:</span> <span id="philsys_no"></span></p>
                                                    <p class="text-sm text-gray-700"><span class="font-medium">Gender:</span> <span id="gender"></span></p>
                                                    <p class="text-sm text-gray-700"><span class="font-medium">Birth Date:</span> <span id="bod"></span></p>
                                                    <p class="text-sm text-gray-700"><span class="font-medium">Place Of Birth:</span> <span id="pod"></span></p>
                                                    <p class="text-sm text-gray-700"><span class="font-medium">Civil Status:</span> <span id="civil_status"></span></p>
                                                    <p class="text-sm text-gray-700"><span class="font-medium">Blood Type:</span> <span id="blood_type"></span></p>
                                                </div>

                                                <div class="col-span-1 space-y-4">
                                                    <div>
                                                        <h3 class="text-lg font-bold text-gray-900 mb-2">Contact & Occupation</h3>
                                                        <p class="text-sm text-gray-700"><span class="font-medium">Mobile:</span> <span id="contact"></span></p>
                                                        <p class="text-sm text-gray-700"><span class="font-medium">Job Title:</span> <span id="position"></span></p>
                                                        <p class="text-sm text-gray-700"><span class="font-medium">Employer:</span> <span id="employeer_name"></span></p>
                                                        <p class="text-sm text-gray-700"><span class="font-medium">Income Bracket:</span> <span id="income_bracket"></span></p>
                                                    </div>

                                                    <div>
                                                        <h3 class="text-lg font-bold text-gray-900 mb-2">Residence Status</h3>
                                                        <p class="text-sm text-gray-700"><span class="font-medium">Owner of House:</span> <span id="ooh"></span></p>
                                                        <p class="text-sm text-gray-700"><span class="font-medium">Residency:</span> <span id="residency"></span></p>
                                                        <p class="text-sm text-gray-700"><span class="font-medium">Barangay:</span> <span id="barangay"></span></p>
                                                        <p class="text-sm text-gray-700"><span class="font-medium">Postal Code:</span> <span id="postal_code"></span></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-6 border-t pt-4">
                                            <details>
                                                <summary class="font-semibold text-gray-800 cursor-pointer hover:text-blue-600">▶ Show Secondary/Supporting Details</summary>
                                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4 p-3 bg-white rounded-lg shadow-inner">
                                                    <div>
                                                        <h4 class="text-base font-bold text-gray-900">Resident ID's</h4>
                                                        <p class="text-sm text-gray-700"><span id="id_type"></span> <span id="id_number"></span></p>
                                                        <p class="text-sm text-gray-700">Issue Date: <span id="issue_date"></span></p>
                                                        <p class="text-sm text-gray-700">Expiry Date: <span id="expiry_date"></span></p>
                                                    </div>
                                                    <div>
                                                        <h4 class="text-base font-bold text-gray-900">Biometric</h4>
                                                        <p class="text-sm text-gray-700">Type: <span id="resident_biometrics"></span></p>
                                                        <p class="text-sm text-gray-700">Capture Date: 12/02/2013</p>
                                                        <p class="text-sm text-gray-700">Recapture Due: 12/02/2025</p>
                                                    </div>
                                                    <div>
                                                        <h4 class="text-base font-bold text-gray-900">Family</h4>
                                                        <p class="text-sm text-gray-700">Relative/s : <span id="relative_name"></span></p>
                                                        </div>
                                                </div>
                                            </details>
                                        </div>
                                    </div>

                                    <!-- <div class="lg:col-span-1 space-y-6 p-4 border-2 border-blue-200 rounded-lg bg-white shadow-lg">
                                        <h2 class="text-xl font-semibold text-blue-700 border-b pb-2">📝 New Employee Fields</h2>
                                        
                                        <form class="space-y-6">
                                            <div>
                                                <label for="employee_id" class="block text-sm font-semibold text-gray-700">Employee ID <span class="text-red-500">*</span></label>
                                                <input type="text" name="employee_id" id="employee_id" placeholder="Enter unique employee number" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-3 text-lg focus:ring-blue-500 focus:border-blue-500 transition">
                                            </div>

                                            <div>
                                                <label for="hired_date" class="block text-sm font-semibold text-gray-700">Date Hired <span class="text-red-500">*</span></label>
                                                <input type="date" name="hired_date" id="hired_date" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-3 text-lg focus:ring-blue-500 focus:border-blue-500 transition">
                                            </div>

                                            <div>
                                                <label for="department" class="block text-sm font-semibold text-gray-700">Department / Office <span class="text-red-500">*</span></label>
                                                <select id="department" name="department" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-3 text-lg focus:ring-blue-500 focus:border-blue-500 transition">
                                                    <option value="">-- Select Department --</option>
                                                    <option value="admin">Administration</option>
                                                    <option value="hr">Human Resources</option>
                                                    <option value="finance">Finance</option>
                                                    </select>
                                            </div>
                                        </form>
                                    </div> -->
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row justify-end p-5 space-y-3 sm:space-y-0 sm:space-x-4 border-t border-gray-200 bg-gray-50 rounded-b-xl">
                                <button type="button" id="cancelShowResidentModal" class="w-full sm:w-auto px-6 py-3 text-base font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl shadow-sm hover:bg-gray-100 transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Cancel
                                </button>
                                <button type="submit" form="employee-registration-form" id="addEmployeeBtn" class="w-full sm:w-auto px-6 py-3 text-base font-semibold text-white bg-blue-600 rounded-xl shadow-lg hover:bg-blue-700 transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <svg class="w-5 h-5 inline-block mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"></path></svg>
                                    Register Employee
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
                                <button type="button" id="closeEditEmployeeModal" class="text-gray-400 hover:text-gray-600 focus:outline-none">
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
                                
                                
                                
                            </div>

                            <div class="flex flex-col sm:flex-row justify-end p-5 space-y-3 sm:space-y-0 sm:space-x-3 border-t border-gray-200">
                                <button type="button" id="cancelEditEmployeeModal" class="w-full sm:w-auto px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none">
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

                <!-- Filter Modal -->
                <div id="filterModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="filter-modal-title" role="dialog" aria-modal="true">
                    <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" id="filterModalBackdrop" aria-hidden="true"></div>

                    <div class="flex items-center justify-center min-h-screen p-4 sm:p-6">
                        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md transition-all transform sm:my-8" id="filterModalContent">
                            <form method="GET" action="">
                                <div class="flex items-center justify-between p-5 border-b border-gray-200">
                                    <h3 class="text-xl font-semibold text-gray-900" id="filter-modal-title">
                                        Filter Residents
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

                                    <!-- Status Type Filter -->
                                    <div>
                                        <label for="filter_status_type" class="block text-sm font-medium text-gray-700 mb-2">
                                            Status Type
                                        </label>
                                        <select name="status_type" id="filter_status_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">All Status Types</option>
                                            <option value="Senior Citizen" <?= (isset($filters['status_type']) && $filters['status_type'] === 'Senior Citizen') ? 'selected' : '' ?>>Senior Citizen</option>
                                            <option value="PWD" <?= (isset($filters['status_type']) && $filters['status_type'] === 'PWD') ? 'selected' : '' ?>>PWD</option>
                                            <option value="Solo Parent" <?= (isset($filters['status_type']) && $filters['status_type'] === 'Solo Parent') ? 'selected' : '' ?>>Solo Parent</option>
                                            <option value="Indigent" <?= (isset($filters['status_type']) && $filters['status_type'] === 'Indigent') ? 'selected' : '' ?>>Indigent</option>
                                            <option value="Other" <?= (isset($filters['status_type']) && $filters['status_type'] === 'Other') ? 'selected' : '' ?>>Other</option>
                                        </select>
                                    </div>

                                    <!-- Life Status Filter -->
                                    <div>
                                        <label for="filter_is_active" class="block text-sm font-medium text-gray-700 mb-2">
                                            Life Status
                                        </label>
                                        <select name="is_active" id="filter_is_active" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">All</option>
                                            <option value="1" <?= (isset($filters['is_active']) && $filters['is_active'] == 1) ? 'selected' : '' ?>>Alive</option>
                                            <option value="0" <?= (isset($filters['is_active']) && $filters['is_active'] == 0) ? 'selected' : '' ?>>Deceased</option>
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
    <script type="module" src="./js/residents/main.js"></script>
</body>
</html>
