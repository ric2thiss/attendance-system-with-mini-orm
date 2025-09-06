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
            "fingerprints"=>$fingerprint
        ];
    }

    public function store($data)
    {
        $lastIdInserted= $this->attendance->create($data);

        try {
            echo "Last Id Inserted: ". $lastIdInserted;
        } catch (Exception $error) {
            echo "Error: " . $error->getMessage();
        }
    }
}