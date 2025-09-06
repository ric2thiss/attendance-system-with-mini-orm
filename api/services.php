<?php
require_once __DIR__ . "/../bootstrap.php";

header("Content-Type: application/json");

$resource = $_GET["resource"];
$method = $_SERVER["REQUEST_METHOD"];


switch ($resource) {
    case 'attendances':
        $attendanceController = new AttendanceController();

        if($method === "GET")
        {
            
            echo json_encode($attendanceController->index());
            // print_r($resource);
            return;
        }

        if($method === "POST")
        {
            $raw = file_get_contents("php://input");

            // Decode JSON to associative array
            $data = json_decode($raw, true);

            // Pass to controller
            $attendanceController->store($data);

        }
        break;
    case 'fingerprints':
        if($method === "GET")
        {

            echo "Fingerprints";
        }
        
        break;
    
    default:
        http_response_code(405); // Method Not Allowed
        echo "Method not supported.";
        break;
}
