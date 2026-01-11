<?php

require_once __DIR__ . '/BaseRepository.php';

class EmployeeRepository extends BaseRepository {
    protected function getModelClass(): string {
        return Employee::class;
    }

    /**
     * Get all employees with related data
     * 
     * @return array
     */
    public function getAllWithRelations(): array {
        return Employee::query()
            ->select(
                "residents.resident_id",
                "residents.first_name",
                "residents.middle_name",
                "residents.last_name",
                "residents.suffix",
                "residents.gender",
                "employees.employee_id",
                "position.position_name",
                "activity_types.activity_name"
            )
            ->join("residents", "employees.resident_id", "=", "residents.resident_id")
            ->leftJoin("employee_activity", "employees.employee_id", "=", "employee_activity.employee_id")
            ->leftJoin("activity_types", "employee_activity.activity_types_id", "=", "activity_types.activity_types_id")
            ->leftJoin("position", "employees.position_id", "=", "position.position_id")
            ->get();
    }

    /**
     * Get paginated employees with search
     * 
     * @param int $page
     * @param int $perPage
     * @param string $searchQuery
     * @return array
     */
    public function getPaginated(int $page, int $perPage, string $searchQuery = ''): array {
        $offset = ($page - 1) * $perPage;

        $baseQuery = Employee::query()
            ->select(
                "residents.resident_id",
                "residents.first_name",
                "residents.middle_name",
                "residents.last_name",
                "residents.suffix",
                "residents.gender",
                "employees.employee_id",
                "position.position_name",
                "activity_types.activity_name"
            )
            ->join("residents", "employees.resident_id", "=", "residents.resident_id")
            ->leftJoin("employee_activity", "employees.employee_id", "=", "employee_activity.employee_id")
            ->leftJoin("activity_types", "employee_activity.activity_types_id", "=", "activity_types.activity_types_id")
            ->leftJoin("position", "employees.position_id", "=", "position.position_id");

        $countQuery = Employee::query()
            ->select("COUNT(DISTINCT employees.employee_id) as total")
            ->join("residents", "employees.resident_id", "=", "residents.resident_id")
            ->leftJoin("employee_activity", "employees.employee_id", "=", "employee_activity.employee_id")
            ->leftJoin("activity_types", "employee_activity.activity_types_id", "=", "activity_types.activity_types_id")
            ->leftJoin("position", "employees.position_id", "=", "position.position_id");

        if (!empty($searchQuery)) {
            $searchCondition = "(CONCAT(residents.first_name, ' ', residents.last_name) LIKE ? OR employees.employee_id LIKE ? OR position.position_name LIKE ?)";
            $searchParams = ["%{$searchQuery}%", "%{$searchQuery}%", "%{$searchQuery}%"];
            
            $baseQuery->whereRaw($searchCondition, $searchParams);
            $countQuery->whereRaw($searchCondition, $searchParams);
        }

        $totalCountQuery = $countQuery->first();
        $totalRecords = is_object($totalCountQuery) ? (int) $totalCountQuery->total : (int) ($totalCountQuery['total'] ?? 0);
        $totalPages = $totalRecords > 0 ? ceil($totalRecords / $perPage) : 1;

        $employees = $baseQuery
            ->limit($perPage)
            ->offset($offset)
            ->get();

        return [
            "employees" => $employees,
            "pagination" => [
                "currentPage" => $page,
                "totalPages" => $totalPages,
                "totalRecords" => $totalRecords,
                "perPage" => $perPage,
                "startRecord" => $offset + 1,
                "endRecord" => min($offset + $perPage, $totalRecords),
            ],
            "searchQuery" => $searchQuery
        ];
    }

    /**
     * Get employee with position
     * 
     * @param string $employeeId
     * @return object|array|null
     */
    public function getWithPosition(string $employeeId) {
        return Employee::query()
            ->select(
                "employees.employee_id",
                "employees.resident_id",
                "employees.position_id",
                "employees.hired_date",
                "position.position_name"
            )
            ->leftJoin("position", "employees.position_id", "=", "position.position_id")
            ->where('employees.employee_id', $employeeId)
            ->first();
    }

    /**
     * Get last employee ID
     * 
     * @return string|null
     */
    public function getLastEmployeeId(): ?string {
        $lastEmployee = Employee::query()
            ->select("employee_id")
            ->orderBy("created_at", "DESC")
            ->first();

        return is_object($lastEmployee) 
            ? ($lastEmployee->employee_id ?? null)
            : ($lastEmployee['employee_id'] ?? null);
    }

    /**
     * Get employee count
     * 
     * @return int
     */
    public function getEmployeeCount(): int {
        $result = Employee::query()
            ->select("COUNT(*) as count")
            ->first();
        
        return is_object($result) ? (int) ($result->count ?? 0) : (int) ($result['count'] ?? 0);
    }
}
