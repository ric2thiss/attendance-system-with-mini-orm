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

        if ($method === "POST") 
        {
            $raw  = file_get_contents("php://input");
            $data = json_decode($raw, true);

            // Fallback: if not JSON, use $_POST
            if (is_null($data)) {
                $data = $_POST;
            }

            $attendanceController->store($data);
        }
        break;
    
    case 'templates':
        $fingerprintsController = new FingerprintsController();
        if($method === "GET")
        {
            echo json_encode($fingerprintsController->index());
        }
        
        break;
    
    case 'attendance-windows':
        if($method === "GET")
        {
            $attendanceController = new AttendanceController();
            echo json_encode($attendanceController->windows());
        }
        
        break;
    
    case 'check':
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
