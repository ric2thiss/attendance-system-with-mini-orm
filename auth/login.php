<?php
/**
 * DEPRECATED - This login file is no longer used
 * 
 * Authentication is now handled by the external login system at /login.php
 * 
 * This file has been disabled. Please use the centralized login at:
 * http://localhost/login.php
 */

// Redirect to external login
// header("Location: /login.php");
// exit();
require_once __DIR__ . "/../bootstrap.php";
require_once __DIR__ . "/helpers.php";

if (AuthController::check()) {
    header("Location: " . BASE_URL . "/admin/dashboard.php");
    exit;
}

header("Location: /login.php");
exit();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Disabled</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f5f5f5;
        }

        .message {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .message h1 {
            color: #333;
            margin-bottom: 20px;
        }

        .message p {
            color: #666;
            margin-bottom: 20px;
        }

        .message a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }

        .message a:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
    <div class="message">
        <h1>⚠️ Login Disabled</h1>
        <p>This login page is no longer active.</p>
        <p>Please use the centralized login system.</p>
        <a href="/login.php">Go to Login Page</a>
    </div>
</body>

</html>