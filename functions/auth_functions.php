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
    public function login(string $email, string $password): array {
        try {
            if (!$this->validator->email($email)) 
            {
                return ['success' => false, 'message' => $this->validator->getErrors()['email']];
            }

            $sql = "SELECT user_id, username, email, password, role FROM msasa_users WHERE email = ? AND is_active = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($password, $user['password'])) 
            {
                return ['success' => false, 'message' => 'Invalid email or password'];
            }
            $this->updateLastLogin($user['user_id']);
            $this->setUserSession($user);
            return 
            [
                'success' => true,
                'user' => 
                [
                    'id' => $user['user_id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ]
            ];
        } 
        catch (PDOException $e) 
        {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred during login'];
        }
    }

   
    public function register(array $userData): array {
        try {
            if (!$this->validator->email($userData['email']) ||
                !$this->validator->username($userData['username']) ||
                !$this->validator->password($userData['password']) ||
                !$this->validator->passwordConfirmation($userData['password'], $userData['confirm_password']) ||
                !$this->validator->role($userData['role'])) {
                return ['success' => false, 'message' => $this->validator->getErrors()];
            }

            if ($this->emailExists($userData['email'])) 
            {
                return ['success' => false, 'message' => 'Email already registered'];
            }
            $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);
            $sql = "INSERT INTO msasa_users (username, email, password, role) VALUES (?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $userData['username'],
                $userData['email'],
                $hashedPassword,
                $userData['role']
            ]);
            $userId = $this->db->lastInsertId();
            return 
            [
                'success' => true,
                'user' => [
                    'id' => $userId,
                    'username' => $userData['username'],
                    'email' => $userData['email'],
                    'role' => $userData['role']
                ]
            ];
        } catch (PDOException $e) 
        {
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred during registration'];
        }
    }

    public function logout(): bool 
    {
        $_SESSION = array();
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        return true;
    }

    public function isLoggedIn(): bool 
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    public function getUserRole(): ?string 
    {
        return $_SESSION['role'] ?? null;
    }

    
    public function hasRole(string $role): bool 
    {
        return isset($_SESSION['role']) && $_SESSION['role'] === $role;
    }

    public function getCurrentUser(): ?array 
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        try {
            $sql = "SELECT user_id, username, email, role FROM msasa_users WHERE user_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching user data: " . $e->getMessage());
            return null;
        }
    }

   
    private function emailExists(string $email): bool 
    {
        $sql = "SELECT COUNT(*) FROM msasa_users WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetchColumn() > 0;
    }

    private function updateLastLogin(int $userId): void 
    {
        $sql = "UPDATE msasa_users SET last_login_date = CURRENT_TIMESTAMP WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
    }
    private function setUserSession(array $user): void {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['last_activity'] = time();
    }
}

?>
