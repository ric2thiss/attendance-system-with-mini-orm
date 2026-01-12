<?php

require_once "./bootstrap.php";

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $data = json_decode(file_get_contents("php://input"), true);

    // Check if employee_id or resident_id is provided
    if (isset($data["employee_id"]) && !empty($data["employee_id"])) {
        // Employee enrollment
        $controller = new FingerprintsController();
        echo $controller->enroll($data);
    } elseif (isset($data["resident_id"]) && !empty($data["resident_id"])) {
        // Resident enrollment
        $controller = new ResidentFingerprintsController();
        echo $controller->enroll($data);
    } else {
        // Neither provided
        http_response_code(422);
        echo json_encode([
            "success" => false,
            "error"   => "Either employee_id or resident_id is required"
        ]);
    }
    exit;
} else {
    http_response_code(400);
    echo json_encode(["message" => "Bad request. Only POST method is allowed"]);
    exit;
}
