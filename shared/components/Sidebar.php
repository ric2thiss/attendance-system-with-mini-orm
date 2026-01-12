<?php

function Sidebar($nav = null, $data = [], $logo = null)
{
    // Detect current directory depth to adjust paths
    // Get the calling file's directory
    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
    $callingFile = $backtrace[0]['file'] ?? __FILE__;
    $callingDir = dirname($callingFile);
    
    // Calculate relative path from calling file to admin directory
    $adminPath = realpath(__DIR__ . '/../../admin');
    $currentPath = realpath($callingDir);
    
    // Count directory depth difference
    $relativePath = '';
    if ($currentPath && $adminPath && strpos($currentPath, $adminPath) === 0) {
        // Current path is within admin directory
        $relativePath = str_replace($adminPath, '', $currentPath);
        $relativePath = trim($relativePath, DIRECTORY_SEPARATOR);
        
        if (!empty($relativePath)) {
            // Count how many directories deep we are
            $depth = substr_count($relativePath, DIRECTORY_SEPARATOR) + 1;
            $relativePath = str_repeat('../', $depth);
        }
    }
    
    // Menu items with their icons (Heroicons outline)
    $nav_menu = [
        "Dashboard"       => ["link" => $relativePath . "dashboard.php",      "icon" => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-1v-10a1 1 0 00-1-1h-3"></path>'],
        "Attendance Logs" => ["link" => $relativePath . "attendance.php",     "icon" => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>'],
        "Employees"       => ["link" => $relativePath . "employees.php",      "icon" => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20v-2c0-.656-.126-1.283-.356-1.857M9 20H7l-1-1v-6a1 1 0 011-1h10a1 1 0 011 1v6l-1 1h-2"></path>'],
        "Residents"       => ["link" => $relativePath . "residents.php",        "icon" => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9a2 2 0 100 4m0 0a2 2 0 00-2 2v2m2-5a2 2 0 112 2m-2-2a2 2 0 002 2v2m-6 0h6m-6 0v2"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-1v-10a1 1 0 00-1-1h-3"></path>'],
        "Biometric Registration" => ["link" => $relativePath . "biometric-registration.php", "icon" => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"></path>'],
        "Visitors"        => ["link" => $relativePath . "visitors.php",       "icon" => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14c-4.667 0-7.778 3.333-8 5v1h16v-1c-.222-1.667-3.333-5-8-5z"></path>'],
        "Payroll"         => ["link" => $relativePath . "payroll.php",        "icon" => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v1a1 1 0 01-1 1H4a1 1 0 01-1-1V4a1 1 0 011-1h12a1 1 0 011 1v2"></path>'],
        "Master Lists"    => ["link" => $relativePath . "master-lists.php",   "icon" => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>'],
        "Reports"         => ["link" => $relativePath . "reports.php",        "icon" => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m0 10a9 9 0 110-18 9 9 0 010 18z"></path>'],
        "Settings"        => ["link" => $relativePath . "settings.php",       "icon" => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.222.955 3.52 1.096"></path>'],
    ];
    ?>
    <!-- SIDEBAR -->
    <aside class="sidebar-bg w-64 fixed top-0 left-0 h-screen text-white shadow-xl z-10 transition-transform duration-300 transform -translate-x-full md:translate-x-0" id="sidebar">
        <div class="p-6">
            <!-- Logo/System Name -->
            <div class="flex items-center space-x-3 mb-8">
                <img src=<?=$logo? $logo : '../utils/img/logo.png'?> alt="Logo" class="rounded-full w-10 h-10">
                <span class="text-xl font-semibold tracking-wide">Attendance System</span>
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

        <!-- Logout Button -->
        <div class="absolute bottom-0 w-full p-6">
            <a href="/attendance-system/auth/logout.php" class="flex items-center justify-center p-3 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm transition-colors shadow-lg">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" 
                     viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                           d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H7a3 3 0 01-3-3V7a3 3 0 013-3h3a3 3 0 013 3v1"></path>
                </svg>
                Logout
            </a>
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
