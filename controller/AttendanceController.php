<?php

class AttendanceController {
    protected $attendance;
    protected $fingerprints;

    public function __construct()
    {
        $db = (new Database())->connect();
        $this->attendance = new Attendance($db);
        $this->fingerprints = new Fingerprints($db);
    }
    public function index()
    {
        $attendances = $this->attendance::all();
        $fingerprint = $this->fingerprints::all();
        return [
            "attendances"=>$attendances,
            "fingerprints"=>$fingerprint,
            "windows"=> $this->windows()
        ];
    }

    public function store($data)
    {
        // 1. Validation
        if (!isset($data["employee_id"]) || empty($data["employee_id"])) {
            http_response_code(422); // Unprocessable Entity
            echo json_encode([
                "success" => false,
                "error" => "Employee ID is required"
            ]);
            return;
        }

        // Optional: check type (integer vs string)
        if (!is_string($data["employee_id"])) {
            http_response_code(422);
            echo json_encode([
                "success" => false,
                "error" => "Employee ID must be String"
            ]);
            return;
        }

        // 2. Auto-fill timestamps
        $data["created_at"] = date("Y-m-d H:i:s");
        $data["updated_at"] = date("Y-m-d H:i:s");

        // 3. Get valid windows (labels)
        $windows = $this->windows();
        $labels  = array_column($windows, 'label'); // ["morning_in", "morning_out", ...]

        // 4. Validate window label
        if (!isset($data["window"]) || !in_array($data["window"], $labels)) {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "error" => "Invalid or missing window label"
            ]);
            return;
        }

        // 5. Save attendance (assuming Attendance::create exists)
        try {
            $saved = $this->attendance->create($data);

            http_response_code(201); // Created
            echo json_encode([
                "success" => true,
                "data" => $saved
            ]);
        } catch (Exception $e) {
            http_response_code(500); // Internal Server Error
            echo json_encode([
                "success" => false,
                "error"   => "Failed to save attendance",
                "details" => $e->getMessage()
            ]);
        }
    }


    public function windows()
    {
        return  $this->getWindows();
    }

     private function getWindows()
    {
        return [
            [
                'label' => 'morning_in',
                'start' => '08:00:00',
                'end' => '11:59:00',
            ],
            [
                'label' => 'morning_out',
                'start' => '12:00:00',
                'end' => '12:59:00',
            ],
            [
                'label' => 'afternoon_in',
                'start' => '13:00:00',
                'end' => '15:59:00',
            ],
            [
                'label' => 'afternoon_out',
                'start' => '16:00:00',
                'end' => '17:30:00',
            ],
        ];
    }

    function now($format = "Y-m-d H:i:s", $timezone = "Asia/Manila") {
        $dt = new DateTime("now", new DateTimeZone($timezone));
        return $dt->format($format);
    }

}