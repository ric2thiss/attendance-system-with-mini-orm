<?php
/**
 * API Endpoint: Check if resident has a booking
 * GET /api/visitors/check-booking.php?resident_id={id}
 * 
 * Returns booking information if resident has an appointment
 */
require_once __DIR__ . "/../../bootstrap.php";

header("Content-Type: application/json");

$method = $_SERVER["REQUEST_METHOD"];

if ($method !== "GET") {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

$residentId = $_GET['resident_id'] ?? null;

if (empty($residentId)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'resident_id parameter is required'
    ]);
    exit;
}

try {
    $db = (new Database())->connect();

    $stmt = $db->prepare("
        SELECT 
            id,
            resident_id,
            service_name,
            created
        FROM `baranggay_services`.`online_appointment`
        WHERE resident_id = :resident_id
        ORDER BY created DESC
        LIMIT 1
    ");
    $stmt->execute([':resident_id' => (int) $residentId]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($booking) {
        echo json_encode([
            'success' => true,
            'has_booking' => true,
            'booking' => [
                'booking_id' => $booking['id'],
                'service_name' => $booking['service_name'],
                'appointment_date' => $booking['created'] ? date('Y-m-d', strtotime($booking['created'])) : null,
                'appointment_time' => $booking['created'] ? date('H:i:s', strtotime($booking['created'])) : null,
                'status' => 'confirmed',
                'notes' => ''
            ]
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'has_booking' => false,
            'booking' => null
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to check booking',
        'message' => $e->getMessage()
    ]);
}
