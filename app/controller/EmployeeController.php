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
            return [
                "success" => false,
                "status"  => 400,
                "error"   => "Incomplete input data"
            ];
        }

        try {
            $createdEmployee = $this->employeeRepository->create($data);

            if ($createdEmployee) {
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

        } catch (Exception $err) {
            return [
                "success" => false,
                "status"  => 500,
                "error"   => "Something went wrong - " . $err->getMessage()
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
     * Get paginated employees with search
     *
     * @param int $page Current page number
     * @param int $perPage Records per page
     * @param string $searchQuery Search term (optional)
     * @return array
     */
    public function getPaginatedEmployees($page = 1, $perPage = 10, $searchQuery = '')
    {
        return $this->employeeRepository->getPaginated($page, $perPage, $searchQuery);
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
}