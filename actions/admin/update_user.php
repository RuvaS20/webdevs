<?php
session_start();
require_once '../../db/database.php';
require_once '../../functions/admin_functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit('Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING));
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING);
    $isActive = filter_input(INPUT_POST, 'is_active', FILTER_VALIDATE_INT);
    if (!$userId || !$username || !$email || !in_array($role, ['student', 'teacher'])) {
        $_SESSION['error'] = "Invalid input data provided.";
        header('Location: ../../view/admin/manage_users.php');
        exit();
    }
    $stmt = $pdo->prepare("SELECT role FROM msasa_users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    if ($user['role'] === 'admin') {
        $_SESSION['error'] = "Admin user cannot be modified.";
        header('Location: ../../view/admin/manage_users.php');
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT user_id FROM msasa_users WHERE username = ? AND user_id != ?");
        $stmt->execute([$username, $userId]);
        if ($stmt->rowCount() > 0) {
            $_SESSION['error'] = "Username is already taken.";
            header('Location: ../../view/admin/manage_users.php');
            exit();
        }
        $stmt = $pdo->prepare("SELECT user_id FROM msasa_users WHERE email = ? AND user_id != ?");
        $stmt->execute([$email, $userId]);
        if ($stmt->rowCount() > 0) {
            $_SESSION['error'] = "Email is already taken.";
            header('Location: ../../view/admin/manage_users.php');
            exit();
        }

        $adminFunctions = new AdminFunctions($pdo);
        $updated = $adminFunctions->updateUser($userId, [
            'username' => $username,
            'email' => $email,
            'role' => $role,
            'is_active' => $isActive
        ]);

        if ($updated) {
            $_SESSION['success'] = "User updated successfully.";
        } else {
            $_SESSION['error'] = "Failed to update user.";
        }
    } catch (PDOException $e) {
        error_log("Error updating user: " . $e->getMessage());
        $_SESSION['error'] = "An error occurred while updating the user.";
    }
} else {
    $_SESSION['error'] = "Invalid request method.";
}

header('Location: ../../view/admin/manage_users.php');
exit();