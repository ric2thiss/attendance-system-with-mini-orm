<?php

require_once __DIR__ . "/autoloader.php";
require_once __DIR__ . "/config/app.config.php"; // Load application configuration

// Polyfills for PHP 7 compatibility (string helper functions)
if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle): bool
    {
        if ($needle === '') {
            return true;
        }
        return mb_strpos($haystack, $needle) !== false;
    }
}

if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle): bool
    {
        if ($needle === '') {
            return true;
        }
        return mb_substr($haystack, 0, mb_strlen($needle)) === $needle;
    }
}

$pdo = (new Database())->connect();
Model::setConnection($pdo);

date_default_timezone_set("Asia/Manila");

// API_KEY is now defined in app.config.php, but keep for backwards compatibility
if (!defined("API_KEY")) {
    define("API_KEY", "HELLOWORLD");
}
