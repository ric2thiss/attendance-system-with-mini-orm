<?php
/**
 * Authentication Helper Functions
 * 
 * Include this file in pages that need authentication checks
 */

require_once __DIR__ . "/../bootstrap.php";

/**
 * Check if user is authenticated
 *
 * @return bool
 */
function isAuthenticated(): bool
{
    return AuthController::check();
}

/**
 * Get current authenticated user
 *
 * @return array|null
 */
function currentUser(): ?array
{
    return AuthController::user();
}

/**
 * Require authentication (redirect if not authenticated)
 *
 * @param string $redirectTo
 * @return void
 */
function requireAuth(string $redirectTo = "/attendance-system/auth/login.php"): void
{
    AuthController::requireAuth($redirectTo);
}

/**
 * Check if user has specific role
 *
 * @param string|array $roles
 * @return bool
 */
function hasRole($roles): bool
{
    $user = currentUser();
    if (!$user) {
        return false;
    }

    if (is_array($roles)) {
        return in_array($user["role"], $roles);
    }

    return $user["role"] === $roles;
}

/**
 * Require specific role (redirect if not authorized)
 *
 * @param string|array $roles
 * @param string $redirectTo
 * @return void
 */
function requireRole($roles, string $redirectTo = "/attendance-system/admin/dashboard.php"): void
{
    requireAuth();
    
    if (!hasRole($roles)) {
        header("Location: " . $redirectTo . "?error=unauthorized");
        exit;
    }
}

