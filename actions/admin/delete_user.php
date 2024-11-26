<?php
session_start();
require_once '../../db/database.php';
require_once '../../functions/admin_functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit('Unauthorized');
}

$userId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$userId) {
    $_SESSION['error'] = "Invalid user ID provided.";
    header('Location: ../../view/admin/manage_users.php');
    exit();
}

try {
    error_log("Starting user deletion process in delete_user.php for ID: " . $userId);
    
  
    $adminFunctions = new AdminFunctions($pdo);
    
    $stmt = $pdo->prepare("SELECT user_id, role FROM msasa_users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    error_log("User data found: " . print_r($user, true));

    if (!$user) {
        throw new Exception("User not found.");
    }

    if ($user['role'] === 'admin') {
        throw new Exception("Admin user cannot be deleted.");
    }

    if ($userId == $_SESSION['user_id']) {
        throw new Exception("You cannot delete your own account.");
    }

    
    error_log("Calling deleteUser method");
    $deleted = $adminFunctions->deleteUser($userId);
    error_log("Delete operation result: " . ($deleted ? 'success' : 'failed'));

    if ($deleted) {
        $_SESSION['success'] = "User and all associated data deleted successfully.";
    } else {
        throw new Exception("Failed to delete user. Please check error logs for details.");
    }

} catch (PDOException $e) {
    error_log("PDO Error in delete_user.php: " . $e->getMessage());
    error_log("SQL State: " . $e->getCode());
    $_SESSION['error'] = "Database error occurred while deleting the user.";
} catch (Exception $e) {
    error_log("Exception in delete_user.php: " . $e->getMessage());
    $_SESSION['error'] = $e->getMessage();
}

header('Location: ../../view/admin/manage_users.php');
exit();