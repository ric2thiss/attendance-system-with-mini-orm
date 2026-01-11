<?php
require_once __DIR__ . "/../../bootstrap.php";
require_once __DIR__ . "/../../auth/helpers.php";

header("Content-Type: application/json");

// Require authentication for delete operations
requireAuth();

$method = $_SERVER["REQUEST_METHOD"];

/**
 * DELETE /api/residents/delete.php?id={id} - Delete a resident
 * POST /api/residents/delete.php?id={id} - Delete a resident (alternative method)
 */
if ($method === "DELETE" || $method === "POST") {
    $id = $_GET["id"] ?? null;

    if (empty($id)) {
        http_response_code(400);
        echo json_encode(["error" => "Resident ID is required"]);
        exit;
    }

    $residentsController = new ResidentController();
    $result = $residentsController->delete($id);
    
    $status = $result["success"] ? 200 : 400;
    http_response_code($status);
    echo json_encode($result);
    exit;
}

http_response_code(405);
echo json_encode(["error" => "Method not allowed"]);
