<?php
session_start();
require_once '../../db/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$quiz_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$quiz_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid quiz ID']);
    exit();
}

try {
    
    $stmt = $pdo->prepare("
        SELECT 
            q.*,
            u.username as teacher_name,
            COUNT(DISTINCT qq.question_id) as question_count,
            COUNT(DISTINCT qa.attempt_id) as attempt_count,
            AVG(qa.total_score) as average_score
        FROM quizzes q
        LEFT JOIN msasa_users u ON q.teacher_id = u.user_id
        LEFT JOIN quiz_questions qq ON q.quiz_id = qq.quiz_id
        LEFT JOIN quiz_attempts qa ON q.quiz_id = qa.quiz_id
        WHERE q.quiz_id = ?
        GROUP BY q.quiz_id
    ");
    
    $stmt->execute([$quiz_id]);
    $quiz = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$quiz) {
        echo json_encode(['success' => false, 'message' => 'Quiz not found']);
        exit();
    }

   
    $quiz['average_score'] = $quiz['average_score'] ? round($quiz['average_score'], 1) : 0;

    echo json_encode(['success' => true, 'quiz' => $quiz]);

} catch (PDOException $e) {
    error_log("Error getting quiz details: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>