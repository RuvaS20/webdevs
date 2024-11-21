<?php
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function registerUser($pdo, $type, $username, $email, $password) {
    try {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT email FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Email already registered'];
        }
        
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT username FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Username already taken'];
        }
        
        $hashedPassword = hashPassword($password);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, registration_date, last_login_date, is_active) 
                              VALUES (:username, :email, :password, :role, NOW(), NOW(), 1)");
        
        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password' => $hashedPassword,
            ':role' => $type
        ]);
        
        return ['success' => true, 'message' => 'Registration successful'];
    } catch(PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Registration failed. Please try again later.'];
    }
}

function createSession($userId, $userType) {
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_type'] = $userType;
    $_SESSION['last_activity'] = time();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function checkUserType($allowedTypes) {
    if (!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], $allowedTypes)) {
        header('Location: /auth/login.php');
        exit();
    }
}
?>
