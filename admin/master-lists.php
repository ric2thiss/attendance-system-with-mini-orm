<?php
require_once __DIR__ . "/../bootstrap.php";
require_once __DIR__ . "/../auth/helpers.php";
requireAuth();

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
    <title>Master Lists</title>
    <link rel="stylesheet" href="../utils/styles/global.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .btn-primary {
            background-color: #007bff;
            transition: background-color 0.2s;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .table-header {
            background-color: #e5e7eb;
        }
        tbody tr {
            border-bottom: 1px solid #e5e7eb;
        }
        tbody tr:last-child {
            border-bottom: none;
        }
        .tab-button {
            border-bottom-width: 2px;
        }
        .tab-button.active {
            border-bottom-color: #3b82f6;
            color: #2563eb;
        }
        .tab-button:not(.active) {
            border-bottom-color: transparent;
            color: #6b7280;
        }
        .tab-button:not(.active):hover {
            color: #374151;
            border-bottom-color: #d1d5db;
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
        /* Prevent body horizontal scroll */
        body {
            overflow-x: hidden;
        }
        /* Ensure main content doesn't overflow */
        main {
            overflow-x: hidden;
            max-width: 100%;
        }
    </style>
</head>
<body>
    <div class="flex min-h-screen">
        <?=Sidebar("Master Lists", null, "../utils/img/logo.png")?>

        <main class="flex-1 md:ml-64 p-6 transition-all duration-300">
            <header class="mb-6">
                <div class="flex justify-between items-center mb-1">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-800">Master Lists</h1>
                        <p class="text-gray-500 text-sm"><?= getGreeting($userName) ?> - Manage attendance windows.</p>
                    </div>
                </div>
                <?php Breadcrumb([
                    ['label' => 'Dashboard', 'link' => 'dashboard.php'],
                    ['label' => 'Master Lists', 'link' => 'master-lists.php']
                ]); ?>
            </header>

            <!-- Success/Error Messages -->
            <div id="messageContainer" class="mb-4 hidden">
                <div id="successMessage" class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-4 hidden">
                    <p class="font-medium" id="successText"></p>
                </div>
                <div id="errorMessage" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4 hidden">
                    <p class="font-medium" id="errorText"></p>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100">
                <!-- Tab Navigation -->
                <div class="border-b border-gray-200 mb-6">
                    <nav class="flex space-x-8" aria-label="Tabs">
                        <button type="button" class="tab-button active whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors" data-tab="attendance-windows" id="tab-attendance-windows">
                            <span class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Attendance Windows
                            </span>
                        </button>
                    </nav>
                </div>

                <!-- Tab Content: Attendance Windows -->
                <div class="tab-content" id="content-attendance-windows">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-semibold text-gray-800">Attendance Windows</h2>
                        <button onclick="openModal('attendance-windows', null)" class="px-6 py-2 text-white font-semibold rounded-lg btn-primary shadow-md flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Add Window
                        </button>
                    </div>
                    <div class="table-container rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="table-header">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Label</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Start Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">End Time</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="attendance-windows-table" class="bg-white divide-y divide-gray-200">
                                <tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal -->
    <div id="modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900" id="modal-title">Add Item</h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <form id="modal-form" onsubmit="handleSubmit(event)">
                    <input type="hidden" id="modal-id" name="id">
                    <input type="hidden" id="modal-type" name="type">
                    <div id="modal-form-content">
                        <!-- Default form content -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2" id="modal-label">Name</label>
                            <input type="text" id="modal-input" name="name" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Enter name">
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Confirm Delete</h3>
                <p class="text-gray-600 mb-4" id="delete-message">Are you sure you want to delete this item?</p>
                <div class="flex justify-end space-x-3">
                    <button onclick="closeDeleteModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50">
                        Cancel
                    </button>
                    <button onclick="confirmDelete()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const API_BASE = '../api/master-lists/';
        let currentTab = 'attendance-windows';
        let deleteItem = { type: null, id: null };

        const tabConfig = {
            'attendance-windows': {
                endpoint: 'attendance-windows.php',
                nameField: 'label',
                label: 'Window Label',
                title: 'Attendance Window',
                isSimple: false
            }
        };

        // Tab Navigation
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', () => {
                const tab = button.getAttribute('data-tab');
                showTab(tab);
            });
        });

        function showTab(tab) {
            currentTab = tab;
            document.querySelectorAll('.tab-content').forEach(content => content.classList.add('hidden'));
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('active', 'border-blue-500', 'text-blue-600');
                btn.classList.add('border-transparent', 'text-gray-500');
            });

            document.getElementById(`content-${tab}`).classList.remove('hidden');
            document.getElementById(`tab-${tab}`).classList.add('active', 'border-blue-500', 'text-blue-600');
            document.getElementById(`tab-${tab}`).classList.remove('border-transparent', 'text-gray-500');

            loadData(tab);
        }

        // Load data
        async function loadData(type) {
            const config = tabConfig[type];
            const tableBody = document.getElementById(`${type}-table`);
            
            try {
                const response = await fetch(`${API_BASE}${config.endpoint}`);
                const result = await response.json();
                
                if (result.success && result.data) {
                    const items = Array.isArray(result.data) ? result.data : [result.data];
                    
                    if (items.length === 0) {
                        const colspan = type === 'attendance-windows' ? '5' : '3';
                        tableBody.innerHTML = `<tr><td colspan="${colspan}" class="px-6 py-4 text-center text-gray-500">No items found. Click "Add" to create one.</td></tr>`;
                        return;
                    }

                    if (type === 'attendance-windows') {
                        // Special handling for attendance windows table
                        tableBody.innerHTML = items.map(item => {
                            const id = isObject(item) ? item.window_id : item.id;
                            const label = isObject(item) ? item.label : item.label;
                            const startTime = isObject(item) ? item.start_time : item.start_time;
                            const endTime = isObject(item) ? item.end_time : item.end_time;
                            return `
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${id}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">${escapeHtml(label)}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${escapeHtml(startTime)}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${escapeHtml(endTime)}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex items-center justify-center space-x-2">
                                            <button onclick="openModal('${type}', ${id})" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-green-700 bg-green-50 rounded-lg hover:bg-green-100 transition-colors focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-1" title="Edit">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                                Edit
                                            </button>
                                            <button onclick="openDeleteModal('${type}', ${id}, '${escapeHtml(label)}')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 rounded-lg hover:bg-red-100 transition-colors focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1" title="Delete">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        }).join('');
                    }
                } else {
                    tableBody.innerHTML = `<tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">Error loading data.</td></tr>`;
                }
            } catch (error) {
                console.error('Error loading data:', error);
                tableBody.innerHTML = `<tr><td colspan="5" class="px-6 py-4 text-center text-red-500">Error loading data.</td></tr>`;
            }
        }

        function isObject(item) {
            return typeof item === 'object' && item !== null && !Array.isArray(item);
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Modal functions
        function openModal(type, id) {
            const config = tabConfig[type];
            const modal = document.getElementById('modal');
            const title = document.getElementById('modal-title');
            const typeInput = document.getElementById('modal-type');
            const idInput = document.getElementById('modal-id');
            const formContent = document.getElementById('modal-form-content');

            typeInput.value = type;
            idInput.value = id || '';
            title.textContent = id ? `Edit ${config.title}` : `Add ${config.title}`;

            if (type === 'attendance-windows') {
                // Custom form for attendance windows
                formContent.innerHTML = `
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Window Label</label>
                        <input type="text" id="modal-label-input" name="label" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                            placeholder="e.g., morning_in">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Start Time</label>
                        <input type="time" id="modal-start-time" name="start_time" required step="1"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">End Time</label>
                        <input type="time" id="modal-end-time" name="end_time" required step="1"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>
                `;

                if (id) {
                    // Load existing data
                    fetch(`${API_BASE}${config.endpoint}?id=${id}`)
                        .then(res => res.json())
                        .then(result => {
                            if (result.success && result.data) {
                                const item = result.data;
                                const startTime = isObject(item) ? item.start_time : item.start_time;
                                const endTime = isObject(item) ? item.end_time : item.end_time;
                                
                                // Convert HH:MM:SS to HH:MM for time input (HTML5 time inputs only accept HH:MM)
                                const formatTimeForInput = (time) => {
                                    if (!time) return '';
                                    return time.substring(0, 5); // Take only HH:MM
                                };
                                
                                document.getElementById('modal-label-input').value = isObject(item) ? item.label : item.label;
                                document.getElementById('modal-start-time').value = formatTimeForInput(startTime);
                                document.getElementById('modal-end-time').value = formatTimeForInput(endTime);
                            }
                        });
                } else {
                    // Clear inputs for new window
                    document.getElementById('modal-label-input').value = '';
                    document.getElementById('modal-start-time').value = '';
                    document.getElementById('modal-end-time').value = '';
                }
            } else {
                // Default form for simple entities
                formContent.innerHTML = `
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2" id="modal-label">${config.label}</label>
                        <input type="text" id="modal-input" name="name" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Enter ${config.label.toLowerCase()}">
                    </div>
                `;

                const input = document.getElementById('modal-input');
                if (id) {
                    // Load existing data
                    fetch(`${API_BASE}${config.endpoint}?id=${id}`)
                        .then(res => res.json())
                        .then(result => {
                            if (result.success && result.data) {
                                const item = result.data;
                                input.value = isObject(item) ? item[config.nameField] : item.name;
                            }
                        });
                } else {
                    input.value = '';
                }
            }

            modal.classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('modal').classList.add('hidden');
        }

        async function handleSubmit(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            const type = formData.get('type');
            const id = formData.get('id');
            const config = tabConfig[type];
            
            let data;
            if (type === 'attendance-windows') {
                // Ensure time format is HH:MM:SS (HTML5 time inputs return HH:MM)
                let startTime = formData.get('start_time');
                let endTime = formData.get('end_time');
                if (startTime && !startTime.includes(':')) {
                    startTime = startTime + ':00';
                } else if (startTime && startTime.split(':').length === 2) {
                    startTime = startTime + ':00';
                }
                if (endTime && !endTime.includes(':')) {
                    endTime = endTime + ':00';
                } else if (endTime && endTime.split(':').length === 2) {
                    endTime = endTime + ':00';
                }
                data = {
                    label: formData.get('label'),
                    start_time: startTime,
                    end_time: endTime
                };
            } else {
                data = { [config.nameField]: formData.get('name') };
            }

            const url = `${API_BASE}${config.endpoint}`;
            const method = id ? 'PUT' : 'POST';
            const body = id ? JSON.stringify({ id, ...data }) : JSON.stringify(data);

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: { 'Content-Type': 'application/json' },
                    body: body
                });

                const result = await response.json();
                if (result.success) {
                    showMessage(result.message, true);
                    closeModal();
                    loadData(type);
                } else {
                    showMessage(result.message, false);
                }
            } catch (error) {
                console.error('Error:', error);
                showMessage('An error occurred. Please try again.', false);
            }
        }

        // Delete modal functions
        function openDeleteModal(type, id, name) {
            deleteItem = { type, id };
            const config = tabConfig[type];
            document.getElementById('delete-message').textContent = `Are you sure you want to delete "${name}"?`;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
            deleteItem = { type: null, id: null };
        }

        async function confirmDelete() {
            if (!deleteItem.type || !deleteItem.id) return;

            const config = tabConfig[deleteItem.type];
            const url = `${API_BASE}${config.endpoint}`;

            try {
                const response = await fetch(url, {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: deleteItem.id })
                });

                const result = await response.json();
                if (result.success) {
                    showMessage(result.message, true);
                    closeDeleteModal();
                    loadData(deleteItem.type);
                } else {
                    showMessage(result.message, false);
                    closeDeleteModal();
                }
            } catch (error) {
                console.error('Error:', error);
                showMessage('An error occurred. Please try again.', false);
                closeDeleteModal();
            }
        }

        function showMessage(message, isSuccess) {
            const container = document.getElementById('messageContainer');
            const successDiv = document.getElementById('successMessage');
            const errorDiv = document.getElementById('errorMessage');
            const successText = document.getElementById('successText');
            const errorText = document.getElementById('errorText');

            container.classList.remove('hidden');
            if (isSuccess) {
                successText.textContent = message;
                successDiv.classList.remove('hidden');
                errorDiv.classList.add('hidden');
            } else {
                errorText.textContent = message;
                errorDiv.classList.remove('hidden');
                successDiv.classList.add('hidden');
            }

            setTimeout(() => {
                container.classList.add('hidden');
            }, 5000);
        }

        // Sidebar toggle (from existing pages)
        const sidebar = document.getElementById('sidebar');
        const toggleButton = document.getElementById('sidebar-toggle');
        const mainContent = document.querySelector('main');

        if (toggleButton) {
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
        }

        // Load initial data
        loadData('attendance-windows');
    </script>
    
    <!-- App Name Updater -->
    <script src="js/shared/appName.js"></script>
</body>
</html>
