<?php

class AuthController
{
    protected $adminRepository;

    public function __construct() {
        $db = (new Database())->connect();
        $this->adminRepository = new AdminRepository($db);
    }

    /**
     * Attempt to login with username/email and password
     *
     * @param string $usernameOrEmail
     * @param string $password
     * @return array
     */
    public function login(string $usernameOrEmail, string $password): array
    {
        // Find admin by username or email
        $admin = $this->adminRepository->findByUsername($usernameOrEmail);
        
        if (!$admin) {
            $admin = $this->adminRepository->findByEmail($usernameOrEmail);
        }

        if (!$admin) {
            return [
                "success" => false,
                "message" => "Invalid username or password"
            ];
        }

        // Convert object to array if needed (QueryBuilder returns objects by default)
        if (is_object($admin)) {
            $admin = json_decode(json_encode($admin), true);
        }

        // Verify password
        if (!$this->adminRepository->verifyPassword($password, $admin["password"])) {
            return [
                "success" => false,
                "message" => "Invalid username or password"
            ];
        }

        // Check if admin is active
        if (!$admin["is_active"]) {
            return [
                "success" => false,
                "message" => "Account is deactivated. Please contact administrator."
            ];
        }

        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Set session variables
        $_SESSION["admin_id"] = $admin["id"];
        $_SESSION["admin_username"] = $admin["username"];
        $_SESSION["admin_email"] = $admin["email"];
        $_SESSION["admin_full_name"] = $admin["full_name"] ?? $admin["username"];
        $_SESSION["admin_role"] = $admin["role"];
        $_SESSION["is_authenticated"] = true;
        $_SESSION["login_time"] = time();

        // Update last login
        $this->adminRepository->updateLastLogin($admin["id"]);

        return [
            "success" => true,
            "message" => "Login successful",
            "admin" => [
                "id" => $admin["id"],
                "username" => $admin["username"],
                "email" => $admin["email"],
                "full_name" => $admin["full_name"] ?? null,
                "role" => $admin["role"]
            ]
        ];
    }

    /**
     * Logout current user
     *
     * @return void
     */
    public function logout(): void
    {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Unset all session variables
        $_SESSION = [];

        // Destroy session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        // Destroy session
        session_destroy();
    }

    /**
     * Check if user is authenticated
     *
     * @return bool
     */
    public static function check(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return isset($_SESSION["is_authenticated"]) && $_SESSION["is_authenticated"] === true;
    }

    /**
     * Get current authenticated admin
     *
     * @return array|null
     */
    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        return [
            "id" => $_SESSION["admin_id"] ?? null,
            "username" => $_SESSION["admin_username"] ?? null,
            "email" => $_SESSION["admin_email"] ?? null,
            "full_name" => $_SESSION["admin_full_name"] ?? null,
            "role" => $_SESSION["admin_role"] ?? null
        ];
    }

    /**
     * Require authentication (redirect if not authenticated)
     *
     * @param string $redirectTo
     * @return void
     */
    public static function requireAuth(string $redirectTo = "/attendance-system/auth/login.php"): void
    {
        if (!self::check()) {
            header("Location: " . $redirectTo);
            exit;
        }
    }
}

