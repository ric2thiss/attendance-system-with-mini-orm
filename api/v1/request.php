<?php
require_once __DIR__ . "/../../bootstrap.php";

header("Content-Type: application/json");

/**
 * ---------------------------------------------------------------
 * Basic Setup
 * ---------------------------------------------------------------
 */
$method = $_SERVER["REQUEST_METHOD"];
$query  = $_GET["query"] ?? null;
$filter = $_GET["filter"] ?? null;

/**
 * Utility function for JSON responses
 */
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * Validate query parameter
 */
if (empty($query)) {
    jsonResponse(["error" => "Missing query"], 400);
}

/**
 * Utility function for API key validation
 */
function validateApiKey() {
    $headers = getallheaders();
    $apiKey  = $headers["x-api-key"] ?? null;

    if (!$apiKey) {
        jsonResponse(["error" => "Missing API key"], 400);
    }

    if (constant("API_KEY") !== $apiKey) {
        jsonResponse(["error" => "Invalid API key"], 401);
    }
}

/**
 * ---------------------------------------------------------------
 * ROUTING LOGIC
 * ---------------------------------------------------------------
 */
try {
    switch ($query) {

        /**
         * ---------------------------------------------------------------
         * EMPLOYEES API
         * ---------------------------------------------------------------
         */
        case "employees":
            validateApiKey();

            $employeesController = new EmployeeController();

            if ($filter === "all") {
                $result = $employeesController->getAllEmployees();
                jsonResponse($result);
            } 
            // elseif ($filter === "active") {
            //     $result = $employeesController->getActiveEmployees();
            //     jsonResponse($result);
            // } 
            // elseif ($filter === "inactive") {
            //     $result = $employeesController->getInactiveEmployees();
            //     jsonResponse($result);
            // } 
            else {
                jsonResponse(["error" => "Invalid or missing filter parameter"], 400);
            }

            break;


        /**
         * ---------------------------------------------------------------
         * ATTENDANCE API (example)
         * ---------------------------------------------------------------
         */
        case "attendance":
            // validateApiKey();

            $attendanceController = new AttendanceController();
            $from = $_GET["from"] ?? null;
            $to   = $_GET["to"] ?? null;

            if ($from && $to) {
                $result = $attendanceController->getAttendanceBetween($from, $to);
                jsonResponse($result);
            } else {
                jsonResponse(["error" => "Missing 'from' or 'to' parameters"], 400);
            }

            if(!$filter) {
                jsonResponse(["error"=>"Missing filter parameter"], 400);
            }

            if($filter === "all") {
                jsonResponse($attendanceController->index(), 200);
            }

            break;


        case "test":
            $employeesController = new EmployeeController();

            // Example test data
            $data = [
                "resident_id" => 1,
                "position" => "Manager",
                "department" => "HR"
            ];

            // Run the store method
            $employeesController->store($data);

            break;
        
        // case "residents":
        //     $residents = (new ResidentController())->getAllResident();

        //     print_r($residents);
        //     break;
        /**
         * ---------------------------------------------------------------
         * UNKNOWN QUERY
         * ---------------------------------------------------------------
         */
        default:
            jsonResponse(["error" => "Unknown query parameter"], 404);
    }

} catch (Exception $e) {
    jsonResponse([
        "error" => "Server error",
        "message" => $e->getMessage()
    ], 500);
}
/*  */
/*  */
