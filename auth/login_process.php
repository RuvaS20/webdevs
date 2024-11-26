<?php
session_start();
require_once '../db/database.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;

    try {
        $stmt = $pdo->prepare("SELECT * FROM msasa_users WHERE username = :username OR email = :email");
        $stmt->execute([
            ':username' => $username,
            ':email' => $username
        ]);
        
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['success'] = "Welcome back, " . $user['username'];  
            $updateStmt = $pdo->prepare("UPDATE msasa_users SET last_login_date = NOW() WHERE user_id = :user_id");
            $updateStmt->execute([':user_id' => $user['user_id']]);

            if ($remember) {
                $token = bin2hex(random_bytes(32));
                setcookie('remember_token', $token, time() + 30 * 24 * 60 * 60, '/'); 
                $tokenStmt = $pdo->prepare("UPDATE msasa_users SET remember_token = :token WHERE user_id = :user_id");
                $tokenStmt->execute([
                    ':token' => $token,
                    ':user_id' => $user['user_id']
                ]);
            }

            if ($user['role'] === 'teacher') {
                header("Location: ../view/teacher/dashboard.php");
                exit();
            } 
            else if ($user['role'] === 'admin') {
                header("Location: ../view/admin/dashboard.php");
            }
            else {
                header("Location: ../view/student/dashboard.php");
                exit();
            }

        } else {
            $_SESSION['error'] = "Invalid username/email or password.";
            header("Location: login.php");
            exit();
        }

    } catch(PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        $_SESSION['error'] = "An error occurred. Please try again later.";
        header("Location: login.php");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
?>
