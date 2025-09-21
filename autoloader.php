<?php

require_once __DIR__ ."/config.php";

spl_autoload_register(function($class){
    $paths = [
        BASE_PATH . "app/functions/",
        BASE_PATH . "app/database/",
        BASE_PATH . "app/models/",
        BASE_PATH . "app/query/",
        BASE_PATH . "app/controller/",
        BASE_PATH . "functions/helpers",

    ];

    foreach($paths as $path) {
        $file = $path . $class . ".php";
        if(file_exists($file)) {
            require_once $file;
            return;
        }
    } 
});