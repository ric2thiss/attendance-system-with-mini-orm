<?php
require_once __DIR__ . "/../bootstrap.php";
require_once __DIR__ . "/../auth/helpers.php";
requireAuth(); // Require authentication

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
    <title>My Profile</title>
    <link rel="stylesheet" href="../utils/styles/global.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            overflow-x: hidden;
        }
    </style>
</head>
<body>

    <!-- Main Container -->
    <div class="flex min-h-screen">

        <?=Sidebar("Profile", null)?>

        <!-- MAIN CONTENT AREA -->
        <main class="flex-1 md:ml-64 p-6 transition-all duration-300">

            <!-- Top Header Bar -->
            <header class="mb-6">
                <div class="flex justify-between items-center mb-1">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-800">My Profile</h1>
                        <p class="text-gray-500 text-sm"><?= getGreeting($userName) ?></p>
                    </div>
                </div>
                <?php Breadcrumb([
                    ['label' => 'Dashboard', 'link' => 'dashboard.php'],
                    ['label' => 'Profile', 'link' => 'profile.php']
                ]); ?>
            </header>

            <!-- Success/Error Messages -->
            <div id="message-container" class="mb-4 hidden"></div>

            <!-- Profile Content -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Left Column: Profile Info -->
                <div class="lg:col-span-1">
                    <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100">
                        <div class="text-center">
                            <!-- Avatar -->
                            <div class="mb-4">
                                <div class="w-24 h-24 mx-auto rounded-full bg-blue-600 flex items-center justify-center text-white text-3xl font-semibold">
                                    <?php
                                    $initials = '';
                                    if ($currentUser) {
                                        $name = $currentUser['full_name'] ?? $currentUser['username'] ?? 'U';
                                        $parts = explode(' ', $name);
                                        if (count($parts) > 1) {
                                            $initials = strtoupper(substr($parts[0], 0, 1) . substr($parts[count($parts)-1], 0, 1));
                                        } else {
                                            $initials = strtoupper(substr($name, 0, 2));
                                        }
                                    }
                                    echo htmlspecialchars($initials);
                                    ?>
                                </div>
                            </div>
                            
                            <!-- Name -->
                            <h2 class="text-xl font-semibold text-gray-800 mb-1">
                                <?= htmlspecialchars($currentUser ? ($currentUser['full_name'] ?? $currentUser['username']) : 'Guest') ?>
                            </h2>
                            
                            <!-- Role -->
                            <p class="text-sm text-gray-500 mb-4">
                                <?php
                                if ($currentUser && isset($currentUser['role'])) {
                                    $role = ucfirst($currentUser['role']);
                                    echo htmlspecialchars($role);
                                }
                                ?>
                            </p>
                            
                            <!-- Account Info -->
                            <div class="border-t border-gray-200 pt-4 mt-4 text-left">
                                <div class="space-y-2 text-sm">
                                    <div>
                                        <span class="text-gray-500">Account ID:</span>
                                        <span class="ml-2 font-medium text-gray-800">#<?= htmlspecialchars($currentUser['id'] ?? 'N/A') ?></span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Email:</span>
                                        <span class="ml-2 font-medium text-gray-800"><?= htmlspecialchars($currentUser['email'] ?? 'N/A') ?></span>
                                    </div>
                                    <?php if ($currentUser && isset($currentUser['last_login'])): ?>
                                    <div>
                                        <span class="text-gray-500">Last Login:</span>
                                        <span class="ml-2 font-medium text-gray-800">
                                            <?php
                                            if ($currentUser['last_login']) {
                                                echo date('M d, Y H:i', strtotime($currentUser['last_login']));
                                            } else {
                                                echo 'Never';
                                            }
                                            ?>
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Edit Forms -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Profile Information Form -->
                    <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100">
                        <h2 class="text-xl font-semibold text-gray-800 mb-5 border-b pb-3">Profile Information</h2>
                        
                        <form id="profile-form" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                                    <input 
                                        type="text" 
                                        id="username" 
                                        name="username"
                                        class="w-full p-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500"
                                        value="<?= htmlspecialchars($currentUser['username'] ?? '') ?>"
                                        placeholder="Enter username"
                                        required
                                    >
                                    <p class="mt-1 text-xs text-gray-500">Unique username for login</p>
                                </div>
                                
                                <div>
                                    <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                    <input 
                                        type="text" 
                                        id="full_name" 
                                        name="full_name"
                                        class="w-full p-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500"
                                        value="<?= htmlspecialchars($currentUser['full_name'] ?? '') ?>"
                                        placeholder="Enter your full name"
                                    >
                                </div>
                            </div>
                            
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                <input 
                                    type="email" 
                                    id="email" 
                                    name="email"
                                    class="w-full p-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500"
                                    value="<?= htmlspecialchars($currentUser['email'] ?? '') ?>"
                                    placeholder="Enter your email"
                                    required
                                >
                            </div>
                            
                            <div class="pt-4 border-t">
                                <button 
                                    type="submit" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow-md transition-colors"
                                >
                                    Update Profile
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Change Password Form -->
                    <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100">
                        <h2 class="text-xl font-semibold text-gray-800 mb-5 border-b pb-3">Change Password</h2>
                        
                        <form id="password-form" class="space-y-4">
                            <div>
                                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                                <input 
                                    type="password" 
                                    id="current_password" 
                                    name="current_password"
                                    class="w-full p-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Enter current password"
                                    required
                                >
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                                    <input 
                                        type="password" 
                                        id="new_password" 
                                        name="new_password"
                                        class="w-full p-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Enter new password"
                                        minlength="6"
                                        required
                                    >
                                    <p class="mt-1 text-xs text-gray-500">Minimum 6 characters</p>
                                </div>
                                
                                <div>
                                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                                    <input 
                                        type="password" 
                                        id="confirm_password" 
                                        name="confirm_password"
                                        class="w-full p-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Confirm new password"
                                        required
                                    >
                                </div>
                            </div>
                            
                            <div class="pt-4 border-t">
                                <button 
                                    type="submit" 
                                    class="bg-yellow-600 hover:bg-yellow-700 text-white font-medium py-2 px-4 rounded-lg shadow-md transition-colors"
                                >
                                    Change Password
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>

        </main>
    </div>

    <!-- JavaScript -->
    <script type="module">
        const API_BASE = '../api/profile/index.php';
        
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

        // Profile form handler
        document.getElementById('profile-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = {
                username: document.getElementById('username').value.trim(),
                full_name: document.getElementById('full_name').value.trim(),
                email: document.getElementById('email').value.trim()
            };
            
            try {
                const response = await fetch(API_BASE, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showMessage('Profile updated successfully!', 'success');
                    // Optionally reload page after 1 second to reflect changes
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showMessage(data.message || 'Failed to update profile', 'error');
                }
            } catch (error) {
                console.error('Error updating profile:', error);
                showMessage('An error occurred. Please try again.', 'error');
            }
        });

        // Password form handler
        document.getElementById('password-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            // Validate passwords match
            if (newPassword !== confirmPassword) {
                showMessage('New passwords do not match', 'error');
                return;
            }
            
            // Validate password length
            if (newPassword.length < 6) {
                showMessage('Password must be at least 6 characters', 'error');
                return;
            }
            
            const formData = {
                current_password: document.getElementById('current_password').value,
                new_password: newPassword
            };
            
            try {
                const response = await fetch(API_BASE, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ ...formData, action: 'change_password' })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showMessage('Password changed successfully!', 'success');
                    // Reset form
                    document.getElementById('password-form').reset();
                } else {
                    showMessage(data.message || 'Failed to change password', 'error');
                }
            } catch (error) {
                console.error('Error changing password:', error);
                showMessage('An error occurred. Please try again.', 'error');
            }
        });

        // Sidebar toggle
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const sidebar = document.getElementById('sidebar');
            
            if (sidebarToggle && sidebar) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('-translate-x-full');
                });
            }
        });
    </script>

</body>
</html>
