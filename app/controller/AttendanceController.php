<?php

class AttendanceController {
    protected $attendanceRepository;
    protected $employeeRepository;
    protected $residentRepository;

    public function __construct()
    {
        $db = (new Database())->connect();
        $this->attendanceRepository = new AttendanceRepository($db);
        $this->employeeRepository = new EmployeeRepository($db);
        $this->residentRepository = new ResidentRepository($db);
    }
    // public function index()
    // {
    //     $attendances = $this->attendance::all();
    //     // $fingerprint = $this->fingerprints::all();
    //     $lastAttendance = Attendance::query()->orderBy('id', 'DESC')->first();
    //     $employee = Employee::query()->where('employee_id', $lastAttendance->employee_id)->first();
    //     $resident = Resident::query()->where("resident_id", $employee->resident_id)->get();

    //     // $employee = null;

    //     // if ($lastAttendance) {
    //     //     $employee = Employee::query()
    //     //         ->where("employee_id", $lastAttendance->employee_id)
    //     //         ->first();
    //     // }
    
    //     return [
    //         "attendances"=>$attendances,
    //         "attendancesTodayCount" => $this->getAttendanceCountToday(),
    //         // "fingerprints"=>$fingerprint,
    //         "lastAttendee" => $lastAttendance,
    //         "lastAttendeeEmployee" => $employee,
    //         "windows"=> $this->windows()
    //     ];
    // }

    public function index()
    {
        $attendances = $this->attendanceRepository->findAll();
        $lastAttendance = $this->attendanceRepository->getLast();

        $employee = null;
        $resident = null;

        if ($lastAttendance) {
            $employeeId = is_object($lastAttendance) ? $lastAttendance->employee_id : $lastAttendance['employee_id'];
            $employee = $this->employeeRepository->getWithPosition($employeeId);
            
            if ($employee) {
                $residentId = is_object($employee) ? $employee->resident_id : $employee['resident_id'];
                $resident = $this->residentRepository->findById($residentId);
            }
        }

        return [
            "attendances" => $attendances,
            "attendancesTodayCount" => $this->getAttendanceCountToday(),
            "lastAttendee" => $lastAttendance,
            "lastAttendeeEmployee" => $employee,
            "lastAttendeeResident" => $resident,
            // "windows" => $this->windows(),
        ];
    }

    public function getAttendanceBetween($from, $to)
    {
        return $this->attendanceRepository->getBetween($from, $to);
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
                "error"   => "No valid attendance window"
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
        if ($this->attendanceRepository->existsTodayForWindow($data["employee_id"], $data["window"])) {
            http_response_code(409); // Conflict
            echo json_encode([
                "success" => false,
                "error"   => "Already logged for this window today"
            ]);
            return;
        }

        // 5. Save attendance
        try {
            $saved = $this->attendanceRepository->create($data);

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
                'start' => '06:00:00',
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
                'end' => '18:30:00',
            ],
        ];
    }

    function now($format = "Y-m-d H:i:s", $timezone = "Asia/Manila")
    {
        $dt = new DateTime("now", new DateTimeZone($timezone));
        return $dt->format($format);
    }

    function getAttendanceCountToday()
    {
        return $this->attendanceRepository->getCountToday();
    }

    /**
     * Get paginated attendance records with search
     *
     * @param int $page Current page number
     * @param int $perPage Records per page
     * @param string $searchQuery Search term (optional)
     * @return array
     */
    public function getPaginatedAttendances($page = 1, $perPage = 10, $searchQuery = '')
    {
        return $this->attendanceRepository->getPaginated($page, $perPage, $searchQuery);
    }




}