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
            "employees.position",
            "employee_current_activity.current_activity as activity_name"
        )
        ->join("residents", "employees.resident_id", "=", "residents.resident_id")
        ->leftJoin("employee_activity","employees.employee_id","=","employee_activity.employee_id")
        ->leftJoin("employee_current_activity","employee_activity.employee_current_activity_id","=","employee_current_activity.employee_current_activity_id")
        ->get();
        $employeeCounts = $this->getAllEmployeeCount();

        return ["employees"=>$employees, "employeeCounts" => $employeeCounts];
    }
    
    private function getAllEmployeeCount()
    {
        return Employee::query()->table("employees")
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