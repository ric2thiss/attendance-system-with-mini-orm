<?php

class AttendanceController {
    protected $attendance;
    // protected $fingerprints;

    public function __construct()
    {
        $db = (new Database())->connect();
        $this->attendance = new Attendance($db);
        // $this->fingerprints = new Fingerprints($db);
    }
    public function index()
    {
        $attendances = $this->attendance::all();
        // $fingerprint = $this->fingerprints::all();
        return [
            "attendances"=>$attendances,
            // "fingerprints"=>$fingerprint,
            "windows"=> $this->windows()
        ];
    }

    public function store($data)
    {
        // Validation
        if (!isset($data["employee_id"]) || empty($data["employee_id"])) {
            http_response_code(422);
            echo json_encode([
                "success" => false,
                "error"   => "Employee ID is required"
            ]);
            return;
        }

        if (!is_string($data["employee_id"])) {
            http_response_code(422);
            echo json_encode([
                "success" => false,
                "error"   => "Employee ID must be string"
            ]);
            return;
        }

        if (!isset($data["window"]) || empty($data["window"])) {
            http_response_code(422);
            echo json_encode([
                "success" => false,
                "error"   => "Employee ID is required"
            ]);
            return;
        }

        // Auto-fill timestamps
        $data["created_at"] = $this->now();
        $data["updated_at"] = $this->now();

        // Get valid windows
        $windows = $this->getWindows();
        $labels  = array_column($windows, 'label');

        if (!isset($data["window"]) || !in_array($data["window"], $labels)) {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "error"   => "Invalid or missing window label"
            ]);
            return;
        }

        // Check if already logged today
        $existing = $this->attendance->where([
            "employee_id" => $data["employee_id"],
            "window"      => $data["window"],
        ])->whereRaw("DATE(created_at) = ?", [date("Y-m-d")])
        ->first();

        if ($existing) {
            http_response_code(409); // Conflict
            echo json_encode([
                "success" => false,
                "error"   => "Already logged for this window today"
            ]);
            return;
        }

        // 5. Save attendance
        try {
            $saved = $this->attendance->create($data);

            http_response_code(201);
            echo json_encode([
                "success" => true,
                "data"    => $saved,
                "message" => "Attendance recorded successfully"
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "error"   => "Failed to save attendance",
                "details" => $e->getMessage()
            ]);
        }
    }



    public function windows()
    {
        return  ["windows"=> $this->getWindows()];
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