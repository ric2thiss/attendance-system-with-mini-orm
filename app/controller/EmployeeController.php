<?php

class EmployeeController {
    protected $employeeRepository;

    public function __construct() {
        $db = (new Database())->connect();
        $this->employeeRepository = new EmployeeRepository($db);
    }

    public function store($data)
    {
        // Validate input
        if (!$data) {
            return [
                "success" => false,
                "status"  => 400,
                "error"   => "Invalid or missing data"
            ];
        }

        if (
            empty($data["employee_id"]) ||
            empty($data["resident_id"]) ||
            empty($data["position_id"]) ||
            empty($data["hired_date"])
        ) {
            // department_id is optional
            return [
                "success" => false,
                "status"  => 400,
                "error"   => "Incomplete input data"
            ];
        }

        try {
            $createdEmployee = $this->employeeRepository->create($data);

            if ($createdEmployee) {
                // Copy resident fingerprint template to employee fingerprints
                try {
                    $db = (new Database())->connect();
                    $residentFingerprintsRepository = new ResidentFingerprintsRepository($db);
                    $fingerprintsRepository = new FingerprintsRepository($db);
                    
                    $residentId = intval($data["resident_id"]);
                    $residentFingerprint = $residentFingerprintsRepository->findByResidentId($residentId);
                    
                    if ($residentFingerprint) {
                        // Get template - handle both object and array
                        $template = is_object($residentFingerprint) 
                            ? ($residentFingerprint->template ?? null)
                            : ($residentFingerprint['template'] ?? null);
                        
                        if (!empty($template)) {
                            // Check if employee fingerprint already exists
                            if (!$fingerprintsRepository->existsByEmployeeId($data["employee_id"])) {
                                $fingerprintsRepository->create([
                                    "employee_id" => $data["employee_id"],
                                    "template" => $template
                                ]);
                            }
                        }
                    }
                } catch (PDOException $fingerprintErr) {
                    // Log the fingerprint error but don't fail the employee creation
                    // The employee was already created successfully
                    error_log("Failed to copy fingerprint template for employee {$data['employee_id']}: " . $fingerprintErr->getMessage());
                } catch (Exception $fingerprintErr) {
                    // Log other errors but don't fail the employee creation
                    error_log("Unexpected error copying fingerprint template for employee {$data['employee_id']}: " . $fingerprintErr->getMessage());
                }
                
                return [
                    "success" => true,
                    "status"  => 201,
                    "message" => "Employee successfully created.",
                ];
            }

            return [
                "success" => false,
                "status"  => 500,
                "error"   => "Failed to create employee."
            ];

        } catch (PDOException $err) {
            return [
                "success" => false,
                "status"  => 400,
                "error"   => $this->getUserFriendlyErrorMessage($err)
            ];
        } catch (Exception $err) {
            return [
                "success" => false,
                "status"  => 500,
                "error"   => "An unexpected error occurred. Please try again or contact support if the problem persists."
            ];
        }
    }

    public function getAllEmployees()
    {
        $employees = $this->employeeRepository->getAllWithRelations();
        $employeeCounts = $this->employeeRepository->getEmployeeCount();

        return ["employees"=>$employees, "employeeCounts" => [["count" => $employeeCounts]]];
    }

    /**
     * Get paginated employees with search and filters
     *
     * @param int $page Current page number
     * @param int $perPage Records per page
     * @param string $searchQuery Search term (optional)
     * @param array $filters Optional filters: department_id, position_id
     * @return array
     */
    public function getPaginatedEmployees($page = 1, $perPage = 10, $searchQuery = '', $filters = [])
    {
        return $this->employeeRepository->getPaginated($page, $perPage, $searchQuery, $filters);
    }

    /**
     * Get the last created employee ID
     * 
     * @return string|null
     */
    public function getLastEmployeeId()
    {
        return $this->employeeRepository->getLastEmployeeId();
    }

    /**
     * Get employee by ID
     * 
     * @param string $employeeId
     * @return array|null
     */
    public function getEmployeeById(string $employeeId): ?array
    {
        return $this->employeeRepository->getEmployeeById($employeeId);
    }

