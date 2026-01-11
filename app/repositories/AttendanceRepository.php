<?php

require_once __DIR__ . '/BaseRepository.php';

class AttendanceRepository extends BaseRepository {
    protected function getModelClass(): string {
        return Attendance::class;
    }

    /**
     * Get last attendance record
     * 
     * @return object|array|null
     */
    public function getLast() {
        return Attendance::query()
            ->orderBy('id', 'DESC')
            ->first();
    }

    /**
     * Get attendance count for today
     * 
     * @return int
     */
    public function getCountToday(): int {
        $result = Attendance::query()
            ->select("COUNT(DISTINCT employee_id) as Total")
            ->whereRaw("DATE(created_at) = ?", [date("Y-m-d")])
            ->first();

        return is_object($result) ? (int) ($result->Total ?? 0) : (int) ($result['Total'] ?? 0);
    }

    /**
     * Check if employee already logged today for a specific window
     * 
     * @param string $employeeId
     * @param string $window
     * @return bool
     */
    public function existsTodayForWindow(string $employeeId, string $window): bool {
        $existing = Attendance::query()
            ->where([
                "employee_id" => $employeeId,
                "window"      => $window,
            ])
            ->whereRaw("DATE(created_at) = ?", [date("Y-m-d")])
            ->first();

        return $existing !== null;
    }

    /**
     * Get paginated attendances with search
     * 
     * @param int $page
     * @param int $perPage
     * @param string $searchQuery
     * @return array
     */
    public function getPaginated(int $page, int $perPage, string $searchQuery = ''): array {
        $offset = ($page - 1) * $perPage;

        $baseQuery = Attendance::query()->table("attendances AS a")
            ->select("a.id AS attendance_id, a.employee_id, CONCAT(r.first_name, ' ', r.last_name) AS full_name, a.timestamp AS attendance_time, a.window")
            ->join("employees AS e", "a.employee_id", "=", " e.employee_id")
            ->join("residents AS r", "e.resident_id", "=", "r.resident_id");

        $countQuery = Attendance::query()->table("attendances AS a")
            ->select("COUNT(*) as total")
            ->join("employees AS e", "a.employee_id", "=", " e.employee_id")
            ->join("residents AS r", "e.resident_id", "=", "r.resident_id");

        if (!empty($searchQuery)) {
            $baseQuery->whereRaw("(CONCAT(r.first_name, ' ', r.last_name) LIKE ? OR a.employee_id LIKE ?)", ["%{$searchQuery}%", "%{$searchQuery}%"]);
            $countQuery->whereRaw("(CONCAT(r.first_name, ' ', r.last_name) LIKE ? OR a.employee_id LIKE ?)", ["%{$searchQuery}%", "%{$searchQuery}%"]);
        }

        $totalCountQuery = $countQuery->first();
        $totalRecords = is_object($totalCountQuery) ? (int) $totalCountQuery->total : (int) ($totalCountQuery['total'] ?? 0);
        $totalPages = $totalRecords > 0 ? ceil($totalRecords / $perPage) : 1;

        $attendances = $baseQuery
            ->orderBy("a.timestamp", "DESC")
            ->limit($perPage)
            ->offset($offset)
            ->get();

        return [
            "attendances" => $attendances,
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
     * Get attendance records between dates
     * 
     * @param string $from
     * @param string $to
     * @return array
     */
    public function getBetween(string $from, string $to): array {
        return Attendance::query()
            ->whereRaw("DATE(created_at) BETWEEN ? AND ?", [$from, $to])
            ->get();
    }
}
