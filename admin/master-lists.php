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
                        <p class="text-gray-500 text-sm"><?= getGreeting($userName) ?> - Manage civil status, departments, and positions.</p>
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
                        <button type="button" class="tab-button active whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors" data-tab="civil-status" id="tab-civil-status">
                            <span class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Civil Status
                            </span>
                        </button>
                        <button type="button" class="tab-button whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors" data-tab="departments" id="tab-departments">
                            <span class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                Departments
                            </span>
                        </button>
                        <button type="button" class="tab-button whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors" data-tab="positions" id="tab-positions">
                            <span class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                Positions
                            </span>
                        </button>
                    </nav>
                </div>

                <!-- Tab Content: Civil Status -->
                <div class="tab-content" id="content-civil-status">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-semibold text-gray-800">Civil Status</h2>
                        <button onclick="openModal('civil-status', null)" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors shadow-md flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add Civil Status
                        </button>
                    </div>
                    <div class="table-container rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="table-header">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Status Name</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="civil-status-table" class="bg-white divide-y divide-gray-200">
                                <tr><td colspan="3" class="px-6 py-4 text-center text-gray-500">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab Content: Departments -->
                <div class="tab-content hidden" id="content-departments">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-semibold text-gray-800">Departments</h2>
                        <button onclick="openModal('departments', null)" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors shadow-md flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add Department
                        </button>
                    </div>
                    <div class="table-container rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="table-header">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Department Name</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="departments-table" class="bg-white divide-y divide-gray-200">
                                <tr><td colspan="3" class="px-6 py-4 text-center text-gray-500">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab Content: Positions -->
                <div class="tab-content hidden" id="content-positions">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-semibold text-gray-800">Positions</h2>
                        <button onclick="openModal('positions', null)" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors shadow-md flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add Position
                        </button>
                    </div>
                    <div class="table-container rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="table-header">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Position Name</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="positions-table" class="bg-white divide-y divide-gray-200">
                                <tr><td colspan="3" class="px-6 py-4 text-center text-gray-500">Loading...</td></tr>
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
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2" id="modal-label">Name</label>
                        <input type="text" id="modal-input" name="name" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Enter name">
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
        let currentTab = 'civil-status';
        let deleteItem = { type: null, id: null };

        const tabConfig = {
            'civil-status': {
                endpoint: 'civil-status.php',
                nameField: 'status_name',
                label: 'Status Name',
                title: 'Civil Status'
            },
            'departments': {
                endpoint: 'departments.php',
                nameField: 'department_name',
                label: 'Department Name',
                title: 'Department'
            },
            'positions': {
                endpoint: 'positions.php',
                nameField: 'position_name',
                label: 'Position Name',
                title: 'Position'
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
                        tableBody.innerHTML = '<tr><td colspan="3" class="px-6 py-4 text-center text-gray-500">No items found. Click "Add" to create one.</td></tr>';
                        return;
                    }

                    tableBody.innerHTML = items.map(item => {
                        const id = isObject(item) ? item[`${type === 'civil-status' ? 'civil_status_id' : type === 'departments' ? 'department_id' : 'position_id'}`] : item.id;
                        const name = isObject(item) ? item[config.nameField] : item.name;
                        return `
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${id}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${escapeHtml(name)}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium space-x-2">
                                    <button onclick="openModal('${type}', ${id})" class="text-blue-600 hover:text-blue-900">Edit</button>
                                    <button onclick="openDeleteModal('${type}', ${id}, '${escapeHtml(name)}')" class="text-red-600 hover:text-red-900">Delete</button>
                                </td>
                            </tr>
                        `;
                    }).join('');
                } else {
                    tableBody.innerHTML = '<tr><td colspan="3" class="px-6 py-4 text-center text-gray-500">Error loading data.</td></tr>';
                }
            } catch (error) {
                console.error('Error loading data:', error);
                tableBody.innerHTML = '<tr><td colspan="3" class="px-6 py-4 text-center text-red-500">Error loading data.</td></tr>';
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
            const label = document.getElementById('modal-label');
            const input = document.getElementById('modal-input');
            const typeInput = document.getElementById('modal-type');
            const idInput = document.getElementById('modal-id');

            typeInput.value = type;
            idInput.value = id || '';
            title.textContent = id ? `Edit ${config.title}` : `Add ${config.title}`;
            label.textContent = config.label;
            input.value = '';

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
            const data = { [config.nameField]: formData.get('name') };

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
        loadData('civil-status');
    </script>
</body>
</html>
