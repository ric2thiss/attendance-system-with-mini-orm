<?php

class EmployeeController {


    public function store($data)
    {
        if (!$data) {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "error"   => "Empty metadata"
            ]);
            return;
        }

        if(!$data["employee_id"] || !$data["resident_id"] || !$data["position"] || !$data["hired_date"]) {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "error"   => "Incomplete input data"
            ]);
            return;
        }

        try {
            (new Employee())->create($data);
        } catch (Exception $err) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "error"   => "Something went wrong - $err"
            ]);
            return;
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
            "employees.position",
            "activity_types.activity_name",
        )
        ->join("residents", "employees.resident_id", "=", "residents.resident_id")
        ->leftJoin("employee_activity","employees.employee_id","=","employee_activity.employee_id")
        ->leftJoin("activity_types","employee_activity.activity_types_id","=","activity_types.activity_types_id")
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