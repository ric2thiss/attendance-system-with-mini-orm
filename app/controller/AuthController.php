<?php

class AuthController
{
    protected $adminRepository;
    protected $profilingDbConnection = null;

    public function __construct()
    {
        $db = (new Database())->connect();
        $this->adminRepository = new AdminRepository($db);
    }

    /**
     * Get connection to profiling-system database
     *
     * @return PDO|null
     */
    protected function getProfilingDbConnection(): ?PDO
    {
        if ($this->profilingDbConnection !== null) {
            return $this->profilingDbConnection;
        }

        try {
            $host = "localhost";
            $dbname = defined('PROFILING_DB_NAME') ? PROFILING_DB_NAME : "profiling-system";
            $username = "root";
            $password = "";

            $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8";
            $this->profilingDbConnection = new PDO($dsn, $username, $password);
            $this->profilingDbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->profilingDbConnection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            return $this->profilingDbConnection;
        } catch (PDOException $e) {
            error_log("Profiling DB connection failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Authenticate against profiling-system database
     * Checks admin, barangay_official, and residents tables in order
     *
     * @param string $username
     * @param string $password
     * @return array|null Returns user data if authenticated, null otherwise
     */
    protected function authenticateProfilingSystem(string $username, string $password): ?array
    {
        $conn = $this->getProfilingDbConnection();
        if (!$conn) {
            return null;
        }

        // 1. Check profiling-system admin table
        try {
            $stmt = $conn->prepare("SELECT * FROM admin WHERE username = :username LIMIT 1");
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            if ($row = $stmt->fetch()) {
                // Verify password (support both hash and plain for legacy/dev)
                if (password_verify($password, $row['password']) || $password === $row['password']) {
                    return [
                        'id' => $row['id'],
                        'username' => $row['username'],
                        'email' => $row['email'] ?? null,
                        'full_name' => $row['name'] ?? $row['username'],
                        'role' => 'admin',
                        'source' => 'profiling_admin'
                    ];
                }
            }
        } catch (PDOException $e) {
            error_log("Profiling admin check failed: " . $e->getMessage());
        }

        // 2. Check barangay_official table
        try {
            $stmt = $conn->prepare("SELECT * FROM barangay_official WHERE username = :username LIMIT 1");
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            if ($row = $stmt->fetch()) {
                if (password_verify($password, $row['password'])) {
                    return [
                        'id' => $row['id'],
                        'username' => $row['username'],
                        'email' => $row['email'] ?? null,
                        'full_name' => trim(($row['first_name'] ?? '') . ' ' . ($row['surname'] ?? '')),
                        'role' => $row['position'] ?? 'Barangay Official',
                        'source' => 'profiling_barangay_official'
                    ];
                }
            }
        } catch (PDOException $e) {
            error_log("Profiling barangay_official check failed: " . $e->getMessage());
        }

        // 3. Check residents table
        try {
            $stmt = $conn->prepare("SELECT * FROM residents WHERE username = :username LIMIT 1");
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            if ($row = $stmt->fetch()) {
                // Residents might use hash or plain
                if (password_verify($password, $row['password']) || $password === $row['password']) {
                    return [
                        'id' => $row['id'],
                        'username' => $row['username'],
                        'email' => $row['email'] ?? null,
                        'full_name' => trim(($row['first_name'] ?? '') . ' ' . ($row['surname'] ?? '')),
                        'role' => 'resident',
                        'source' => 'profiling_resident'
                    ];
                }
            }
        } catch (PDOException $e) {
            error_log("Profiling residents check failed: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Set session variables compatible with both attendance-system and profiling-system
     *
     * @param array $userData
     * @return void
     */
    protected function setCompatibleSessionVariables(array $userData): void
    {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Attendance-system session variables
        $_SESSION["admin_id"] = $userData["id"];
        $_SESSION["admin_username"] = $userData["username"];
        $_SESSION["admin_email"] = $userData["email"] ?? '';
        $_SESSION["admin_full_name"] = $userData["full_name"];
        $_SESSION["admin_role"] = $userData["role"];
        $_SESSION["is_authenticated"] = true;
        $_SESSION["login_time"] = time();

        // Profiling-system compatible session variables (login.php format)
        $_SESSION["user_id"] = $userData["id"];
        $_SESSION["username"] = $userData["username"];
        $_SESSION["role"] = $userData["role"];
        $_SESSION["name"] = $userData["full_name"];

        // Track authentication source for debugging/logging
        $_SESSION["auth_source"] = $userData["source"] ?? 'attendance_admin';
    }

    /**
     * Attempt to login with username/email and password
     * Authentication flow:
     * 1. Check attendance-system admins table
     * 2. If not found, check profiling-system admin table
     * 3. If not found, check profiling-system barangay_official table
     * 4. If not found, check profiling-system residents table
     *
     * @param string $usernameOrEmail
     * @param string $password
     * @return array
     */
    public function login(string $usernameOrEmail, string $password): array
    {
        // STEP 1: Check attendance-system admins table
        $admin = Admin::query()
            ->where("username", $usernameOrEmail)
            ->first();

        if (!$admin) {
            $admin = Admin::query()
                ->where("email", $usernameOrEmail)
                ->first();
        }

        if ($admin) {
            // Convert object to array if needed (QueryBuilder returns objects by default)
            if (is_object($admin)) {
                $admin = json_decode(json_encode($admin), true);
            }

            // Verify password
            if ($this->adminRepository->verifyPassword($password, $admin["password"])) {
                // Check if admin account is locked (is_active = 0 means locked)
                if (!$admin["is_active"]) {
                    return [
                        "success" => false,
                        "message" => "Your account has been locked by the administrator. Please contact support for assistance."
                    ];
                }

                // Prepare user data
                $userData = [
                    "id" => $admin["id"],
                    "username" => $admin["username"],
                    "email" => $admin["email"],
                    "full_name" => $admin["full_name"] ?? $admin["username"],
                    "role" => $admin["role"],
                    "source" => "attendance_admin"
                ];

                // Set compatible session variables
                $this->setCompatibleSessionVariables($userData);

                // Update last login
                $this->adminRepository->updateLastLogin($admin["id"]);

                return [
                    "success" => true,
                    "message" => "Login successful",
                    "admin" => [
                        "id" => $userData["id"],
                        "username" => $userData["username"],
                        "email" => $userData["email"],
                        "full_name" => $userData["full_name"],
                        "role" => $userData["role"]
                    ]
                ];
            }
        }

        // STEP 2-4: Check profiling-system database (admin → barangay_official → residents)
        $profilingUser = $this->authenticateProfilingSystem($usernameOrEmail, $password);

        if ($profilingUser) {
            // Set compatible session variables
            $this->setCompatibleSessionVariables($profilingUser);

            return [
                "success" => true,
                "message" => "Login successful",
                "admin" => [
                    "id" => $profilingUser["id"],
                    "username" => $profilingUser["username"],
                    "email" => $profilingUser["email"],
                    "full_name" => $profilingUser["full_name"],
                    "role" => $profilingUser["role"]
                ]
            ];
        }

        // No match found in any table
        return [
            "success" => false,
            "message" => "Invalid username or password"
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
    public static function requireAuth(?string $redirectTo = null): void
    {
        if (!defined("BASE_URL")) {
            require_once __DIR__ . "/../../config/app.config.php";
        }

        if ($redirectTo === null) {
            $redirectTo = "/login.php";
        }

        if (!self::check()) {
            header("Location: " . $redirectTo);
            exit;
        }
    }
}

