<?php
session_start();
require_once '../db/database.php';
require_once 'register-actions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = sanitizeInput($_POST['role']);
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    $errors = [];
    
    // Validate input
    if (empty($username) || strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters long";
    }
    
    if (!validateEmail($email)) {
        $errors[] = "Please enter a valid email address";
    }
    
    if (empty($password) || strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if ($role !== 'teacher' && $role !== 'student') {
        $errors[] = "Invalid role selected";
    }
    
    if (empty($errors)) {
        $result = registerUser($pdo, $role, $username, $email, $password);
        
        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
            header("Location: login.php");
            exit();
        } else {
            $_SESSION['error'] = $result['message'];
            header("Location: register.php?role=" . urlencode($role));
            exit();
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
        header("Location: register.php?role=" . urlencode($role));
        exit();
    }
} else {
    header("Location: choose_role.php");
    exit();
}
if (empty($errors)) {
    $result = registerUser($pdo, $role, $username, $email, $password);
    
    if ($result['success']) {
        $_SESSION['success'] = "Registration successful! Please login with your credentials.";
        $_SESSION['registration_success'] = true;
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['error'] = $result['message'];
        header("Location: register.php?role=" . urlencode($role));
        exit();
    }
}
?>
