<?php
require_once __DIR__ . "/../bootstrap.php";
require_once __DIR__ . "/helpers.php";

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If already logged in, redirect to dashboard
if (AuthController::check()) {
    header("Location: " . BASE_URL . "/admin/dashboard.php");
    exit;
}

$error = "";
$success = "";

// Check maintenance mode
$maintenanceMode = false;
$maintenanceMessage = "";
try {
    $settingsController = new SettingsController();
    $maintenanceCheck = $settingsController->checkMaintenanceMode();
    $maintenanceMode = $maintenanceCheck["maintenance_mode"] ?? false;
    $maintenanceMessage = $maintenanceCheck["message"] ?? "The system is currently under maintenance. Please try again later.";
} catch (Exception $e) {
    error_log("Login page - Error checking maintenance mode: " . $e->getMessage());
}

// Handle login form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Check maintenance mode - only allow admin to login
    if ($maintenanceMode) {
        $usernameOrEmail = trim($_POST["username"] ?? "");
        $password = $_POST["password"] ?? "";
        
        // Try to login to check if user is admin
        $authController = new AuthController();
        $result = $authController->login($usernameOrEmail, $password);
        
        if ($result["success"]) {
            $user = $result["admin"] ?? null;
            // Only allow administrator role during maintenance
            if ($user && $user["role"] === "administrator") {
                // Admin can login during maintenance
                header("Location: " . BASE_URL . "/admin/dashboard.php");
                exit;
            } else {
                // Log out non-admin users immediately
                $authController->logout();
                $error = "System is under maintenance. Only administrators can access the system at this time.";
            }
        } else {
            $error = $result["message"];
        }
    } else {
        // Normal login flow
        $usernameOrEmail = trim($_POST["username"] ?? "");
        $password = $_POST["password"] ?? "";

        if (empty($usernameOrEmail) || empty($password)) {
            $error = "Please enter both username/email and password";
        } else {
            $authController = new AuthController();
            $result = $authController->login($usernameOrEmail, $password);

            if ($result["success"]) {
                // Redirect to dashboard
                header("Location: " . BASE_URL . "/admin/dashboard.php");
                exit;
            } else {
                $error = $result["message"];
            }
        }
    }
}

// Check for logout message
if (isset($_GET["logout"]) && $_GET["logout"] === "success") {
    $success = "You have been successfully logged out.";
}

// Maintenance redirect message (from protected pages/API)
if (isset($_GET["error"]) && $_GET["error"] === "maintenance") {
    if (!$error) {
        $error = $maintenanceMessage ?: "The system is currently under maintenance. Please try again later.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Attendance System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: #f5f5f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .btn-login {
            background-color: #374151;
        }
        .btn-login:hover {
            background-color: #1f2937;
        }
    </style>
</head>
<body>
    <div class="w-full max-w-md">
        <div class="bg-white rounded-lg shadow-2xl p-8">
            <!-- Logo/Header -->
            <div class="mb-8">
                <div class="flex items-center justify-center space-x-3 mb-2">
                    <img src="../utils/img/logo.png" alt="Logo" class="w-12 h-12 object-contain">
                    <h1 class="text-3xl font-bold text-gray-800">Attendance System</h1>
                </div>
                <p class="text-center text-gray-600">Sign in to your account</p>
            </div>

            <!-- Maintenance Mode Notice -->
            <?php if ($maintenanceMode): ?>
                <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded mb-4">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div>
                            <p class="font-semibold">System Maintenance</p>
                            <p class="text-sm mt-1"><?= htmlspecialchars($maintenanceMessage) ?></p>
                            <p class="text-sm mt-1 font-medium">Only administrators can access the system during maintenance.</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Error Message -->
            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
                    <p class="font-medium"><?= htmlspecialchars($error) ?></p>
                </div>
            <?php endif; ?>

            <!-- Success Message -->
            <?php if ($success): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-4">
                    <p class="font-medium"><?= htmlspecialchars($success) ?></p>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" action="" class="space-y-6">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                        Username or Email
                    </label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        required
                        autofocus
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none transition"
                        placeholder="Enter your username or email"
                        value="<?= htmlspecialchars($_POST["username"] ?? "") ?>"
                    >
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Password
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none transition"
                        placeholder="Enter your password"
                    >
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <input type="checkbox" name="remember" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                        <span class="ml-2 text-sm text-gray-600">Remember me</span>
                    </label>
                    <a href="#" class="text-sm text-purple-600 hover:text-purple-800">Forgot password?</a>
                </div>

                <button 
                    type="submit" 
                    class="w-full btn-login text-white font-semibold py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition duration-200"
                >
                    Sign In
                </button>
            </form>
        </div>

        <!-- Copyright -->
        <p class="text-center text-gray-700 text-sm mt-6">
            © <?= date("Y") ?> Attendance System. All rights reserved.
        </p>
    </div>
</body>
</html>

