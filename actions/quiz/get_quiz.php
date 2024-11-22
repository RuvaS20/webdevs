<?php
require_once '../../db/database.php';
require_once '../../utils/session_helper.php';

if (!isTeacher()) {
    http_response_code(403);
    exit('Unauthorized');
}

$quiz_id = $_GET['id'] ?? null;
if (!$quiz_id) {
    http_response_code(400);
    exit('Quiz ID required');
}

try {
    // Get quiz details
    $stmt = $pdo->prepare("
        SELECT * FROM quizzes 
        WHERE quiz_id = ? AND teacher_id = ?
    ");
    $stmt->execute([$quiz_id, $_SESSION['user_id']]);
    $quiz = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$quiz) {
        http_response_code(404);
        exit('Quiz not found');
    }
    
    // Get questions
    $stmt = $pdo->prepare("
        SELECT * FROM quiz_questions 
        WHERE quiz_id = ? 
        ORDER BY order_number
    ");
    $stmt->execute([$quiz_id]);
    $quiz['questions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($quiz);
    
} catch(PDOException $e) {
    http_response_code(500);
    exit('Database error');
}
?>