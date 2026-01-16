<?php
/**
 * Payroll Sidebar Component
 * 
 * Dedicated sidebar for Payroll Management system.
 * Uses same styling and theme as Attendance system for visual consistency.
 * Contains payroll-specific navigation items following Philippine government standards.
 */

function PayrollSidebar($nav = null, $data = [], $logo = null)
{
    // Detect current directory depth to adjust paths
    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
    $callingFile = $backtrace[0]['file'] ?? __FILE__;
    $callingDir = dirname($callingFile);
    
    // Calculate relative path from calling file to admin directory
    $adminPath = realpath(__DIR__ . '/../../admin');
    $currentPath = realpath($callingDir);
    
    // Count directory depth difference
    $relativePath = '';
    if ($currentPath && $adminPath && strpos($currentPath, $adminPath) === 0) {
        $relativePath = str_replace($adminPath, '', $currentPath);
        $relativePath = trim($relativePath, DIRECTORY_SEPARATOR);
        
        if (!empty($relativePath)) {
            $depth = substr_count($relativePath, DIRECTORY_SEPARATOR) + 1;
            $relativePath = str_repeat('../', $depth);
        }
    }
    
    // Payroll-specific menu items (Philippine Government Standard)
    $nav_menu = [
        "Payroll Dashboard" => [
            "link" => $relativePath . "payroll.php", 
            "icon" => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-1v-10a1 1 0 00-1-1h-3"></path>'
        ],
        "Process Payroll" => [
            "link" => $relativePath . "payroll.php?action=process", 
            "icon" => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>'
        ],
        "Employee Payroll Records" => [
            "link" => $relativePath . "payroll.php?action=employees", 
            "icon" => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20v-2c0-.656-.126-1.283-.356-1.857M9 20H7l-1-1v-6a1 1 0 011-1h10a1 1 0 011 1v6l-1 1h-2"></path>'
        ],
        "Payroll History" => [
            "link" => $relativePath . "payroll.php?action=history", 
            "icon" => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>'
        ],
        "Payroll Reports" => [
            "link" => $relativePath . "payroll.php?action=reports", 
            "icon" => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m0 10a9 9 0 110-18 9 9 0 010 18z"></path>'
        ],
        "Payroll Settings" => [
            "link" => $relativePath . "payroll.php?action=settings", 
            "icon" => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.222.955 3.52 1.096"></path>'
        ],
    ];
    ?>
    <!-- PAYROLL SIDEBAR -->
    <aside class="sidebar-bg w-64 fixed top-0 left-0 h-screen text-white shadow-xl z-10 transition-transform duration-300 transform -translate-x-full md:translate-x-0 flex flex-col" id="sidebar">
        <!-- Scrollable Content Area -->
        <div class="flex-1 overflow-y-auto sidebar-scrollable">
            <div class="p-6">
                <!-- Logo/System Name -->
                <div class="flex items-center space-x-3 mb-8">
                    <img src=<?=$logo? $logo : '../utils/img/logo.png'?> alt="Logo" class="rounded-full w-10 h-10">
                    <span class="text-xl font-semibold tracking-wide">Payroll System</span>
                </div>

                <!-- User Greeting -->
                <div class="mb-10 p-3 bg-white bg-opacity-10 rounded-lg">
                    <p class="text-sm text-gray-300">Welcome back,</p>
                    <p class="font-medium">
                        <?php
                        // Get authenticated user name
                        if (function_exists('currentUser')) {
                            $user = currentUser();
                            echo htmlspecialchars($user ? ($user['full_name'] ?? $user['username']) : 'Guest');
                        } else {
                            echo 'Guest';
                        }
                        ?>
                    </p>
                </div>

                <!-- Navigation Links -->
                <nav class="space-y-1">
                    <?php foreach ($nav_menu as $label => $item): ?>
                        <a href="<?= $item['link'] ?>" 
                           class="flex items-center p-3 rounded-lg text-sm transition-colors 
                                  hover:bg-white hover:bg-opacity-10 <?= ($nav === $label ? 'active-link' : '') ?>">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" 
                                 viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                 <?= $item['icon'] ?>
                            </svg>
                            <?= $label ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </div>
        </div>

        <!-- Fixed Bottom Section -->
        <div class="flex-shrink-0 w-full">
            <!-- Back to Attendance System Link -->
            <div class="px-6 pb-3">
                <a href="<?= $relativePath ?>dashboard.php" 
                   class="flex items-center justify-center p-3 rounded-lg bg-gray-700 hover:bg-gray-600 text-white text-sm transition-colors shadow-lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" 
                         viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                               d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Attendance
                </a>
            </div>

            <!-- Logout Button -->
            <div class="p-6 pt-0">
                <?php
                if (!defined("BASE_URL")) {
                    require_once __DIR__ . "/../../config/app.config.php";
                }
                ?>
                <a href="<?= BASE_URL ?>/auth/logout.php" class="flex items-center justify-center p-3 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm transition-colors shadow-lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" 
                         viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                               d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H7a3 3 0 01-3-3V7a3 3 0 013-3h3a3 3 0 013 3v1"></path>
                    </svg>
                    Logout
                </a>
            </div>
        </div>
    </aside>

    <!-- Sidebar Toggle for Mobile -->
    <button id="sidebar-toggle" class="md:hidden fixed top-4 left-4 z-20 p-2 bg-blue-600 text-white rounded-full shadow-lg">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" 
             viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                   d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
    </button>
    <?php
}
