<?php
require_once __DIR__ . "/../bootstrap.php";

header("Content-Type: application/json");

$resource = $_GET["resource"];
$method = $_SERVER["REQUEST_METHOD"];

if (!isset($_GET['resource'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing resource"]);
    exit;
}

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
    
    case 'employees':
       if ($method === "GET") {
            $headers = getallheaders();
            $apiKey = $headers["x-api-key"] ?? null;

            if (!$apiKey) {
                http_response_code(400);
                echo json_encode(["error" => "Missing API key"]);
                return;
            }

            if (constant("API_KEY") !== $apiKey) {
                http_response_code(401);
                echo json_encode(["error" => "Invalid API key"]);
                return;
            }

            $employeesController = new EmployeeController();
            echo json_encode($employeesController->getAllEmployees());
        }

        
        break;
    
    default:
        http_response_code(405); // Method Not Allowed
        echo "Method not supported.";
        break;
}
