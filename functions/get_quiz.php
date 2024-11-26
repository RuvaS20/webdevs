<?php
session_start();
require_once '../db/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') 
{
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['quiz_id'])) 
{
    echo json_encode(['success' => false, 'error' => 'Quiz ID not provided']);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM quizzes WHERE quiz_id = ? AND teacher_id = ?");
    $stmt->execute([$_GET['quiz_id'], $_SESSION['user_id']]);
    $quiz = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$quiz) 
    {
        echo json_encode(['success' => false, 'error' => 'Quiz not found']);
        exit();
    }
    $stmt = $pdo->prepare("SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY order_number");
    $stmt->execute([$_GET['quiz_id']]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'quiz' => $quiz,
        'questions' => $questions
    ]);

} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