    /**
     * Update employee
     * 
     * @param string $employeeId
     * @param array $data
     * @return array
     */
    public function update(string $employeeId, array $data)
    {
        // Validate input
        if (empty($data["position_id"]) || empty($data["hired_date"])) {
            return [
                "success" => false,
                "status"  => 400,
                "error"   => "Position and hired date are required"
            ];
        }

        try {
            // Get existing employee
            $existingEmployee = $this->employeeRepository->getEmployeeById($employeeId);
            if (!$existingEmployee) {
                return [
                    "success" => false,
                    "status"  => 404,
                    "error"   => "Employee not found"
                ];
            }

            // Update employee data
            $updateData = [
                "position_id" => intval($data["position_id"]),
                "hired_date" => trim($data["hired_date"])
            ];

            if (!empty($data["department_id"])) {
                $updateData["department_id"] = intval($data["department_id"]);
            } else {
                $updateData["department_id"] = null;
            }

            $updated = Employee::query()
                ->where('employee_id', $employeeId)
                ->update($updateData);

            if ($updated) {
                return [
                    "success" => true,
                    "status"  => 200,
                    "message" => "Employee successfully updated."
                ];
            }

            return [
                "success" => false,
                "status"  => 500,
                "error"   => "Failed to update employee."
            ];

        } catch (PDOException $err) {
            return [
                "success" => false,
                "status"  => 400,
                "error"   => $this->getUserFriendlyErrorMessage($err)
            ];
        } catch (Exception $err) {
            return [
                "success" => false,
                "status"  => 500,
                "error"   => "An unexpected error occurred. Please try again or contact support if the problem persists."
            ];
        }
    }

    /**
     * Delete employee
     * 
     * @param string $employeeId
     * @return array
     */
    public function delete(string $employeeId)
    {
        try {
            // Check if employee exists
            $employee = $this->employeeRepository->getEmployeeById($employeeId);
            if (!$employee) {
                return [
                    "success" => false,
                    "status"  => 404,
                    "error"   => "Employee not found"
                ];
            }

            // Delete employee (cascade should handle related records)
            $deleted = Employee::query()
                ->where('employee_id', $employeeId)
                ->delete();

            if ($deleted) {
                return [
                    "success" => true,
                    "status"  => 200,
                    "message" => "Employee successfully deleted."
                ];
            }

            return [
                "success" => false,
                "status"  => 500,
                "error"   => "Failed to delete employee."
            ];

        } catch (PDOException $err) {
            return [
                "success" => false,
                "status"  => 400,
                "error"   => $this->getUserFriendlyErrorMessage($err)
            ];
        } catch (Exception $err) {
            return [
                "success" => false,
                "status"  => 500,
                "error"   => "An unexpected error occurred. Please try again or contact support if the problem persists."
            ];
        }
    }

    /**
     * Convert database errors to user-friendly messages
     * 
     * @param PDOException $exception
     * @return string
     */
    private function getUserFriendlyErrorMessage(PDOException $exception): string
    {
        $errorCode = $exception->getCode();
        $errorMessage = $exception->getMessage();
        
        // Handle duplicate entry errors (1062)
        if ($errorCode == 23000 || strpos($errorMessage, '1062') !== false) {
            // Extract the duplicate value if possible
            if (preg_match("/Duplicate entry '([^']+)' for key/", $errorMessage, $matches)) {
                $duplicateValue = $matches[1];
                
                // Check if it's an employee_id duplicate
                if (preg_match("/for key 'PRIMARY'/", $errorMessage) || 
                    preg_match("/for key 'employee_id'/", $errorMessage)) {
                    return "Employee ID '{$duplicateValue}' is already in use. Please use a different Employee ID.";
                }
                
                // Generic duplicate entry message
                return "The information you entered already exists in the system. Please check your input and try again.";
            }
            
            return "This record already exists. Please check if the employee has already been registered.";
        }
        
        // Handle foreign key constraint violations (1452)
        if (strpos($errorMessage, '1452') !== false) {
            if (strpos($errorMessage, 'resident_id') !== false) {
                return "The selected resident is invalid or does not exist. Please select a valid resident.";
            }
            if (strpos($errorMessage, 'position_id') !== false) {
                return "The selected position is invalid. Please select a valid position.";
            }
            if (strpos($errorMessage, 'department_id') !== false) {
                return "The selected department is invalid. Please select a valid department.";
            }
            return "The information you entered references data that does not exist. Please check your selections and try again.";
        }
        
        // Handle other constraint violations
        if ($errorCode == 23000) {
            return "The information you entered violates a data constraint. Please check your input and try again.";
        }
        
        // Generic database error
        return "Unable to save the employee information. Please check all fields and try again. If the problem persists, contact support.";
    }
}