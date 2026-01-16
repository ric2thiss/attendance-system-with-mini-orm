<?php
/**
 * Profile API Endpoint
 * PUT: Update profile information or change password
 */

// Turn off error display, log errors instead
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Set JSON header first
header('Content-Type: application/json');

// Start output buffering to catch any errors
ob_start();

try {
    require_once __DIR__ . '/../../bootstrap.php';

    // Only allow authenticated users
    require_once __DIR__ . '/../../auth/helpers.php';
    requireAuth();

    $user = currentUser();
    if (!$user) {
        ob_clean();
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "Unauthorized"
        ]);
        exit;
    }

    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    
    if ($method !== 'PUT' && $method !== 'POST') {
        ob_clean();
        http_response_code(405);
        echo json_encode([
            "success" => false,
            "message" => "Method not allowed"
        ]);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !is_array($input)) {
        ob_clean();
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Invalid request data"
        ]);
        exit;
    }

    $db = (new Database())->connect();
    $adminRepository = new AdminRepository($db);
    
    // Check if this is a password change request
    if (isset($input['action']) && $input['action'] === 'change_password') {
        // Change password
        $currentPassword = $input['current_password'] ?? '';
        $newPassword = $input['new_password'] ?? '';
        
        if (empty($currentPassword) || empty($newPassword)) {
            ob_clean();
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "message" => "Current password and new password are required"
            ]);
            exit;
        }
        
        if (strlen($newPassword) < 6) {
            ob_clean();
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "message" => "New password must be at least 6 characters"
            ]);
            exit;
        }
        
        // Verify current password
        $admin = $adminRepository->findById($user['id']);
        if (!$admin) {
            ob_clean();
            http_response_code(404);
            echo json_encode([
                "success" => false,
                "message" => "User not found"
            ]);
            exit;
        }
        
        // Convert object to array if needed
        if (is_object($admin)) {
            $admin = json_decode(json_encode($admin), true);
        }
        
        if (!$adminRepository->verifyPassword($currentPassword, $admin['password'])) {
            ob_clean();
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "message" => "Current password is incorrect"
            ]);
            exit;
        }
        
        // Update password
        $hashedPassword = $adminRepository->hashPassword($newPassword);
        $success = Admin::query()->where('id', $user['id'])->update(['password' => $hashedPassword]);
        
        if ($success) {
            ob_clean();
            echo json_encode([
                "success" => true,
                "message" => "Password changed successfully"
            ]);
        } else {
            ob_clean();
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Failed to update password"
            ]);
        }
    } else {
        // Update profile information
        $updateData = [];
        
        if (isset($input['username'])) {
            $username = trim($input['username']);
            
            if (empty($username)) {
                ob_clean();
                http_response_code(400);
                echo json_encode([
                    "success" => false,
                    "message" => "Username cannot be empty"
                ]);
                exit;
            }
            
            // Check if username is already taken by another user
            $existingAdmin = $adminRepository->findByUsername($username);
            if ($existingAdmin) {
                if (is_object($existingAdmin)) {
                    $existingAdmin = json_decode(json_encode($existingAdmin), true);
                }
                
                // Allow if it's the same user
                if ($existingAdmin['id'] != $user['id']) {
                    ob_clean();
                    http_response_code(400);
                    echo json_encode([
                        "success" => false,
                        "message" => "Username is already taken by another user"
                    ]);
                    exit;
                }
            }
            
            $updateData['username'] = $username;
        }
        
        if (isset($input['full_name'])) {
            $updateData['full_name'] = trim($input['full_name']);
        }
        
        if (isset($input['email'])) {
            $email = trim($input['email']);
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                ob_clean();
                http_response_code(400);
                echo json_encode([
                    "success" => false,
                    "message" => "Invalid email format"
                ]);
                exit;
            }
            
            // Check if email is already taken by another user
            $existingAdmin = $adminRepository->findByEmail($email);
            if ($existingAdmin) {
                if (is_object($existingAdmin)) {
                    $existingAdmin = json_decode(json_encode($existingAdmin), true);
                }
                
                // Allow if it's the same user
                if ($existingAdmin['id'] != $user['id']) {
                    ob_clean();
                    http_response_code(400);
                    echo json_encode([
                        "success" => false,
                        "message" => "Email is already taken by another user"
                    ]);
                    exit;
                }
            }
            
            $updateData['email'] = $email;
        }
        
        if (empty($updateData)) {
            ob_clean();
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "message" => "No valid fields to update"
            ]);
            exit;
        }
        
        // Update profile
        $success = Admin::query()->where('id', $user['id'])->update($updateData);
        
        if ($success) {
            // Update session if username, full_name or email changed
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            if (isset($updateData['username'])) {
                $_SESSION['admin_username'] = $updateData['username'];
            }
            
            if (isset($updateData['full_name'])) {
                $_SESSION['admin_full_name'] = $updateData['full_name'];
            }
            
            if (isset($updateData['email'])) {
                $_SESSION['admin_email'] = $updateData['email'];
            }
            
            ob_clean();
            echo json_encode([
                "success" => true,
                "message" => "Profile updated successfully"
            ]);
        } else {
            ob_clean();
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Failed to update profile"
            ]);
        }
    }
    
} catch (Exception $e) {
    // Log the error
    error_log("Profile API Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Clean any output and send JSON error
    ob_clean();
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Internal server error",
        "error" => $e->getMessage()
    ]);
} catch (Error $e) {
    // Catch PHP 7+ errors
    error_log("Profile API Fatal Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    ob_clean();
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Internal server error",
        "error" => $e->getMessage()
    ]);
}

// End output buffering
ob_end_flush();
