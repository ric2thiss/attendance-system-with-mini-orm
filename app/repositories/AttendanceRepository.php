<?php

require_once __DIR__ . '/BaseRepository.php';

class AttendanceRepository extends BaseRepository {
    protected function getModelClass(): string {
        return Attendance::class;
    }

    /**
     * Get last attendance record for today only
     * 
     * @return object|array|null
     */
    public function getLast() {
        $today = date("Y-m-d");
        $result = Attendance::query()
            ->table("attendances AS a")
            ->select("a.*, COALESCE(aw.label, a.window) AS window_label")
            ->leftJoin("attendance_windows AS aw", "LOWER(TRIM(a.window))", "=", "LOWER(TRIM(aw.label))")
            ->whereRaw("DATE(a.timestamp) = ? OR DATE(a.created_at) = ?", [$today, $today])
            ->orderBy('a.id', 'DESC')
            ->first();
        return $result;
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
     * Uses case-insensitive comparison for window
     * 
     * @param string $employeeId
     * @param string $window
     * @return bool
     */
    public function existsTodayForWindow(string $employeeId, string $window): bool {
        // Normalize window to lowercase for case-insensitive comparison
        $normalizedWindow = strtolower(trim($window));
        
        $existing = Attendance::query()
            ->where([
                "employee_id" => $employeeId,
            ])
            ->whereRaw("LOWER(TRIM(window)) = ?", [$normalizedWindow])
            ->whereRaw("DATE(created_at) = ?", [date("Y-m-d")])
            ->first();

        return $existing !== null;
    }

    /**
     * Get paginated attendances with search and date filtering
     * 
     * @param int $page
     * @param int $perPage
     * @param string $searchQuery
     * @param string|null $fromDate
     * @param string|null $toDate
     * @return array
     */
    public function getPaginated(int $page, int $perPage, string $searchQuery = '', ?string $fromDate = null, ?string $toDate = null): array {
        $offset = ($page - 1) * $perPage;

        $baseQuery = Attendance::query()->table("attendances AS a")
            ->select("a.id AS attendance_id, a.employee_id, CONCAT(r.first_name, ' ', r.last_name) AS full_name, a.timestamp AS attendance_time, a.window, COALESCE(aw.label, a.window) AS window_label")
            ->join("employees AS e", "a.employee_id", "=", " e.employee_id")
            ->join("residents AS r", "e.resident_id", "=", "r.resident_id")
            ->leftJoin("attendance_windows AS aw", "LOWER(TRIM(a.window))", "=", "LOWER(TRIM(aw.label))");

        $countQuery = Attendance::query()->table("attendances AS a")
            ->select("COUNT(*) as total")
            ->join("employees AS e", "a.employee_id", "=", " e.employee_id")
            ->join("residents AS r", "e.resident_id", "=", "r.resident_id");

        if (!empty($searchQuery)) {
            $baseQuery->whereRaw("(CONCAT(r.first_name, ' ', r.last_name) LIKE ? OR a.employee_id LIKE ?)", ["%{$searchQuery}%", "%{$searchQuery}%"]);
            $countQuery->whereRaw("(CONCAT(r.first_name, ' ', r.last_name) LIKE ? OR a.employee_id LIKE ?)", ["%{$searchQuery}%", "%{$searchQuery}%"]);
        }

        // Date filtering
        if (!empty($fromDate)) {
            $baseQuery->whereRaw("DATE(a.timestamp) >= ?", [$fromDate]);
            $countQuery->whereRaw("DATE(a.timestamp) >= ?", [$fromDate]);
        }
        if (!empty($toDate)) {
            $baseQuery->whereRaw("DATE(a.timestamp) <= ?", [$toDate]);
            $countQuery->whereRaw("DATE(a.timestamp) <= ?", [$toDate]);
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

    /**
     * Get corresponding attendance (in/out pair) for the same employee on the same date
     * Uses case-insensitive comparison for window matching
     * 
     * @param string $employeeId
     * @param string $currentWindow The current window (e.g., "morning_out")
     * @param string $date The date to search (Y-m-d format)
     * @return object|array|null
     */
    public function getCorrespondingAttendance(string $employeeId, string $currentWindow, string $date) {
        // Normalize current window to lowercase for comparison
        $normalizedCurrentWindow = strtolower(trim($currentWindow));
        
        // Determine the corresponding window (case-insensitive)
        $correspondingWindow = null;
        
        if ($normalizedCurrentWindow === 'morning_out') {
            $correspondingWindow = 'morning_in';
        } elseif ($normalizedCurrentWindow === 'morning_in') {
            $correspondingWindow = 'morning_out';
        } elseif ($normalizedCurrentWindow === 'afternoon_out') {
            $correspondingWindow = 'afternoon_in';
        } elseif ($normalizedCurrentWindow === 'afternoon_in') {
            $correspondingWindow = 'afternoon_out';
        }
        
        if (!$correspondingWindow) {
            return null;
        }
        
        // Get the corresponding attendance using case-insensitive window comparison
        $result = Attendance::query()
            ->table("attendances AS a")
            ->select("a.*, COALESCE(aw.label, a.window) AS window_label")
            ->leftJoin("attendance_windows AS aw", "LOWER(TRIM(a.window))", "=", "LOWER(TRIM(aw.label))")
            ->where([
                "a.employee_id" => $employeeId,
            ])
            ->whereRaw("LOWER(TRIM(a.window)) = ?", [strtolower($correspondingWindow)])
            ->whereRaw("DATE(a.timestamp) = ? OR DATE(a.created_at) = ?", [$date, $date])
            ->orderBy('a.id', 'DESC')
            ->first();
            
        return $result;
    }
}
