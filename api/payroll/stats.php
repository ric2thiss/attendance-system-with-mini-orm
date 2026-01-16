<?php
/**
 * Payroll Statistics API
 * 
 * Returns payroll summary statistics for the dashboard
 * GET /api/payroll/stats.php?period=last_month|current_month|last_payrun
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

try {
    $db = (new Database())->connect();
    $period = $_GET['period'] ?? 'last_payrun';
    
    // Get the most recent completed payrun
    $stmt = $db->prepare("
        SELECT 
            payrun_id,
            payrun_date,
            period_start,
            period_end,
            total_gross_pay,
            total_deductions,
            total_net_pay,
            employees_count
        FROM payruns
        WHERE status = 'completed'
        ORDER BY payrun_date DESC
        LIMIT 1
    ");
    $stmt->execute();
    $lastPayrun = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get previous payrun for comparison
    $stmt = $db->prepare("
        SELECT 
            total_gross_pay,
            total_deductions,
            total_net_pay
        FROM payruns
        WHERE status = 'completed' AND payrun_id != ?
        ORDER BY payrun_date DESC
        LIMIT 1
    ");
    $stmt->execute([$lastPayrun['payrun_id'] ?? 0]);
    $previousPayrun = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Calculate percentage changes
    $grossChange = 0;
    $deductionsChange = 0;
    
    if ($previousPayrun && $lastPayrun) {
        if ($previousPayrun['total_gross_pay'] > 0) {
            $grossChange = (($lastPayrun['total_gross_pay'] - $previousPayrun['total_gross_pay']) / $previousPayrun['total_gross_pay']) * 100;
        }
        if ($previousPayrun['total_deductions'] > 0) {
            $deductionsChange = (($lastPayrun['total_deductions'] - $previousPayrun['total_deductions']) / $previousPayrun['total_deductions']) * 100;
        }
    }
    
    // Get active employees count
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM employees");
    $stmt->execute();
    $employeeCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    
    // If no payrun exists, return zeros with employee count
    if (!$lastPayrun) {
        echo json_encode([
            "success" => true,
            "data" => [
                "total_gross_pay" => 0.00,
                "total_deductions" => 0.00,
                "total_net_pay" => 0.00,
                "employees_count" => (int)$employeeCount,
                "gross_pay_change" => 0,
                "deductions_change" => 0,
                "last_payrun_date" => null,
                "period_covered" => null
            ]
        ]);
        exit;
    }
    
    echo json_encode([
        "success" => true,
        "data" => [
            "total_gross_pay" => (float)$lastPayrun['total_gross_pay'],
            "total_deductions" => (float)$lastPayrun['total_deductions'],
            "total_net_pay" => (float)$lastPayrun['total_net_pay'],
            "employees_count" => (int)$lastPayrun['employees_count'],
            "gross_pay_change" => round($grossChange, 2),
            "deductions_change" => round($deductionsChange, 2),
            "last_payrun_date" => $lastPayrun['payrun_date'],
            "period_covered" => $lastPayrun['period_start'] . ' - ' . $lastPayrun['period_end']
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Payroll stats error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error fetching payroll statistics"
    ]);
}
