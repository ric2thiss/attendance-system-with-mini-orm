<?php
require_once __DIR__ . "/../bootstrap.php";
require_once __DIR__ . "/../auth/helpers.php";
requireAuth(); // Require authentication - redirects to login if not authenticated

include_once '../shared/components/Sidebar.php';
include_once '../shared/components/Breadcrumb.php';

// Get current user for greeting
$currentUser = currentUser();
$userName = $currentUser ? ($currentUser['full_name'] ?? $currentUser['username']) : 'Guest';

// Get search query
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get residents data
$residentController = new ResidentController();
$residents = [];

if (!empty($searchQuery)) {
    // Get paginated results with search
    $data = $residentController->getPaginatedResidents(1, 50, $searchQuery, []);
    $residents = $data['residents'];
} else {
    // Get all residents if no search query
    $residents = $residentController->getAllResidents();
}

// Check if resident already has fingerprint enrolled
$pdo = (new Database())->connect();
$residentFingerprintsRepository = new ResidentFingerprintsRepository($pdo);

// Add enrollment status to each resident
foreach ($residents as &$resident) {
    $residentId = is_array($resident) ? ($resident['resident_id'] ?? null) : ($resident->resident_id ?? null);
    if ($residentId) {
        $resident['has_fingerprint'] = $residentFingerprintsRepository->existsByResidentId(intval($residentId));
    } else {
        $resident['has_fingerprint'] = false;
    }
}
unset($resident); // Break reference

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biometric Registration</title>
    <!-- Load global css -->
    <link rel="stylesheet" href="../utils/styles/global.css">
    <!-- Load Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .btn-primary {
            background-color: #007bff;
            transition: background-color 0.2s;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

    <!-- Main Container -->
    <div class="flex min-h-screen">

        <?=Sidebar("Biometric Registration", null, "../utils/img/logo.png")?>

        <!-- 2. MAIN CONTENT AREA -->
        <main class="flex-1 md:ml-64 p-6 transition-all duration-300">

            <!-- Top Header Bar -->
            <header class="mb-6">
                <div class="flex justify-between items-center mb-1">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-800">Biometric Registration</h1>
                        <p class="text-gray-500 text-sm"><?= getGreeting($userName) ?> - Register fingerprints for residents.</p>
                    </div>
                </div>
                <?php Breadcrumb([
                    ['label' => 'Dashboard', 'link' => 'dashboard.php'],
                    ['label' => 'Biometric Registration', 'link' => 'biometric-registration.php']
                ]); ?>
            </header>

            <!-- Search and Selection Section -->
            <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Search and Select Resident</h2>
                
                <!-- Search Form -->
                <form method="GET" action="" class="mb-6">
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <input type="text" 
                                name="search" 
                                id="searchInput"
                                placeholder="Search by name, resident ID, or PhilSys number..." 
                                value="<?= htmlspecialchars($searchQuery) ?>"
                                class="w-full py-2 pl-10 pr-10 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <?php if (!empty($searchQuery)): ?>
                            <a href="?" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </a>
                            <?php endif; ?>
                        </div>
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors whitespace-nowrap">
                            Search
                        </button>
                    </div>
                </form>

                <!-- Selected Resident Display -->
                <div id="selectedResident" class="hidden mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-semibold text-gray-800">Selected Resident:</h3>
                            <p class="text-sm text-gray-600" id="selectedResidentName"></p>
                            <p class="text-xs text-gray-500" id="selectedResidentId"></p>
                        </div>
                        <button type="button" id="clearSelection" class="text-sm text-gray-600 hover:text-gray-800">
                            Clear Selection
                        </button>
                    </div>
                </div>

                <!-- Residents List -->
                <?php if (empty($searchQuery)): ?>
                    <div class="text-center py-8 text-gray-500">
                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <p class="text-lg font-medium">Search for a resident to begin</p>
                        <p class="text-sm mt-2">Enter a name, resident ID, or PhilSys number in the search box above.</p>
                    </div>
                <?php elseif (empty($residents)): ?>
                    <div class="text-center py-8 text-gray-500">
                        <p class="text-lg font-medium">No residents found</p>
                        <p class="text-sm mt-2">Try a different search term.</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-2 max-h-96 overflow-y-auto">
                        <?php foreach ($residents as $resident): ?>
                            <div class="resident-item p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer"
                                 data-resident-id="<?= htmlspecialchars($resident['resident_id']) ?>"
                                 data-resident-name="<?= htmlspecialchars(($resident['first_name'] ?? '') . ' ' . ($resident['middle_name'] ?? '') . ' ' . ($resident['last_name'] ?? '') . ' ' . ($resident['suffix'] ?? '')) ?>">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <h3 class="font-medium text-gray-800">
                                            <?= htmlspecialchars(trim(($resident['first_name'] ?? '') . ' ' . ($resident['middle_name'] ?? '') . ' ' . ($resident['last_name'] ?? '') . ' ' . ($resident['suffix'] ?? ''))) ?>
                                        </h3>
                                        <p class="text-sm text-gray-600">
                                            ID: <?= htmlspecialchars($resident['resident_id'] ?? '') ?>
                                            <?php if (!empty($resident['phil_sys_number'])): ?>
                                                | PhilSys: <?= htmlspecialchars($resident['phil_sys_number']) ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <div class="ml-4">
                                        <?php if ($resident['has_fingerprint']): ?>
                                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                Enrolled
                                            </span>
                                        <?php else: ?>
                                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Not Enrolled
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Fingerprint Registration Section -->
            <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Fingerprint Registration</h2>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <div id="registrationInstructions" class="space-y-4">
                        <p class="text-sm text-gray-700">
                            <strong>Instructions:</strong>
                        </p>
                        <ol class="list-decimal list-inside space-y-2 text-sm text-gray-700 ml-4">
                            <li>Search and select a resident from the list above</li>
                            <li>Click the "Register Fingerprint" button below</li>
                            <li>The fingerprint enrollment application will launch</li>
                            <li>Follow the on-screen instructions to complete the registration</li>
                        </ol>
                    </div>

                    <div id="selectedResidentInfo" class="hidden mt-4 p-4 bg-white rounded-lg border border-blue-300">
                        <p class="text-sm font-medium text-gray-800 mb-2">Ready to register fingerprint for:</p>
                        <p class="text-sm text-gray-700" id="registrationResidentName"></p>
                        <p class="text-xs text-gray-500 mt-1" id="registrationResidentId"></p>
                    </div>

                    <div class="mt-6">
                        <button type="button" 
                                id="registerFingerprintBtn" 
                                disabled
                                class="px-6 py-3 bg-gray-400 text-white font-medium rounded-lg cursor-not-allowed transition-colors">
                            <span class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"></path>
                                </svg>
                                Register Fingerprint
                            </span>
                        </button>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <!-- JavaScript -->
    <script>
        // --- Mobile Sidebar Toggle Logic ---
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

        if (mainContent) {
            mainContent.addEventListener('click', () => {
                if (window.innerWidth < 768 && sidebar.classList.contains('translate-x-0')) {
                    sidebar.classList.remove('translate-x-0');
                    sidebar.classList.add('-translate-x-full');
                    mainContent.classList.remove('opacity-50', 'pointer-events-none');
                }
            });
        }

        // Resident Selection Logic
        let selectedResidentId = null;
        let selectedResidentName = null;

        // Handle resident item clicks
        document.querySelectorAll('.resident-item').forEach(item => {
            item.addEventListener('click', function() {
                // Remove previous selection
                document.querySelectorAll('.resident-item').forEach(i => {
                    i.classList.remove('bg-blue-100', 'border-blue-400');
                    i.classList.add('border-gray-200');
                });

                // Add selection to clicked item
                this.classList.add('bg-blue-100', 'border-blue-400');
                this.classList.remove('border-gray-200');

                // Get resident data
                selectedResidentId = this.getAttribute('data-resident-id');
                selectedResidentName = this.getAttribute('data-resident-name');

                // Show selected resident info
                document.getElementById('selectedResident').classList.remove('hidden');
                document.getElementById('selectedResidentName').textContent = selectedResidentName;
                document.getElementById('selectedResidentId').textContent = 'ID: ' + selectedResidentId;

                // Show registration info
                document.getElementById('selectedResidentInfo').classList.remove('hidden');
                document.getElementById('registrationResidentName').textContent = selectedResidentName;
                document.getElementById('registrationResidentId').textContent = 'Resident ID: ' + selectedResidentId;

                // Enable register button
                const registerBtn = document.getElementById('registerFingerprintBtn');
                registerBtn.disabled = false;
                registerBtn.classList.remove('bg-gray-400', 'cursor-not-allowed');
                registerBtn.classList.add('bg-blue-600', 'hover:bg-blue-700', 'cursor-pointer');
            });
        });

        // Clear selection
        document.getElementById('clearSelection')?.addEventListener('click', function() {
            selectedResidentId = null;
            selectedResidentName = null;

            // Remove selection styling
            document.querySelectorAll('.resident-item').forEach(i => {
                i.classList.remove('bg-blue-100', 'border-blue-400');
                i.classList.add('border-gray-200');
            });

            // Hide selected resident info
            document.getElementById('selectedResident').classList.add('hidden');
            document.getElementById('selectedResidentInfo').classList.add('hidden');

            // Disable register button
            const registerBtn = document.getElementById('registerFingerprintBtn');
            registerBtn.disabled = true;
            registerBtn.classList.add('bg-gray-400', 'cursor-not-allowed');
            registerBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700', 'cursor-pointer');
        });

        // Register fingerprint button
        document.getElementById('registerFingerprintBtn')?.addEventListener('click', function() {
            if (!selectedResidentId) {
                alert('Please select a resident first.');
                return;
            }

            // Launch Enrollment.exe using the biometrics:// protocol
            // The protocol should be registered in Windows to work properly
            // Note: C# application expects 'resident_id' (with underscore), not 'residentId'
            const protocolUrl = 'biometrics://enroll?resident_id=' + encodeURIComponent(selectedResidentId);
            
            // Log for debugging
            console.log('Launching enrollment with URL:', protocolUrl);
            console.log('Resident ID:', selectedResidentId);
            
            try {
                // Try to launch using the custom protocol
                // Using window.location.href to trigger the protocol handler
                window.location.href = protocolUrl;
                
                // Show confirmation message after a short delay
                setTimeout(() => {
                    console.log('Protocol handler triggered');
                }, 100);
            } catch (error) {
                // Fallback: show instructions
                console.error('Error launching enrollment:', error);
                alert('Unable to launch enrollment application automatically.\n\nError: ' + error.message + '\n\nPlease manually launch Enrollment.exe with:\n\nbiometrics://enroll?resident_id=' + selectedResidentId);
            }
        });
    </script>
</body>
</html>
