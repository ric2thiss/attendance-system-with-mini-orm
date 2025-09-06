<?php

require_once __DIR__ ."/config.php";

spl_autoload_register(function($class){
    $paths = [
        BASE_PATH . "functions/",
        BASE_PATH . "api/model/",
        BASE_PATH . "database/",
        BASE_PATH . "functions/helpers",
        BASE_PATH . "models/",
        BASE_PATH . "query/",
        BASE_PATH . "controller/",

    ];

    foreach($paths as $path) {
        $file = $path . $class . ".php";
        if(file_exists($file)) {
            require_once $file;
            return;
        }
    } 
});