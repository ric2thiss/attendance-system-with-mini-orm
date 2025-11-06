<?php
require_once __DIR__ . "/../bootstrap.php";
include_once '../shared/components/Sidebar.php';

// For employee table
$employeesData = (new EmployeeController())->getAllEmployees();

$residents = (new ResidentController())->getAllResident();
// $employeeCurrentActivities = (new EmployeeController())->getEmployeeCurrentActivity();
$departmentLists = (new DepartmentController())->getDepartmentLists();

$positions = (new PositionController())->getAllPosition();

// foreach($positions as $position){
//     echo $position->position_name;
// }

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
                    <button class="w-full sm:w-auto px-6 py-2 text-white font-semibold rounded-lg btn-primary shadow-md flex items-center justify-center" id="openAddEmployeeModal">
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
                                    <?= $employee->position_name ?>
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


            
                <!--Modal Here-->

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
                                        <input type="text" name="hired_date" id="hired_date" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
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


        </main>
    </div>

    <!-- JavaScript for Sidebar Toggle -->
    <script>
        const addEmployeeBtn = document.getElementById("addEmployeeBtn");

        const URL = "http://localhost/attendance-system/api/v1/request.php?query=employees";

        const handleAddEmployee = async () => {
            const employee_id = document.getElementById("employee_id").value;
            const resident_id = document.getElementById("resident_id").value;
            const department_id = document.getElementById("department_id").value;
            const position_id = document.getElementById("position_id").value;
            const hired_date = document.getElementById("hired_date").value;

            if (!resident_id || !department_id || !position_id || !hired_date) {
                alert("Please fill out all required fields.");
                return;
            }

            const formData = new FormData();
            formData.append("employee_id", employee_id);
            formData.append("resident_id", resident_id);
            formData.append("department_id", department_id);
            formData.append("position_id", position_id);
            formData.append("hired_date", hired_date);

            try {
                const response = await fetch(URL, {
                    method: "POST",
                    headers: {
                        "x-api-key": "HELLOWORLD"
                        // ⚠️ Do NOT include "Content-Type" when sending FormData
                        // The browser will set it automatically with proper boundary
                    },
                    body: formData
                });

                const data = await response.json();

                if (response.ok) {
                    alert(data.message || "Employee added successfully!");
                    document.getElementById("employeeForm")?.reset();
                } else {
                    alert(data.error || "Failed to add employee.");
                }
            } catch (error) {
                console.error("Error:", error);
                alert("Something went wrong. Please try again later.");
            }
        };

        addEmployeeBtn.addEventListener("click", handleAddEmployee);



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

    <script>
    // ... (Your existing sidebar toggle script) ...

    // --- Modal Toggle Logic ---
    const openModalButton = document.getElementById('openAddEmployeeModal');
    const modal = document.getElementById('addEmployeeModal');

    // Function to open the modal
    if (openModalButton) {
        openModalButton.addEventListener('click', () => {
            modal.classList.remove('hidden');
            // Optional: Focus the first input for accessibility
            document.getElementById('firstName').focus();
        });
    }

    // Function to close the modal (already handled by close buttons, but good for backdrop)
    const closeModal = () => {
        modal.classList.add('hidden');
    }

    // Close when clicking the backdrop (outside the modal content)
    modal.addEventListener('click', (e) => {
        if (e.target.id === 'addEmployeeModal') {
            closeModal();
        }
    });

    // Close when pressing the ESC key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });

</script>
</body>
</html>
