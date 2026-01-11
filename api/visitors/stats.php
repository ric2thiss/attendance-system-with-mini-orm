<?php
require_once __DIR__ . "/../../bootstrap.php";
require_once __DIR__ . "/../../auth/helpers.php";

header("Content-Type: application/json");

// Require authentication
if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$filter = $_GET["filter"] ?? "month";

// Get date range based on filter
$now = new DateTime();
$startDate = null;
$endDate = new DateTime();

switch($filter) {
    case 'today':
        $startDate = (new DateTime())->setTime(0, 0, 0);
        break;
    case 'week':
        $startDate = (clone $now)->modify('monday this week')->setTime(0, 0, 0);
        break;
    case 'month':
        $startDate = (clone $now)->modify('first day of this month')->setTime(0, 0, 0);
        break;
    case 'year':
        $startDate = (clone $now)->modify('first day of January this year')->setTime(0, 0, 0);
        break;
    default:
        $startDate = (clone $now)->modify('first day of this month')->setTime(0, 0, 0);
}

$startDateStr = $startDate->format('Y-m-d H:i:s');
$endDateStr = $endDate->format('Y-m-d H:i:s');

// Get visitor counts from verification_log
$pdo = (new Database())->connect();

// Total visitors in date range using whereBetween
$totalVisitors = VerificationLog::query()
    ->whereBetween('created_at', [$startDateStr, $endDateStr])
    ->count();

// Resident visitors: verifications that link to employees (who are residents)
// Use raw SQL query for complex join with count
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT verification_log.id) as count
    FROM verification_log
    INNER JOIN employees ON verification_log.employee_id = employees.employee_id
    INNER JOIN residents ON employees.resident_id = residents.resident_id
    WHERE verification_log.created_at >= ? AND verification_log.created_at <= ?
");
$stmt->execute([$startDateStr, $endDateStr]);
$result = $stmt->fetch(PDO::FETCH_OBJ);

$totalResidentVisitors = $result ? (int)$result->count : 0;
$totalNonResidentVisitors = max(0, $totalVisitors - $totalResidentVisitors);

// Count Online Appointment visitors first (based on status field - adjust status values as needed)
// Prioritize online/appointment status to make counts mutually exclusive
$onlineStmt = $pdo->prepare("
    SELECT COUNT(DISTINCT verification_log.id) as count
    FROM verification_log
    WHERE verification_log.created_at >= ? AND verification_log.created_at <= ?
    AND (
        LOWER(verification_log.status) LIKE '%online%'
        OR LOWER(verification_log.status) LIKE '%appointment%'
        OR LOWER(verification_log.status) LIKE '%web%'
        OR LOWER(verification_log.status) = 'online'
        OR LOWER(verification_log.status) = 'appointment'
    )
");
$onlineStmt->execute([$startDateStr, $endDateStr]);
$onlineResult = $onlineStmt->fetch(PDO::FETCH_OBJ);
$totalOnlineAppointment = $onlineResult ? (int)$onlineResult->count : 0;

// Count Walk-in visitors (excludes online/appointment to make mutually exclusive)
$walkinStmt = $pdo->prepare("
    SELECT COUNT(DISTINCT verification_log.id) as count
    FROM verification_log
    WHERE verification_log.created_at >= ? AND verification_log.created_at <= ?
    AND (
        LOWER(verification_log.status) LIKE '%walk%in%' 
        OR LOWER(verification_log.status) LIKE '%walkin%'
        OR LOWER(verification_log.status) LIKE '%in-person%'
        OR LOWER(verification_log.status) LIKE '%inperson%'
        OR (
            LOWER(verification_log.status) NOT LIKE '%online%'
            AND LOWER(verification_log.status) NOT LIKE '%appointment%'
            AND LOWER(verification_log.status) NOT LIKE '%web%'
        )
    )
");
$walkinStmt->execute([$startDateStr, $endDateStr]);
$walkinResult = $walkinStmt->fetch(PDO::FETCH_OBJ);
$totalWalkin = $walkinResult ? (int)$walkinResult->count : 0;

echo json_encode([
    "success" => true,
    "filter" => $filter,
    "total_visitors" => $totalVisitors,
    "resident_visitors" => $totalResidentVisitors,
    "non_resident_visitors" => $totalNonResidentVisitors,
    "walkin" => $totalWalkin,
    "online_appointment" => $totalOnlineAppointment
]);
