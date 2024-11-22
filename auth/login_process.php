<?php
session_start();
require_once '../db/config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;

    try {
        // Check if user exists with the provided username/email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
        $stmt->execute([
            ':username' => $username,
            ':email' => $username
        ]);
        
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Login successful, set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['success'] = "Welcome back, " . $user['username'];  
            
            // Update last login time
            $updateStmt = $pdo->prepare("UPDATE users SET last_login_date = NOW() WHERE user_id = :user_id");
            $updateStmt->execute([':user_id' => $user['user_id']]);

            // Handle remember me functionality
            if ($remember) {
                // Generate a secure token for 'remember me'
                $token = bin2hex(random_bytes(32));
                setcookie('remember_token', $token, time() + 30 * 24 * 60 * 60, '/'); // 30 days
                
                // Store token in database for auto-login purposes
                $tokenStmt = $pdo->prepare("UPDATE users SET remember_token = :token WHERE user_id = :user_id");
                $tokenStmt->execute([
                    ':token' => $token,
                    ':user_id' => $user['user_id']
                ]);
            }

            // Redirect based on role
            if ($user['role'] === 'teacher') {
                header("Location: ../teacher/dashboard.php");
                exit();
            } else {
                header("Location: ../student/dashboard.php");
                exit();
            }

        } else {
            // Invalid username/password or user doesn't exist
            $_SESSION['error'] = "Invalid username/email or password.";
            header("Location: login.php");
            exit();
        }

    } catch(PDOException $e) {
        // Log error and display a generic message
        error_log("Login error: " . $e->getMessage());
        $_SESSION['error'] = "An error occurred. Please try again later.";
        header("Location: login.php");
        exit();
    }
} else {
    // If someone tries to access this file directly without POST data
    header("Location: login.php");
    exit();
}
?>
