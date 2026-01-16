<?php
/**
 * API Endpoint: Get residents with photos for face recognition
 * GET /api/visitors/residents.php
 * 
 * Returns residents with their photo paths formatted for face recognition
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
    $residentController = new ResidentController();
    $residents = $residentController->getAllResidents();
    
    // Get base URL for images
    // Photos are stored relative to project root (e.g., storage/img/residents/...)
    // Use BASE_URL from config if available, otherwise calculate it
    if (!defined("BASE_URL")) {
        require_once __DIR__ . "/../../config/app.config.php";
    }
    $baseUrl = BASE_URL;
    
    // Format residents for face recognition
    $formattedResidents = [];
    
    foreach ($residents as $resident) {
        // Get photo path (handle both JSON array and single path)
        $photoPath = $resident['photo_path'] ?? null;
        $photoUrls = [];
        
        if (!empty($photoPath)) {
            // Try to decode as JSON (new format - array of photos)
            $decoded = json_decode($photoPath, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && !empty($decoded)) {
                // Use all photos (3 angles) for face recognition
                foreach ($decoded as $path) {
                    if (!empty($path) && is_string($path)) {
                        // Skip invalid paths like '/to/photo' or empty strings
                        if (strlen(trim($path)) > 3 && !preg_match('/^\/to\/|^\/path\/to\//i', $path)) {
                            // If path already starts with http:// or https://, use as-is
                            if (preg_match('/^https?:\/\//i', $path)) {
                                $photoUrls[] = $path;
                            } else {
                                $photoUrls[] = $baseUrl . '/' . ltrim($path, '/');
                            }
                        }
                    }
                }
            } else {
                // Single path (old format) - convert to array for consistency
                $singlePath = trim($photoPath);
                // Skip invalid paths
                if (strlen($singlePath) > 3 && !preg_match('/^\/to\/|^\/path\/to\//i', $singlePath)) {
                    // If path already starts with http:// or https://, use as-is
                    if (preg_match('/^https?:\/\//i', $singlePath)) {
                        $photoUrls[] = $singlePath;
                    } else {
                        $photoUrls[] = $baseUrl . '/' . ltrim($singlePath, '/');
                    }
                }
            }
        }
        
        // Only include residents with at least one photo
        if (!empty($photoUrls)) {
            $fullName = trim(($resident['first_name'] ?? '') . ' ' . ($resident['middle_name'] ?? '') . ' ' . ($resident['last_name'] ?? ''));
            $fullName = preg_replace('/\s+/', ' ', $fullName); // Clean up multiple spaces
            
            $formattedResidents[] = [
                'id' => (int)$resident['resident_id'],
                'name' => $fullName,
                'imgs' => $photoUrls, // Array of all photos (3 angles)
                'img' => $photoUrls[0], // First photo for display purposes
                'resident_id' => (int)$resident['resident_id'],
                'phil_sys_number' => $resident['phil_sys_number'] ?? null,
                'first_name' => $resident['first_name'] ?? '',
                'middle_name' => $resident['middle_name'] ?? '',
                'last_name' => $resident['last_name'] ?? '',
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'residents' => $formattedResidents,
        'count' => count($formattedResidents)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch residents',
        'message' => $e->getMessage()
    ]);
}
