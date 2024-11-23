<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../../db/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../../auth/login.php');
    exit();
}

if (isset($_GET['id'])) {
    $quiz_id = $_GET['id'];
    
    try {
        // First, let's check the actual tables in the database
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        error_log("Available tables: " . print_r($tables, true));
        
        // Verify the quiz belongs to this teacher
        $stmt = $pdo->prepare("SELECT teacher_id FROM quizzes WHERE quiz_id = :quiz_id");
        $stmt->execute([':quiz_id' => $quiz_id]);
        $quiz = $stmt->fetch();
        
        if ($quiz && $quiz['teacher_id'] == $_SESSION['user_id']) {
            $pdo->beginTransaction();
            
            // 1. Delete quiz attempts first
            $stmt = $pdo->prepare("DELETE FROM quiz_attempts WHERE quiz_id = :quiz_id");
            $stmt->execute([':quiz_id' => $quiz_id]);
            error_log("Deleted quiz attempts");
            
            // 2. Delete quiz questions
            $stmt = $pdo->prepare("DELETE FROM quiz_questions WHERE quiz_id = :quiz_id");
            $stmt->execute([':quiz_id' => $quiz_id]);
            error_log("Deleted quiz questions");
            
            // 3. Finally, delete the quiz itself
            $stmt = $pdo->prepare("DELETE FROM quizzes WHERE quiz_id = :quiz_id");
            $stmt->execute([':quiz_id' => $quiz_id]);
            error_log("Deleted quiz");
            
            $pdo->commit();
            $_SESSION['success'] = "Quiz and all related data deleted successfully";
            error_log("Transaction committed successfully");
        } else {
            $_SESSION['error'] = "You don't have permission to delete this quiz";
            error_log("Permission denied - quiz doesn't belong to teacher");
        }
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['error'] = "Error deleting quiz: " . $e->getMessage();
        error_log("Error deleting quiz: " . $e->getMessage());
    }
}

header('Location: ../../view/teacher/dashboard.php');
exit();
?>
