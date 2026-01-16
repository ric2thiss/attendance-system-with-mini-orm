<?php
require_once __DIR__ . "/../bootstrap.php";

$authController = new AuthController();
$authController->logout();

// Redirect to login page with success message
// BASE_URL is already defined in bootstrap.php via config/app.config.php
header("Location: " . BASE_URL . "/auth/login.php?logout=success");
exit;

