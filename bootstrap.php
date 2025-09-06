<?php

require_once __DIR__ . "/autoloader.php";

$pdo = (new Database())->connect();
Model::setConnection($pdo);
