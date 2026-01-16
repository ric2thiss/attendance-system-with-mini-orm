<?php
require_once __DIR__ . "/../bootstrap.php";
require_once __DIR__ . "/../auth/helpers.php";
requireAuth(); // Require authentication
requireRole('administrator'); // Only administrators can access settings

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
    <title>System Settings</title>
    <link rel="stylesheet" href="../utils/styles/global.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            overflow-x: hidden;
        }
        .settings-tab-active {
            border-bottom: 2px solid #3b82f6;
            color: #1f2937;
            font-weight: 600;
        }
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .slider {
            background-color: #3b82f6;
        }
        input:checked + .slider:before {
            transform: translateX(26px);
        }
        input:disabled + .slider {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
</head>
<body>

    <!-- Main Container -->
    <div class="flex min-h-screen">

        <?=Sidebar("Settings", null)?>

        <!-- MAIN CONTENT AREA -->
        <main class="flex-1 md:ml-64 p-6 transition-all duration-300">

            <!-- Top Header Bar -->
            <header class="mb-6">
                <div class="flex justify-between items-center mb-1">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-800">System Settings</h1>
                        <p class="text-gray-500 text-sm"><?= getGreeting($userName) ?></p>
                    </div>
                </div>
                <?php Breadcrumb([
                    ['label' => 'Dashboard', 'link' => 'dashboard.php'],
                    ['label' => 'Settings', 'link' => 'settings.php']
                ]); ?>
            </header>

            <!-- Settings Navigation Tabs -->
            <div class="border-b border-gray-200 mb-6">
                <nav class="-mb-px flex space-x-8" id="settings-tabs">
                    <a href="#" onclick="showTab('general')" class="settings-tab whitespace-nowrap py-3 px-1 border-b-2 border-transparent text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-colors settings-tab-active" data-tab="general">
                        General
                    </a>
                    <a href="#" onclick="showTab('maintenance')" class="settings-tab whitespace-nowrap py-3 px-1 border-b-2 border-transparent text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-colors" data-tab="maintenance">
                        Maintenance Mode
                    </a>
                    <a href="#" onclick="showTab('security')" class="settings-tab whitespace-nowrap py-3 px-1 border-b-2 border-transparent text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-colors" data-tab="security">
                        Security
                    </a>
                </nav>
            </div>

            <!-- Success/Error Messages -->
            <div id="message-container" class="mb-4 hidden"></div>

            <!-- SETTINGS CONTENT -->
            <div id="settings-content">

                <!-- 1. General Settings Tab (Default Active) -->
                <div id="general" class="settings-tab-content bg-white p-6 rounded-xl shadow-lg border border-gray-100">
                    <h2 class="text-xl font-semibold text-gray-800 mb-5 border-b pb-3">General Configuration</h2>
                    
                    <div class="space-y-6">
                        <!-- Application Name -->
                        <div>
                            <label for="app_name" class="block text-sm font-medium text-gray-700 mb-1">Application Name</label>
                            <input type="text" id="app_name" class="mt-1 block w-full md:w-1/2 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border p-2">
                            <p class="mt-1 text-xs text-gray-500">The name displayed throughout the application</p>
                        </div>

                        <!-- Timezone Selector -->
                        <div>
                            <label for="timezone" class="block text-sm font-medium text-gray-700 mb-1">Default Time Zone</label>
                            <select id="timezone" class="mt-1 block w-full md:w-1/2 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-lg border">
                                <option value="UTC-8:00">UTC-8:00 (Pacific Time)</option>
                                <option value="UTC-5:00">UTC-5:00 (Eastern Time)</option>
                                <option value="Asia/Manila" selected>UTC+8:00 (Philippine Standard Time)</option>
                                <option value="UTC+0:00">UTC+0:00 (Greenwich Mean Time)</option>
                                <option value="Asia/Singapore">UTC+8:00 (Singapore Time)</option>
                                <option value="Asia/Tokyo">UTC+9:00 (Japan Standard Time)</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">This affects all time log and reporting calculations.</p>
                        </div>

                        <!-- Data Retention Policy -->
                        <div>
                            <label for="data_retention_days" class="block text-sm font-medium text-gray-700 mb-1">Data Retention Period (Attendance Logs)</label>
                            <input type="number" id="data_retention_days" min="30" max="3650" class="mt-1 block w-full md:w-1/4 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border p-2">
                            <p class="mt-1 text-xs text-gray-500">Days to keep detailed logs before archival (30-3650 days).</p>
                        </div>
                    </div>
                    
                    <div class="mt-8 pt-4 border-t">
                        <button onclick="saveGeneralSettings()" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow-md transition-colors">
                            Save General Settings
                        </button>
                    </div>
                </div>

                <!-- 2. Maintenance Mode Tab (Hidden by default) -->
                <div id="maintenance" class="settings-tab-content bg-white p-6 rounded-xl shadow-lg border border-gray-100 hidden">
                    <h2 class="text-xl font-semibold text-gray-800 mb-5 border-b pb-3">Maintenance Mode</h2>
                    
                    <div class="space-y-6">
                        <!-- Maintenance Mode Toggle -->
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                            <div class="flex items-start">
                                <svg class="w-6 h-6 text-yellow-600 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-yellow-800 mb-2">Maintenance Mode</h3>
                                    <p class="text-sm text-yellow-700 mb-4">
                                        When enabled, only administrators can log in to the system. All other users will be blocked from accessing the system until maintenance mode is disabled.
                                    </p>
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-medium text-gray-800">Enable Maintenance Mode</p>
                                            <p class="text-xs text-gray-600 mt-1">Toggle maintenance mode on or off</p>
                                        </div>
                                        <label class="toggle-switch">
                                            <input type="checkbox" id="maintenance_mode" onchange="onMaintenanceToggle()">
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Maintenance Message -->
                        <div>
                            <label for="maintenance_message" class="block text-sm font-medium text-gray-700 mb-1">Maintenance Message</label>
                            <textarea id="maintenance_message" rows="3" class="mt-1 block w-full md:w-2/3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border p-2" placeholder="Enter a message to display during maintenance mode"></textarea>
                            <p class="mt-1 text-xs text-gray-500">This message will be shown to non-admin users when they try to log in during maintenance mode.</p>
                        </div>
                    </div>
                    
                    <div class="mt-8 pt-4 border-t">
                        <button onclick="saveMaintenanceSettings()" class="bg-yellow-600 hover:bg-yellow-700 text-white font-medium py-2 px-4 rounded-lg shadow-md transition-colors">
                            Save Maintenance Settings
                        </button>
                    </div>
                </div>

                <!-- 3. Security Settings Tab (Hidden by default) -->
                <div id="security" class="settings-tab-content bg-white p-6 rounded-xl shadow-lg border border-gray-100 hidden">
                    <h2 class="text-xl font-semibold text-gray-800 mb-5 border-b pb-3">Security & Authentication</h2>
                    
                    <div class="space-y-6">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h3 class="font-semibold text-blue-800 mb-2">Security Information</h3>
                            <p class="text-sm text-blue-700">
                                Security settings and authentication options will be available here in a future update.
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- JavaScript -->
    <script type="module">
        const API_BASE = '../api/settings/index.php';
        
        let currentSettings = {};

        // Load settings on page load
        async function loadSettings() {
            try {
                const response = await fetch(API_BASE);
                const data = await response.json();
                
                if (data.success && data.settings) {
                    currentSettings = data.settings;
                    
                    // Populate form fields
                    if (currentSettings.app_name) {
                        document.getElementById('app_name').value = currentSettings.app_name.value || '';
                    }
                    if (currentSettings.timezone) {
                        document.getElementById('timezone').value = currentSettings.timezone.value || 'Asia/Manila';
                    }
                    if (currentSettings.data_retention_days) {
                        document.getElementById('data_retention_days').value = currentSettings.data_retention_days.value || 365;
                    }
                    if (currentSettings.maintenance_mode) {
                        document.getElementById('maintenance_mode').checked = currentSettings.maintenance_mode.value || false;
                    }
                    if (currentSettings.maintenance_message) {
                        document.getElementById('maintenance_message').value = currentSettings.maintenance_message.value || '';
                    }
                }
            } catch (error) {
                console.error('Error loading settings:', error);
                showMessage('Failed to load settings', 'error');
            }
        }

        // Save general settings
        window.saveGeneralSettings = async function() {
            const settings = {
                app_name: document.getElementById('app_name').value,
                timezone: document.getElementById('timezone').value,
                data_retention_days: parseInt(document.getElementById('data_retention_days').value) || 365
            };

            try {
                const response = await fetch(API_BASE, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(settings)
                });

                const data = await response.json();
                
                if (data.success) {
                    showMessage('General settings saved successfully', 'success');
                    loadSettings(); // Reload to sync
                } else {
                    showMessage(data.message || 'Failed to save settings', 'error');
                }
            } catch (error) {
                console.error('Error saving settings:', error);
                showMessage('Failed to save settings', 'error');
            }
        };

        // Save maintenance settings
        window.saveMaintenanceSettings = async function() {
            const settings = {
                maintenance_mode: document.getElementById('maintenance_mode').checked ? 1 : 0,
                maintenance_message: document.getElementById('maintenance_message').value
            };

            try {
                const response = await fetch(API_BASE, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(settings)
                });

                const data = await response.json();
                
                if (data.success) {
                    const mode = settings.maintenance_mode ? 'enabled' : 'disabled';
                    showMessage(`Maintenance mode ${mode} successfully`, 'success');
                    loadSettings(); // Reload to sync
                } else {
                    showMessage(data.message || 'Failed to save maintenance settings', 'error');
                }
            } catch (error) {
                console.error('Error saving maintenance settings:', error);
                showMessage('Failed to save maintenance settings', 'error');
            }
        };

        // Maintenance toggle handler
        window.onMaintenanceToggle = function() {
            const isEnabled = document.getElementById('maintenance_mode').checked;
            const message = isEnabled 
                ? 'Maintenance mode will be enabled when you save. Only administrators will be able to log in.'
                : 'Maintenance mode will be disabled when you save. All users will be able to log in.';
            
            // Show confirmation for enabling maintenance mode
            if (isEnabled) {
                if (!confirm(message + '\n\nDo you want to continue?')) {
                    document.getElementById('maintenance_mode').checked = false;
                    return;
                }
            }
        };

        // Show message
        function showMessage(message, type = 'success') {
            const container = document.getElementById('message-container');
            const bgColor = type === 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800';
            
            container.className = `mb-4 p-4 rounded-lg border ${bgColor}`;
            container.innerHTML = `<p class="font-medium">${message}</p>`;
            container.classList.remove('hidden');
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                container.classList.add('hidden');
            }, 5000);
        }

        // Tab switching
        window.showTab = function(tabName) {
            // Hide all content tabs
            document.querySelectorAll('.settings-tab-content').forEach(content => {
                content.classList.add('hidden');
            });

            // Deactivate all navigation tabs
            document.querySelectorAll('.settings-tab').forEach(tab => {
                tab.classList.remove('settings-tab-active');
            });

            // Show the selected content tab
            const activeContent = document.getElementById(tabName);
            if (activeContent) {
                activeContent.classList.remove('hidden');
            }

            // Activate the selected navigation tab
            const activeTab = document.querySelector(`.settings-tab[data-tab="${tabName}"]`);
            if (activeTab) {
                activeTab.classList.add('settings-tab-active');
            }
        };

        // Sidebar toggle
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const sidebar = document.getElementById('sidebar');
            
            if (sidebarToggle && sidebar) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('-translate-x-full');
                });
            }
            
            // Load settings
            loadSettings();
        });
    </script>

</body>
</html>
