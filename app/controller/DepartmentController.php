<?php

class DepartmentController
{
    protected $departmentRepository;

    public function __construct() {
        $db = (new Database())->connect();
        $this->departmentRepository = new DepartmentRepository($db);
    }

    /**
     * Get all departments
     * @return array
     */
    public function getAll()
    {
        return $this->departmentRepository->findAll();
    }

    /**
     * Get department by ID
     * @param int $id
     * @return object|array|null
     */
    public function getById($id)
    {
        return $this->departmentRepository->findById($id);
    }

    /**
     * Create a new department
     * @param array $data
     * @return array
     */
    public function store($data)
    {
        // Validate required fields
        if (empty($data['department_name'])) {
            return [
                "success" => false,
                "message" => "Department name is required."
            ];
        }

        // Check for duplicate department_name
        $existing = $this->departmentRepository->findBy('department_name', trim($data['department_name']));
        if ($existing) {
            return [
                "success" => false,
                "message" => "Department already exists."
            ];
        }

        // Create the record
        $insertData = ['department_name' => trim($data['department_name'])];
        $id = $this->departmentRepository->create($insertData);

        if ($id) {
            return [
                "success" => true,
                "message" => "Department created successfully.",
                "id" => $id
            ];
        }

        return [
            "success" => false,
            "message" => "Failed to create department."
        ];
    }

    /**
     * Update a department
     * @param int $id
     * @param array $data
     * @return array
     */
    public function update($id, $data)
    {
        // Validate required fields
        if (empty($data['department_name'])) {
            return [
                "success" => false,
                "message" => "Department name is required."
            ];
        }

        // Check if record exists
        if (!$this->departmentRepository->exists($id)) {
            return [
                "success" => false,
                "message" => "Department not found."
            ];
        }

        // Check for duplicate department_name (excluding current record)
        $existing = $this->departmentRepository->findBy('department_name', trim($data['department_name']));
        if ($existing && (is_object($existing) ? $existing->department_id : $existing['department_id']) != $id) {
            return [
                "success" => false,
                "message" => "Department with this name already exists."
            ];
        }

        // Update the record
        $updateData = ['department_name' => trim($data['department_name'])];
        $success = $this->departmentRepository->update($id, $updateData);

        if ($success) {
            return [
                "success" => true,
                "message" => "Department updated successfully."
            ];
        }

        return [
            "success" => false,
            "message" => "Failed to update department."
        ];
    }

    /**
     * Delete a department
     * @param int $id
     * @return array
     */
    public function delete($id)
    {
        // Check if record exists
        if (!$this->departmentRepository->exists($id)) {
            return [
                "success" => false,
                "message" => "Department not found."
            ];
        }

        // Delete the record
        $success = $this->departmentRepository->delete($id);

        if ($success) {
            return [
                "success" => true,
                "message" => "Department deleted successfully."
            ];
        }

        return [
            "success" => false,
            "message" => "Failed to delete department."
        ];
    }

    /**
     * Legacy method for backward compatibility
     * @return array
     */
    public function getDepartmentLists()
    {
        return $this->getAll();
    }
}