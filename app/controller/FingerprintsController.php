<?php

class FingerprintsController {
    protected $fingerprintsRepository;

    public function __construct()
    {
        $db = (new Database())->connect();
        $this->fingerprintsRepository = new FingerprintsRepository($db);
    }

    public function index() 
    {
        return $this->fingerprintsRepository->getAllLimited();
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

        if($this->fingerprintsRepository->existsByEmployeeId($data["employee_id"])) {
            http_response_code(409);
            echo json_encode(["message"=>"Employee already enrolled"]);
            return;
        }

        $this->fingerprintsRepository->create([
            "employee_id" => $data["employee_id"],
            "template"    => $data["template"]
        ]);

        http_response_code(201);
        echo json_encode(["message"=>"Fingerprint enrolled successfully"]);
        return;
    }
}