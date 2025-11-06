<?php

class EmployeeController {


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
            $createdEmployee = Employee::create($data);

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
        $employees = Employee::query()
        ->select(
            "residents.resident_id",
            "residents.first_name",
            "residents.middle_name",
            "residents.last_name",
            "residents.suffix",
            "residents.gender",
            "employees.employee_id",
            "position.position_name",
            "activity_types.activity_name",
        )
        ->join("residents", "employees.resident_id", "=", "residents.resident_id")
        ->leftJoin("employee_activity","employees.employee_id","=","employee_activity.employee_id")
        ->leftJoin("activity_types","employee_activity.activity_types_id","=","activity_types.activity_types_id")
        ->leftJoin("position", "employees.position_id", "=", "position.position_id")
        ->get();
        $employeeCounts = $this->getAllEmployeeCount();

        return ["employees"=>$employees, "employeeCounts" => $employeeCounts];
    }
    
    private function getAllEmployeeCount()
    {
        return Employee::query()
        ->select("COUNT(*) as count")
        ->get();
    }

    // public function getEmployeeCurrentActivity()
    // {
    //     $employees = Employee::query()
    //     ->join("employee_activity", "")
    //     return;
    // }

}