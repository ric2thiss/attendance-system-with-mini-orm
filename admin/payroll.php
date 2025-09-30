<?php

include_once '../shared/components/Sidebar.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Management</title>
    <!-- Load Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Use Inter font family and custom styles from the dashboard -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f7f9fc; /* Light background for the main content area */
        }
        /* Custom dark blue for the sidebar */
        .sidebar-bg {
            background-color: #172B4D; /* A deep navy blue */
        }
        /* Active link background color */
        .active-link {
            background-color: rgba(255, 255, 255, 0.1);
            border-left: 4px solid #007bff; /* Light blue border highlight */
        }
        /* Custom Modal Styling */
        .modal {
            transition: opacity 0.25s ease-in-out;
        }
    </style>
</head>
<body>

    <!-- MODAL FOR SUCCESS MESSAGE (Custom Alert) -->
    <div id="successModal" class="modal fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center opacity-0 pointer-events-none z-50">
        <div class="bg-white p-6 rounded-xl shadow-2xl max-w-sm w-full transform scale-95 transition-transform duration-300">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Payrun Successful!</h3>
                    <p class="mt-1 text-sm text-gray-500" id="modalMessage">Payroll for the new period has been processed.</p>
                </div>
            </div>
            <div class="mt-4 text-right">
                <button id="closeModal" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-1.5 px-4 rounded-lg text-sm transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>
    <!-- END MODAL -->

    <!-- Main Container -->
    <div class="flex min-h-screen">

        <?=Sidebar("Attendance Logs", null, "./Login_logo1.png")?>

        <!-- Sidebar Toggle for Mobile -->
        <button id="sidebar-toggle" class="md:hidden fixed top-4 left-4 z-20 p-2 bg-blue-600 text-white rounded-full shadow-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>

        <!-- 2. MAIN CONTENT AREA -->
        <main class="flex-1 md:ml-64 p-6 transition-all duration-300">

            <!-- Top Header Bar -->
            <header class="mb-6">
                <div class="flex justify-between items-center mb-1">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-800">Payroll Management</h1>
                        <p class="text-gray-500 text-sm">Manage employee compensation, process payruns, and review history.</p>
                    </div>
                    <div>
                        <button id="processPayrunButton" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg shadow-md transition-colors">
                            <svg class="w-5 h-5 inline mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            Process New Payrun
                        </button>
                    </div>
                </div>
            </header>

            <!-- PAYROLL SUMMARY CARDS -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                
                <!-- Card 1: Total Gross Pay (Last Month) -->
                <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-500">Total Gross Pay</p>
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8a4 4 0 100 8m-9 1h18a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    </div>
                    <p class="text-3xl font-bold text-gray-900 mt-1" id="totalGrossPay">₱ 850,500.00</p>
                    <p class="text-xs text-green-500 mt-1" id="grossPayChange">+1.5% vs. previous month</p>
                </div>

                <!-- Card 2: Total Deductions (Last Month) -->
                <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-500">Total Deductions</p>
                        <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    </div>
                    <p class="text-3xl font-bold text-gray-900 mt-1" id="totalDeductions">₱ 125,100.00</p>
                    <p class="text-xs text-red-500 mt-1" id="deductionsChange">-0.2% vs. previous month</p>
                </div>

                <!-- Card 3: Total Net Pay (Last Month) -->
                <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-500">Total Net Pay</p>
                        <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.108c-.36-.088-.748-.112-1.128-.068C18.667 8.01 17.13 9.493 15 12c-2.13 2.507-3.667 3.99-5.49 5.068-1.558.918-3.085 1.042-4.524.364-1.396-.64-2.583-2.025-3.064-3.568.088-.36.112-.748.068-1.128C5.333 13.99 6.87 12.507 9 10c2.13-2.507 3.667-3.99 5.49-5.068 1.558-.918 3.085-1.042 4.524-.364 1.396.64 2.583 2.025 3.064 3.568z"></path></svg>
                    </div>
                    <p class="text-3xl font-bold text-gray-900 mt-1" id="totalNetPay">₱ 725,400.00</p>
                    <p class="text-xs text-gray-500 mt-1" id="employeesCount">For 15 active employees</p>
                </div>
            </div>


            <!-- RECENT PAYROLL RUNS TABLE -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Recent Payruns</h2>

                <!-- Table Header -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payrun Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period Covered</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employees Paid</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Net Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="payrunsTableBody">
                            
                            <!-- Initial Row 1 -->
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Oct 15, 2024</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Oct 1 - Oct 15</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">15</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">₱ 360,200.00</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Completed</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="#" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                                    <a href="#" class="text-gray-600 hover:text-gray-900">Export</a>
                                </td>
                            </tr>

                            <!-- Initial Row 2 -->
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Sep 30, 2024</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Sep 16 - Sep 30</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">14</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">₱ 348,100.00</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Completed</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="#" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                                    <a href="#" class="text-gray-600 hover:text-gray-900">Export</a>
                                </td>
                            </tr>
                            
                            <!-- Initial Row 3 - Pending (We will remove this when the first new run is processed) -->
                            <tr id="pending-row">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Nov 15, 2024 (Upcoming)</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Nov 1 - Nov 15</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">15</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">N/A</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="#" class="text-blue-600 hover:text-blue-900 mr-3">Prepare</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    <!-- JavaScript for Sidebar Toggle and Payroll Logic -->
    <script>
        // --- Global Variables (Initial State - updated on payrun) ---
        let lastNetTotal = 725400; // Corresponds to the initial P725,400.00
        let lastGrossTotal = 850500;
        let lastDeductionsTotal = 125100;
        let employeeCount = 15;

        // Utility function to format currency
        const formatCurrency = (amount) => {
            return '₱ ' + amount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        };

        // Utility function to get today's date in 'Mon DD, YYYY' format
        const getFormattedDate = () => {
            const date = new Date();
            const options = { month: 'short', day: 'numeric', year: 'numeric' };
            return date.toLocaleDateString('en-US', options);
        };

        // --- Core Payroll Logic ---
        function processNewPayrun() {
            // 1. Generate Fake Data for the new Payrun
            const payrunDate = getFormattedDate();
            
            // Increment the employee count slightly for variation
            employeeCount = Math.min(employeeCount + Math.floor(Math.random() * 2), 20); 

            // Simulate slight growth in totals (Net Pay between +1% and +5%)
            const netIncreaseFactor = 1 + (Math.random() * 0.04 + 0.01); // 1.01 to 1.05
            const newNetTotal = Math.round(lastNetTotal * netIncreaseFactor / 100) * 100; // Round to nearest 100
            
            // Gross and Deductions are derived from new Net Total to maintain consistency
            const newGrossTotal = Math.round(newNetTotal * 1.18 / 100) * 100; // Gross is approx Net * 1.18
            const newDeductionsTotal = newGrossTotal - newNetTotal;

            const newPayrunData = {
                payrunDate: payrunDate,
                periodCovered: `New Period - ${payrunDate}`, // Placeholder period
                employeesPaid: employeeCount,
                netTotal: newNetTotal,
                grossTotal: newGrossTotal,
                deductionsTotal: newDeductionsTotal,
                status: 'Completed',
            };

            // 2. Update Summary Cards
            updateSummaryCards(newPayrunData);

            // 3. Add New Row to Table
            addNewRowToTable(newPayrunData);

            // 4. Update the "last" totals for the next run
            lastNetTotal = newNetTotal;
            lastGrossTotal = newGrossTotal;
            lastDeductionsTotal = newDeductionsTotal;

            // 5. Show Success Modal
            showModal(`Payroll for ${payrunDate} (Net: ${formatCurrency(newNetTotal)}) processed.`);

            // Remove the 'Pending' row if it exists
            const pendingRow = document.getElementById('pending-row');
            if (pendingRow) {
                pendingRow.remove();
            }
        }

        function updateSummaryCards(data) {
            // Update Net Pay Card
            document.getElementById('totalNetPay').textContent = formatCurrency(data.netTotal);
            document.getElementById('employeesCount').textContent = `For ${data.employeesPaid} active employees`;
            
            // Update Gross Pay Card
            document.getElementById('totalGrossPay').textContent = formatCurrency(data.grossTotal);
            const grossChange = (((data.grossTotal - lastGrossTotal) / lastGrossTotal) * 100).toFixed(1);
            const grossChangeElement = document.getElementById('grossPayChange');
            grossChangeElement.textContent = `${grossChange > 0 ? '+' : ''}${grossChange}% vs. previous month`;
            grossChangeElement.classList.toggle('text-green-500', grossChange >= 0);
            grossChangeElement.classList.toggle('text-red-500', grossChange < 0);


            // Update Deductions Card
            document.getElementById('totalDeductions').textContent = formatCurrency(data.deductionsTotal);
            const deductionsChange = (((data.deductionsTotal - lastDeductionsTotal) / lastDeductionsTotal) * 100).toFixed(1);
            const deductionsChangeElement = document.getElementById('deductionsChange');
            deductionsChangeElement.textContent = `${deductionsChange > 0 ? '+' : ''}${deductionsChange}% vs. previous month`;
            // Deductions going up is typically negative for net pay, but here we just show the change
            deductionsChangeElement.classList.toggle('text-red-500', deductionsChange >= 0); 
            deductionsChangeElement.classList.toggle('text-green-500', deductionsChange < 0);
        }

        function addNewRowToTable(data) {
            const tableBody = document.getElementById('payrunsTableBody');

            const newRowHTML = `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${data.payrunDate}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${data.periodCovered}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${data.employeesPaid}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">${formatCurrency(data.netTotal)}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">${data.status}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="#" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                        <a href="#" class="text-gray-600 hover:text-gray-900">Export</a>
                    </td>
                </tr>
            `;

            // Prepend the new row to the table body
            tableBody.insertAdjacentHTML('afterbegin', newRowHTML);
        }
        
        // --- Modal Functionality ---
        const successModal = document.getElementById('successModal');
        const closeModalButton = document.getElementById('closeModal');
        const modalMessage = document.getElementById('modalMessage');

        function showModal(message) {
            modalMessage.textContent = message;
            successModal.classList.remove('opacity-0', 'pointer-events-none');
            successModal.querySelector('div').classList.remove('scale-95');
            successModal.querySelector('div').classList.add('scale-100');
        }

        function hideModal() {
            successModal.classList.add('opacity-0', 'pointer-events-none');
            successModal.querySelector('div').classList.remove('scale-100');
            successModal.querySelector('div').classList.add('scale-95');
        }

        closeModalButton.addEventListener('click', hideModal);

        // --- Event Listeners and Initialization ---
        document.getElementById('processPayrunButton').addEventListener('click', processNewPayrun);


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
