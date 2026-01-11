<?php

require_once __DIR__ . "/autoloader.php";
require_once __DIR__ . "/config/app.config.php"; // Load application configuration

$pdo = (new Database())->connect();
Model::setConnection($pdo);

date_default_timezone_set("Asia/Manila");

// API_KEY is now defined in app.config.php, but keep for backwards compatibility
if (!defined("API_KEY")) {
    define("API_KEY", "HELLOWORLD");
}
