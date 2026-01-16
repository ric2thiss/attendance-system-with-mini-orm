<?php
/**
 * Reports API Endpoint
 * 
 * Returns report data based on type and date range
 * All calculations are based strictly on actual attendance records from the attendances table
 */

header("Content-Type: application/json");

require_once __DIR__ . "/../../bootstrap.php";
require_once __DIR__ . "/../../auth/helpers.php";

// Require authentication for reports
if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$type = $_GET['type'] ?? 'attendance-department';
$fromDate = $_GET['from'] ?? date('Y-m-01');
$toDate = $_GET['to'] ?? date('Y-m-t');

/**
 * Calculate hours worked from attendance pairs
 * Pairs morning_in with morning_out and afternoon_in with afternoon_out
 * Returns total hours in decimal format
 * Only counts complete pairs (both in and out must exist)
 */
function calculateHoursFromAttendances($attendances) {
    $totalHours = 0.0;
    
    if (empty($attendances)) {
        return 0.0;
    }
    
    // Group by employee and date
    $grouped = [];
    foreach ($attendances as $att) {
        // Get timestamp - prefer timestamp field, fallback to created_at
        $timeValue = $att['timestamp'] ?? $att['created_at'] ?? null;
        if (!$timeValue) {
            continue; // Skip records without valid timestamp
        }
        
        $empId = $att['employee_id'] ?? null;
        if (!$empId) {
            continue; // Skip records without employee_id
        }
        
        // Parse date and timestamp
        $dateTime = strtotime($timeValue);
        if ($dateTime === false) {
            continue; // Skip invalid timestamps
        }
        
        $date = date('Y-m-d', $dateTime);
        $window = strtolower(trim($att['window'] ?? ''));
        if (empty($window)) {
            continue; // Skip records without window
        }
        
        if (!isset($grouped[$empId])) {
            $grouped[$empId] = [];
        }
        if (!isset($grouped[$empId][$date])) {
            $grouped[$empId][$date] = [];
        }
        
        // Store the earliest timestamp for each window (in case of duplicates)
        if (!isset($grouped[$empId][$date][$window]) || $dateTime < $grouped[$empId][$date][$window]) {
            $grouped[$empId][$date][$window] = $dateTime;
        }
    }
    
    // Calculate hours for each employee-date combination
    foreach ($grouped as $empId => $dates) {
        foreach ($dates as $date => $windows) {
            // Morning shift: morning_in to morning_out
            if (isset($windows['morning_in']) && isset($windows['morning_out'])) {
                $morningHours = ($windows['morning_out'] - $windows['morning_in']) / 3600; // Convert seconds to hours
                // Only count positive hours (out must be after in)
                if ($morningHours > 0 && $morningHours < 24) { // Sanity check: less than 24 hours
                    $totalHours += $morningHours;
                }
            }
            
            // Afternoon shift: afternoon_in to afternoon_out
            if (isset($windows['afternoon_in']) && isset($windows['afternoon_out'])) {
                $afternoonHours = ($windows['afternoon_out'] - $windows['afternoon_in']) / 3600; // Convert seconds to hours
                // Only count positive hours (out must be after in)
                if ($afternoonHours > 0 && $afternoonHours < 24) { // Sanity check: less than 24 hours
                    $totalHours += $afternoonHours;
                }
            }
        }
    }
    
    return round($totalHours, 2);
}

