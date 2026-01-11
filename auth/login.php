<?php
require_once __DIR__ . "/../bootstrap.php";

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If already logged in, redirect to dashboard
if (AuthController::check()) {
    header("Location: /attendance-system/admin/dashboard.php");
    exit;
}

$error = "";
$success = "";

// Handle login form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usernameOrEmail = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if (empty($usernameOrEmail) || empty($password)) {
        $error = "Please enter both username/email and password";
    } else {
        $authController = new AuthController();
        $result = $authController->login($usernameOrEmail, $password);

        if ($result["success"]) {
            // Redirect to dashboard
            header("Location: /attendance-system/admin/dashboard.php");
            exit;
        } else {
            $error = $result["message"];
        }
    }
}

// Check for logout message
if (isset($_GET["logout"]) && $_GET["logout"] === "success") {
    $success = "You have been successfully logged out.";
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

