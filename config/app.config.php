<?php
/**
 * Application Configuration
 * This file contains all configuration settings for the attendance system
 */

// Base URL Configuration
define("BASE_URL", "http://localhost/attendance-system");
// BASE_PATH is already defined in config.php, so we don't redefine it here
if (!defined("BASE_PATH")) {
    define("BASE_PATH", __DIR__ . "/../");
}

// WebSocket Configuration
define("WEBSOCKET_HOST", "localhost");
define("WEBSOCKET_PORT", 8081);
define("WEBSOCKET_URL", "ws://" . WEBSOCKET_HOST . ":" . WEBSOCKET_PORT);

// API Configuration
define("API_BASE_URL", BASE_URL . "/api");

// OLD ROUTER ENDPOINTS (kept for backward compatibility)
// These routers redirect to the new modular endpoints
define("API_SERVICES_URL", API_BASE_URL . "/services.php"); // Router - redirects to modular endpoints
define("API_V1_URL", API_BASE_URL . "/v1/request.php"); // Router - redirects to modular endpoints

// NEW MODULAR API ENDPOINTS
define("API_ENDPOINT_TEMPLATES", API_BASE_URL . "/templates/index.php");
define("API_ENDPOINT_ATTENDANCES", API_BASE_URL . "/attendance/index.php");
define("API_ENDPOINT_ATTENDANCE_WINDOWS", API_BASE_URL . "/attendance/windows.php");
define("API_ENDPOINT_EMPLOYEES", API_BASE_URL . "/employees/index.php");
define("API_ENDPOINT_EMPLOYEES_STORE", API_BASE_URL . "/employees/store.php");
define("API_ENDPOINT_ATTENDANCE_STATS", API_BASE_URL . "/attendance/stats.php");
define("API_ENDPOINT_ATTENDANCE_BETWEEN", API_BASE_URL . "/attendance/between.php");
define("API_ENDPOINT_RESIDENTS", API_BASE_URL . "/residents/index.php");
define("API_ENDPOINT_RESIDENTS_SHOW", API_BASE_URL . "/residents/show.php");
define("API_ENDPOINT_RESIDENTS_DELETE", API_BASE_URL . "/residents/delete.php");
define("API_ENDPOINT_VISITORS_STATS", API_BASE_URL . "/visitors/stats.php");

// OLD ENDPOINT CONSTANTS (backward compatible - point to router which redirects)
// define("API_ENDPOINT_TEMPLATES_OLD", API_SERVICES_URL . "?resource=templates");
// define("API_ENDPOINT_ATTENDANCES_OLD", API_SERVICES_URL . "?resource=attendances");
// define("API_ENDPOINT_ATTENDANCE_WINDOWS_OLD", API_SERVICES_URL . "?resource=attendance-windows");
// define("API_ENDPOINT_EMPLOYEES_OLD", API_SERVICES_URL . "?resource=employees");

// Direct PHP Endpoints
define("ENROLL_ENDPOINT", BASE_URL . "/enroll.php");
define("BIOMETRIC_VERIFICATION_ENDPOINT", BASE_URL . "/biometricVerification.php");
define("VERIFY_ENDPOINT", BASE_URL . "/verify.php");
define("BIOMETRIC_SUCCESS_ENDPOINT", BASE_URL . "/biometric-success.php");

// Security Configuration
if (!defined("API_KEY")) {
    define("API_KEY", "HELLOWORLD"); // API key for protected endpoints
}
define("VERIFICATION_SECRET_KEY", "MY_SECRET_KEY"); // Secret key for verification

// Database Configuration (if not already defined)
if (!defined("DB_HOST")) {
    // These should be set in your database config file
    // define("DB_HOST", "localhost");
    // define("DB_NAME", "attendance_system");
    // define("DB_USER", "root");
    // define("DB_PASS", "");
}

// Application Settings
define("APP_NAME", "Attendance System");
define("APP_VERSION", "1.0.0");

// Timezone
date_default_timezone_set("Asia/Manila"); // Change to your timezone
