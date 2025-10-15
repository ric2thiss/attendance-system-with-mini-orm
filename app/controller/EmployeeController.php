<?php

class EmployeeController {


    public function store($data)
    {
        
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
            "employees.position"
        )
        ->join("residents", "employees.resident_id", "=", "residents.resident_id")
        ->get();
        $employeeCounts = $this->getAllEmployeeCount();

        return ["employees"=>$employees, "employeeCounts" => $employeeCounts];
    }
    
    private function getAllEmployeeCount() {
        return Employee::query()->table("employees")
        ->select("COUNT(*) as count")
        ->get();
    }
}