try {
    $db = (new Database())->connect();
    
    switch ($type) {
        case 'attendance-department':
        case 'attendance-count':
            // Step 1: Get all attendance records with department info for the date range
            $query = "
                SELECT 
                    a.id,
                    a.employee_id,
                    a.timestamp,
                    a.created_at,
                    a.window,
                    COALESCE(d.department_id, 0) AS department_id,
                    COALESCE(d.department_name, 'Unassigned') AS department
                FROM attendances a
                LEFT JOIN employees e ON a.employee_id = e.employee_id
                LEFT JOIN departments d ON e.department_id = d.department_id
                WHERE DATE(COALESCE(a.timestamp, a.created_at)) BETWEEN ? AND ?
                ORDER BY a.employee_id, DATE(COALESCE(a.timestamp, a.created_at)), a.window
            ";
            
            $stmt = $db->prepare($query);
            $stmt->execute([$fromDate, $toDate]);
            $allAttendances = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Step 2: Group by department and calculate metrics
            $departmentData = [];
            
            foreach ($allAttendances as $att) {
                $deptId = $att['department_id'] ?? 0;
                $deptName = $att['department'] ?? 'Unassigned';
                
                if (!isset($departmentData[$deptId])) {
                    $departmentData[$deptId] = [
                        'department' => $deptName,
                        'employees' => [],
                        'attendance_records' => [],
                        'total_attendance' => 0
                    ];
                }
                
                // Track unique employees
                $empId = $att['employee_id'];
                if (!in_array($empId, $departmentData[$deptId]['employees'])) {
                    $departmentData[$deptId]['employees'][] = $empId;
                }
                
                // Collect attendance records for hour calculation
                $departmentData[$deptId]['attendance_records'][] = $att;
                $departmentData[$deptId]['total_attendance']++;
            }
            
            // Step 3: Calculate hours per employee, then aggregate by department
            $results = [];
            foreach ($departmentData as $deptId => $deptInfo) {
                // Group attendance records by employee
                $employeeAttendances = [];
                
                foreach ($deptInfo['attendance_records'] as $att) {
                    $empId = $att['employee_id'];
                    if (!isset($employeeAttendances[$empId])) {
                        $employeeAttendances[$empId] = [];
                    }
                    $employeeAttendances[$empId][] = $att;
                }
                
                // Calculate total hours for each employee
                $totalHours = 0.0;
                foreach ($employeeAttendances as $empId => $empAtts) {
                    $empHours = calculateHoursFromAttendances($empAtts);
                    $totalHours += $empHours;
                }
                
                // Calculate average hours per employee
                // Average = sum of all employee hours / number of employees
                $totalEmployees = count($deptInfo['employees']);
                $avgHoursPerEmployee = $totalEmployees > 0 
                    ? round($totalHours / $totalEmployees, 2)
                    : 0.0;
                
                // Count distinct days (based on actual attendance dates)
                $distinctDates = [];
                foreach ($deptInfo['attendance_records'] as $att) {
                    $timeValue = $att['timestamp'] ?? $att['created_at'] ?? null;
                    if ($timeValue) {
                        $dateTime = strtotime($timeValue);
                        if ($dateTime !== false) {
                            $date = date('Y-m-d', $dateTime);
                            if (!in_array($date, $distinctDates)) {
                                $distinctDates[] = $date;
                            }
                        }
                    }
                }
                $totalDays = count($distinctDates);
                
                $results[] = [
                    'department' => $deptInfo['department'],
                    'total_employees' => $totalEmployees,
                    'total_attendance' => $deptInfo['total_attendance'],
                    'total_days' => $totalDays,
                    'total_hours' => round($totalHours, 2),
                    'avg_hours_per_employee' => $avgHoursPerEmployee
                ];
            }
            
            // Sort by total_attendance descending
            usort($results, function($a, $b) {
                return $b['total_attendance'] - $a['total_attendance'];
            });
            
            // Format data for chart (ensure numeric types)
            $data = array_map(function($row) {
                return [
                    'department' => $row['department'],
                    'total_employees' => (int)$row['total_employees'],
                    'total_attendance' => (int)$row['total_attendance'],
                    'total_days' => (int)$row['total_days'],
                    'total_hours' => (float)$row['total_hours'],
                    'avg_hours_per_employee' => (float)$row['avg_hours_per_employee']
                ];
            }, $results);
            
            echo json_encode([
                'success' => true,
                'type' => $type,
                'from' => $fromDate,
                'to' => $toDate,
                'data' => $data
            ]);
            break;
            
        case 'employee-department':
            // Get employee distribution by department
            $query = "
                SELECT 
                    COALESCE(d.department_id, 0) AS department_id,
                    COALESCE(d.department_name, 'Unassigned') AS department,
                    COUNT(DISTINCT e.employee_id) AS total_employees
                FROM employees e
                LEFT JOIN departments d ON e.department_id = d.department_id
                GROUP BY d.department_id, d.department_name
                ORDER BY total_employees DESC
            ";
            
            $stmt = $db->prepare($query);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $data = array_map(function($row) {
                return [
                    'department' => $row['department'],
                    'total_employees' => (int)$row['total_employees']
                ];
            }, $results);
            
            echo json_encode([
                'success' => true,
                'type' => $type,
                'data' => $data
            ]);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(["error" => "Invalid report type"]);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Server error",
        "message" => $e->getMessage()
    ]);
}
