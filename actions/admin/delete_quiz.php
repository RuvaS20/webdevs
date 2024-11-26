<?php
session_start();
require_once '../../db/database.php';
require_once '../../functions/admin_functions.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit('Unauthorized');
}

$quiz_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$quiz_id) {
    $_SESSION['error'] = "Invalid quiz ID.";
    header('Location: ../../view/admin/manage_quizzes.php');
    exit();
}

try {
    $adminFunctions = new AdminFunctions($pdo);
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("SELECT quiz_id FROM quizzes WHERE quiz_id = ?");
    $stmt->execute([$quiz_id]);
    if (!$stmt->fetch()) {
        $_SESSION['error'] = "Quiz not found.";
        header('Location: ../../view/admin/manage_quizzes.php');
        exit();
    }

    $stmt = $pdo->prepare("
        DELETE qr FROM quiz_responses qr
        INNER JOIN quiz_attempts qa ON qr.attempt_id = qa.attempt_id
        WHERE qa.quiz_id = ?
    ");
    $stmt->execute([$quiz_id]);
    $stmt = $pdo->prepare("DELETE FROM quiz_attempts WHERE quiz_id = ?");
    $stmt->execute([$quiz_id]);

    
    $stmt = $pdo->prepare("DELETE FROM quiz_questions WHERE quiz_id = ?");
    $stmt->execute([$quiz_id]);

    
    $stmt = $pdo->prepare("DELETE FROM quizzes WHERE quiz_id = ?");
    $stmt->execute([$quiz_id]);

    
    $pdo->commit();
    $_SESSION['success'] = "Quiz and all associated data have been successfully deleted.";

} catch (PDOException $e) {
    
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Error deleting quiz: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred while deleting the quiz. Please try again.";
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("General error deleting quiz: " . $e->getMessage());
    $_SESSION['error'] = "An unexpected error occurred. Please try again.";
}
header('Location: ../../view/admin/manage_quizzes.php');
exit();