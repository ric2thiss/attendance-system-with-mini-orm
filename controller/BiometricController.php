<?php

class BiometricController 
{
    public function store(array $data)
    {
        header('Content-Type: application/json');

        // Check if $data is an array and not empty
        if(!is_array($data) || empty($data)){
            http_response_code(422);
            echo json_encode(["success" => false, "message" => "Missing parameter or wrong data type is given"]);
            return;
        }

        // Check required fields
        $required = ["employee_id", "status", "timestamp"];
        foreach ($required as $field) {
            if(!isset($data[$field])) {
                http_response_code(422);
                echo json_encode(["success" => false, "message" => "Missing required field: $field"]);
                return;
            }
        }

        $employee = (new Fingerprints())->where('employee_id', $data["employee_id"])->first();

        if(!$employee) {
            http_response_code(404);
            echo json_encode([
                "success" => false,
                "message" => "Employee not found"
            ]);
            return;
        }

        // $log = VerificationLog::create([
        //     "employee_id" => $data["employee_id"],
        //     "status"      => $data["status"],
        //     "device_id"   => $data["device_id"] ?? null,
        //     "verified_at" => $data["timestamp"]
        // ]);

        echo json_encode([
            "success"  => true,
            "message"  => "Fingerprint verified successfully",
            "employee" => $employee,
            // "log"      => $log
        ]);
    }
}
