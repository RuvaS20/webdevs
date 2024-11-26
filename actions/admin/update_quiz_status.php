<?php
session_start();
require_once '../../db/database.php';
require_once '../../functions/admin_functions.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit('Unauthorized');
}

$quiz_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$status = filter_input(INPUT_GET, 'status', FILTER_VALIDATE_INT);

if (!$quiz_id || !in_array($status, [0, 1])) {
    $_SESSION['error'] = "Invalid input parameters.";
    header('Location: ../../view/admin/manage_quizzes.php');
    exit();
}

try {
    $adminFunctions = new AdminFunctions($pdo);
    $updated = $adminFunctions->updateQuizStatus($quiz_id, $status);

    if ($updated) {
        $_SESSION['success'] = "Quiz status updated successfully.";
    } else {
        $_SESSION['error'] = "Failed to update quiz status.";
    }
} catch (PDOException $e) {
    error_log("Error updating quiz status: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred while updating the quiz.";
}

header('Location: ../../view/admin/manage_quizzes.php');
exit();
?>