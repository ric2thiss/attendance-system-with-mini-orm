<?php

require_once __DIR__ . "/autoloader.php";

$pdo = (new Database())->connect();
Model::setConnection($pdo);

date_default_timezone_set("Asia/Manila");
