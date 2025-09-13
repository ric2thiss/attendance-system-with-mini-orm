<?php

class FingerprintsController {
    protected $fingerprints;

    public function __construct()
    {
        $db = (new Database())->connect();
        $this->fingerprints = new Fingerprints($db);
    }

    public function index() 
    {
        return $this->fingerprints->all(["employee_id", "template"]);
    }
    
    public function enroll($data)
    {
        header('Content-Type: application/json'); 
        
        // Validation
        if (!isset($data["employee_id"]) || empty($data["employee_id"])) {
            http_response_code(422);
            echo json_encode([
                "success" => false,
                "error"   => "Employee ID is required"
            ]);
            return;
        }
        
        if (!isset($data["template"]) || empty($data["template"])) {
            http_response_code(422);
            echo json_encode([
                "success" => false,
                "error"   => "Template data is required"
            ]);
            return;
        }

        $exists = $this->fingerprints->where("employee_id", $data["employee_id"])->first();

        if($exists) {
            http_response_code(409);
            echo json_encode(["message"=>"Employee already enrolled"]);
            return;
        }

        Fingerprints::create([
            "employee_id" => $data["employee_id"],
            "template"    => $data["template"]
        ]);

        http_response_code(201);
        echo json_encode(["message"=>"Fingerprint enrolled successfully"]);
        return;
    }
}