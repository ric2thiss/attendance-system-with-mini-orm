<?php

class ResidentFingerprintsController {
    protected $residentFingerprintsRepository;

    public function __construct()
    {
        $db = (new Database())->connect();
        $this->residentFingerprintsRepository = new ResidentFingerprintsRepository($db);
    }

    public function index() 
    {
        return $this->residentFingerprintsRepository->getAllLimited();
    }
    
    public function enroll($data)
    {
        header('Content-Type: application/json'); 
        
        // Validation
        if (!isset($data["resident_id"]) || empty($data["resident_id"])) {
            http_response_code(422);
            echo json_encode([
                "success" => false,
                "error"   => "Resident ID is required"
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

        $residentId = intval($data["resident_id"]);
        
        if($this->residentFingerprintsRepository->existsByResidentId($residentId)) {
            http_response_code(409);
            echo json_encode(["message"=>"Resident already enrolled"]);
            return;
        }

        $this->residentFingerprintsRepository->create([
            "resident_id" => $residentId,
            "template"    => $data["template"]
        ]);

        http_response_code(201);
        echo json_encode(["message"=>"Fingerprint enrolled successfully"]);
        return;
    }
}
