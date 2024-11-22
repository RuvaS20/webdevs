<?php
require_once __DIR__ . '../db/database.php';
require_once __DIR__ . '../utils/validation.php';

class AuthFunctions {
    private $db;
    private $validator;

    public function __construct() {
        $this->db = new Database();
        $this->validator = new Validate();
    }

    /**
     * Attempt to log in a user
     * @param string $email
     * @param string $password
     * @return array Success status and message/user data
     */
    public function login(string $email, string $password): array {
        try {
            // Validate input
            if (!$this->validator->email($email)) {
                return ['success' => false, 'message' => $this->validator->getErrors()['email']];
            }

            // Get user from database
            $sql = "SELECT user_id, username, email, password, role FROM users WHERE email = ? AND is_active = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($password, $user['password'])) {
                return ['success' => false, 'message' => 'Invalid email or password'];
            }

            // Update last login time
            $this->updateLastLogin($user['user_id']);

            // Set session data
            $this->setUserSession($user);

            return [
                'success' => true,
                'user' => [
                    'id' => $user['user_id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ]
            ];
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred during login'];
        }
    }

    /**
     * Register a new user
     * @param array $userData
     * @return array Success status and message/user data
     */
    public function register(array $userData): array {
        try {
            // Validate all user input
            if (!$this->validator->email($userData['email']) ||
                !$this->validator->username($userData['username']) ||
                !$this->validator->password($userData['password']) ||
                !$this->validator->passwordConfirmation($userData['password'], $userData['confirm_password']) ||
                !$this->validator->role($userData['role'])) {
                return ['success' => false, 'message' => $this->validator->getErrors()];
            }

            // Check if email already exists
            if ($this->emailExists($userData['email'])) {
                return ['success' => false, 'message' => 'Email already registered'];
            }

            // Hash password
            $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);

            // Insert new user
            $sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $userData['username'],
                $userData['email'],
                $hashedPassword,
                $userData['role']
            ]);

            $userId = $this->db->lastInsertId();

            return [
                'success' => true,
                'user' => [
                    'id' => $userId,
                    'username' => $userData['username'],
                    'email' => $userData['email'],
                    'role' => $userData['role']
                ]
            ];
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred during registration'];
        }
    }

    /**
     * Log out the current user
     * @return bool
     */
    public function logout(): bool {
        // Unset all session variables
        $_SESSION = array();

        // Destroy the session
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        return true;
    }

    /**
     * Check if user is logged in
     * @return bool
     */
    public function isLoggedIn(): bool {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Get current user's role
     * @return string|null
     */
    public function getUserRole(): ?string {
        return $_SESSION['role'] ?? null;
    }

    /**
     * Check if user has specific role
     * @param string $role
     * @return bool
     */
    public function hasRole(string $role): bool {
        return isset($_SESSION['role']) && $_SESSION['role'] === $role;
    }

    /**
     * Get current user's data
     * @return array|null
     */
    public function getCurrentUser(): ?array {
        if (!$this->isLoggedIn()) {
            return null;
        }

        try {
            $sql = "SELECT user_id, username, email, role FROM users WHERE user_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching user data: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if email exists in database
     * @param string $email
     * @return bool
     */
    private function emailExists(string $email): bool {
        $sql = "SELECT COUNT(*) FROM users WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Update user's last login time
     * @param int $userId
     */
    private function updateLastLogin(int $userId): void {
        $sql = "UPDATE users SET last_login_date = CURRENT_TIMESTAMP WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
    }

    /**
     * Set user session data
     * @param array $user
     */
    private function setUserSession(array $user): void {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['last_activity'] = time();
    }
}


?>
