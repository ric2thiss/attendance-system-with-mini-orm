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
    // TODO: Replace this with actual booking check from database
    // For now, this is a placeholder that checks a hypothetical bookings table
    // You'll need to create the bookings table or integrate with external API
    
    $db = (new Database())->connect();
    
    // Check if bookings table exists (for future implementation)
    // For now, we'll return a mock response structure
    // When you have the booking API, replace this section
    
    // Example query structure (uncomment when bookings table is created):
    /*
    $stmt = $db->prepare("
        SELECT 
            booking_id,
            resident_id,
            service_name,
            appointment_date,
            appointment_time,
            status,
            notes
        FROM bookings
        WHERE resident_id = :resident_id
        AND appointment_date >= CURDATE()
        AND status IN ('pending', 'confirmed')
        ORDER BY appointment_date ASC, appointment_time ASC
        LIMIT 1
    ");
    $stmt->execute([':resident_id' => $residentId]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    */
    
    // Placeholder: Return no booking for now
    // Replace this with actual database query when bookings table is ready
    $booking = null;
    
    if ($booking) {
        echo json_encode([
            'success' => true,
            'has_booking' => true,
            'booking' => [
                'booking_id' => $booking['booking_id'],
                'service_name' => $booking['service_name'],
                'appointment_date' => $booking['appointment_date'],
                'appointment_time' => $booking['appointment_time'],
                'status' => $booking['status'],
                'notes' => $booking['notes'] ?? ''
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
