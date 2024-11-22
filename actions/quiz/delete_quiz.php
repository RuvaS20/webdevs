<?php
session_start();
require_once '../../db/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../../auth/login.php');
    exit();
}

if (isset($_GET['id'])) {
    $quiz_id = $_GET['id'];
    
    try {
        // Verify the quiz belongs to this teacher
        $stmt = $pdo->prepare("SELECT teacher_id FROM quizzes WHERE quiz_id = :quiz_id");
        $stmt->execute([':quiz_id' => $quiz_id]);
        $quiz = $stmt->fetch();
        
        if ($quiz && $quiz['teacher_id'] == $_SESSION['user_id']) {
            $pdo->beginTransaction();
            
            // Delete quiz and related records
            // Note: CASCADE should handle related records if set up in database
            $stmt = $pdo->prepare("DELETE FROM quizzes WHERE quiz_id = :quiz_id");
            $stmt->execute([':quiz_id' => $quiz_id]);
            
            $pdo->commit();
            $_SESSION['success'] = "Quiz deleted successfully";
        } else {
            $_SESSION['error'] = "You don't have permission to delete this quiz";
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Error deleting quiz";
        error_log("Error deleting quiz: " . $e->getMessage());
    }
}

header('Location: ../../view/teacher/dashboard.php');
exit();
?>
