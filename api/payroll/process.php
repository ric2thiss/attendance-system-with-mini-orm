<?php
/**
 * Process Payrun API
 * 
 * Creates a new payrun based on attendance data and employee salaries
 * POST /api/payroll/process.php
 * Body: { "period_start": "2024-11-01", "period_end": "2024-11-15" }
 */

require_once __DIR__ . "/../../bootstrap.php";
require_once __DIR__ . "/../../auth/helpers.php";

header('Content-Type: application/json');

// Require authentication
if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Not authenticated"]);
    exit;
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed"]);
    exit;
}

try {
    $db = (new Database())->connect();
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Get period dates (default to current pay period)
    $periodStart = $input['period_start'] ?? date('Y-m-01'); // First day of current month
    $periodEnd = $input['period_end'] ?? date('Y-m-d'); // Today
    
    // Validate dates
    if (strtotime($periodStart) > strtotime($periodEnd)) {
        throw new Exception("Period start date must be before end date");
    }
    
    // Check if payrun already exists for this period
    $stmt = $db->prepare("
        SELECT payrun_id FROM payruns 
        WHERE period_start = ? AND period_end = ? AND status != 'cancelled'
    ");
    $stmt->execute([$periodStart, $periodEnd]);
    if ($stmt->fetch()) {
        throw new Exception("Payrun already exists for this period");
    }
    
    // Start transaction
    $db->beginTransaction();
    
    // Create payrun record
    $currentUser = currentUser();
    $createdBy = $currentUser ? ($currentUser['username'] ?? 'system') : 'system';
    
    $stmt = $db->prepare("
        INSERT INTO payruns (payrun_date, period_start, period_end, status, created_by)
        VALUES (?, ?, ?, 'processing', ?)
    ");
    $stmt->execute([date('Y-m-d'), $periodStart, $periodEnd, $createdBy]);
    $payrunId = $db->lastInsertId();
    
    // Get all active employees with their salaries
    $stmt = $db->prepare("
        SELECT 
            e.employee_id,
            es.base_salary,
            es.daily_rate,
            es.hourly_rate,
            es.allowances
        FROM employees e
        LEFT JOIN employee_salaries es ON e.employee_id = es.employee_id
        WHERE es.employee_id IS NOT NULL
    ");
    $stmt->execute();
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $totalGrossPay = 0;
    $totalDeductions = 0;
    $totalNetPay = 0;
    $employeesProcessed = 0;
    
    // Process each employee
    foreach ($employees as $employee) {
        $employeeId = $employee['employee_id'];
        $dailyRate = (float)$employee['daily_rate'];
        $hourlyRate = (float)$employee['hourly_rate'];
        $allowances = (float)$employee['allowances'];
        
        // Get attendance records for this period
        $stmt = $db->prepare("
            SELECT DATE(timestamp) as date, COUNT(*) as count
            FROM attendances
            WHERE employee_id = ? 
            AND DATE(timestamp) >= ? 
            AND DATE(timestamp) <= ?
            GROUP BY DATE(timestamp)
        ");
        $stmt->execute([$employeeId, $periodStart, $periodEnd]);
        $attendanceDays = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $daysWorked = count($attendanceDays);
        $hoursWorked = $daysWorked * 8; // Assume 8 hours per day
        $overtimeHours = 0; // Can be calculated based on actual hours if available
        
        // Calculate gross pay
        $grossPay = ($daysWorked * $dailyRate) + $allowances;
        $overtimePay = $overtimeHours * ($hourlyRate * 1.25); // 1.25x for overtime
        $totalGross = $grossPay + $overtimePay;
        
        // Calculate deductions (Philippine standard rates)
        // SSS: 11% of gross (employee share: 4.5%)
        $sss = $totalGross * 0.045;
        // PhilHealth: 3% of gross
        $philhealth = $totalGross * 0.03;
        // Pag-IBIG: 2% of gross
        $pagibig = $totalGross * 0.02;
        // Tax: Simplified calculation (can be enhanced)
        $tax = max(0, ($totalGross - ($sss + $philhealth + $pagibig)) * 0.05);
        $otherDeductions = 0;
        
        $totalDeductions = $sss + $philhealth + $pagibig + $tax + $otherDeductions;
        $netPay = $totalGross - $totalDeductions;
        
        // Insert payroll record
        $stmt = $db->prepare("
            INSERT INTO payroll_records (
                payrun_id, employee_id, days_worked, hours_worked, overtime_hours,
                gross_pay, allowances, overtime_pay,
                sss, philhealth, pagibig, tax, other_deductions,
                total_deductions, net_pay
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $payrunId, $employeeId, $daysWorked, $hoursWorked, $overtimeHours,
            $grossPay, $allowances, $overtimePay,
            $sss, $philhealth, $pagibig, $tax, $otherDeductions,
            $totalDeductions, $netPay
        ]);
        
        $totalGrossPay += $totalGross;
        $totalDeductions += $totalDeductions;
        $totalNetPay += $netPay;
        $employeesProcessed++;
    }
    
    // Update payrun totals
    $stmt = $db->prepare("
        UPDATE payruns 
        SET total_gross_pay = ?, 
            total_deductions = ?, 
            total_net_pay = ?,
            employees_count = ?,
            status = 'completed'
        WHERE payrun_id = ?
    ");
    $stmt->execute([$totalGrossPay, $totalDeductions, $totalNetPay, $employeesProcessed, $payrunId]);
    
    // Commit transaction
    $db->commit();
    
    echo json_encode([
        "success" => true,
        "message" => "Payrun processed successfully",
        "data" => [
            "payrun_id" => $payrunId,
            "payrun_date" => date('Y-m-d'),
            "period_start" => $periodStart,
            "period_end" => $periodEnd,
            "total_gross_pay" => $totalGrossPay,
            "total_deductions" => $totalDeductions,
            "total_net_pay" => $totalNetPay,
            "employees_count" => $employeesProcessed
        ]
    ]);
    
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    error_log("Process payrun error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
