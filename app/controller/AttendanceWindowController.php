<?php

class AttendanceWindowController
{
    protected $windowRepository;

    public function __construct() {
        $db = (new Database())->connect();
        $this->windowRepository = new AttendanceWindowRepository($db);
    }

    /**
     * Get all attendance windows
     * @return array
     */
    public function getAll()
    {
        return $this->windowRepository->findAll();
    }

    /**
     * Get attendance window by ID
     * @param int $id
     * @return object|array|null
     */
    public function getById($id)
    {
        return $this->windowRepository->findById($id);
    }

    /**
     * Create a new attendance window
     * @param array $data
     * @return array
     */
    public function store($data)
    {
        // Validate required fields
        if (empty($data['label'])) {
            return [
                "success" => false,
                "message" => "Window label is required."
            ];
        }

        if (empty($data['start_time'])) {
            return [
                "success" => false,
                "message" => "Start time is required."
            ];
        }

        if (empty($data['end_time'])) {
            return [
                "success" => false,
                "message" => "End time is required."
            ];
        }

        // Validate time format
        if (!$this->validateTime($data['start_time']) || !$this->validateTime($data['end_time'])) {
            return [
                "success" => false,
                "message" => "Invalid time format. Please use HH:MM:SS format."
            ];
        }

        // Check if start_time is before end_time
        if ($data['start_time'] >= $data['end_time']) {
            return [
                "success" => false,
                "message" => "Start time must be before end time."
            ];
        }

        // Normalize label to lowercase for consistency
        $normalizedLabel = strtolower(trim($data['label']));
        
        // Check for duplicate label (case-insensitive)
        $existing = $this->windowRepository->findByLabel($normalizedLabel);
        if ($existing) {
            return [
                "success" => false,
                "message" => "Window label already exists."
            ];
        }

        // Create the record with normalized label
        $insertData = [
            'label' => $normalizedLabel,
            'start_time' => trim($data['start_time']),
            'end_time' => trim($data['end_time'])
        ];
        $id = $this->windowRepository->create($insertData);

        if ($id) {
            return [
                "success" => true,
                "message" => "Attendance window created successfully.",
                "id" => $id
            ];
        }

        return [
            "success" => false,
            "message" => "Failed to create attendance window."
        ];
    }

    /**
     * Update an attendance window
     * @param int $id
     * @param array $data
     * @return array
     */
    public function update($id, $data)
    {
        // Validate required fields
        if (empty($data['label'])) {
            return [
                "success" => false,
                "message" => "Window label is required."
            ];
        }

        if (empty($data['start_time'])) {
            return [
                "success" => false,
                "message" => "Start time is required."
            ];
        }

        if (empty($data['end_time'])) {
            return [
                "success" => false,
                "message" => "End time is required."
            ];
        }

        // Validate time format
        if (!$this->validateTime($data['start_time']) || !$this->validateTime($data['end_time'])) {
            return [
                "success" => false,
                "message" => "Invalid time format. Please use HH:MM:SS format."
            ];
        }

        // Check if start_time is before end_time
        if ($data['start_time'] >= $data['end_time']) {
            return [
                "success" => false,
                "message" => "Start time must be before end time."
            ];
        }

        // Check if record exists
        if (!$this->windowRepository->exists($id)) {
            return [
                "success" => false,
                "message" => "Attendance window not found."
            ];
        }

        // Normalize label to lowercase for consistency
        $normalizedLabel = strtolower(trim($data['label']));
        
        // Check for duplicate label (excluding current record, case-insensitive)
        $existing = $this->windowRepository->findByLabel($normalizedLabel);
        if ($existing) {
            $existingId = is_object($existing) ? $existing->window_id : $existing['window_id'];
            if ($existingId != $id) {
                return [
                    "success" => false,
                    "message" => "Window label already exists."
                ];
            }
        }

        // Update the record with normalized label
        $updateData = [
            'label' => $normalizedLabel,
            'start_time' => trim($data['start_time']),
            'end_time' => trim($data['end_time'])
        ];
        $success = $this->windowRepository->update($id, $updateData);

        if ($success) {
            return [
                "success" => true,
                "message" => "Attendance window updated successfully."
            ];
        }

        return [
            "success" => false,
            "message" => "Failed to update attendance window."
        ];
    }

    /**
     * Delete an attendance window
     * @param int $id
     * @return array
     */
    public function delete($id)
    {
        // Check if record exists
        if (!$this->windowRepository->exists($id)) {
            return [
                "success" => false,
                "message" => "Attendance window not found."
            ];
        }

        // Delete the record
        $success = $this->windowRepository->delete($id);

        if ($success) {
            return [
                "success" => true,
                "message" => "Attendance window deleted successfully."
            ];
        }

        return [
            "success" => false,
            "message" => "Failed to delete attendance window."
        ];
    }

    /**
     * Validate time format (HH:MM:SS or HH:MM)
     * @param string $time
     * @return bool
     */
    private function validateTime($time)
    {
        // Accept HH:MM:SS or HH:MM format
        return preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/', $time) === 1;
    }
}
