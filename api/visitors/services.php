<?php
/**
 * API Endpoint: Get available services
 * GET /api/visitors/services.php
 * 
 * Returns list of available services (placeholder for external API integration)
 */
require_once __DIR__ . "/../../bootstrap.php";

header("Content-Type: application/json");

$method = $_SERVER["REQUEST_METHOD"];

if ($method !== "GET") {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

try {
    // TODO: Replace this with actual external API call
    // For now, this is a placeholder that returns mock services
    // When you have the external API, replace this section with:
    // 1. API call to external service
    // 2. Parse and format the response
    // 3. Return formatted services
    
    // Example external API call (uncomment when ready):
    /*
    $externalApiUrl = 'https://your-external-api.com/services';
    $apiKey = 'your-api-key'; // Store in config
    
    $ch = curl_init($externalApiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $services = json_decode($response, true);
        echo json_encode([
            'success' => true,
            'services' => $services
        ]);
        exit;
    }
    */
    
    $services = [
        [
            'service_id' => 1,
            'service_name' => 'Barangay Clearance',
            'description' => 'Certificate of residency and good standing',
            'duration' => '15 minutes',
            'fee' => 0
        ],
        [
            'service_id' => 2,
            'service_name' => 'Certificate of Residency',
            'description' => 'Official proof of residence within the barangay',
            'duration' => '15 minutes',
            'fee' => 0
        ],
        [
            'service_id' => 3,
            'service_name' => 'Certificate of Indigency',
            'description' => 'Certificate of indigency for government assistance',
            'duration' => '20 minutes',
            'fee' => 0
        ],
        [
            'service_id' => 4,
            'service_name' => 'Business Permit',
            'description' => 'Application for barangay business permit',
            'duration' => '30 minutes',
            'fee' => 500
        ],
        [
            'service_id' => 5,
            'service_name' => 'Other Barangay Services',
            'description' => 'General inquiry or other barangay services',
            'duration' => 'Varies',
            'fee' => 0
        ]
    ];

    echo json_encode([
        'success' => true,
        'services' => $services,
        'count' => count($services)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch services',
        'message' => $e->getMessage()
    ]);
}
