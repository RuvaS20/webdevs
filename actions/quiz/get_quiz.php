<?php
session_start();
require_once '../../db/database.php';

header('Content-Type: application/json');

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if quiz ID is provided
if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Quiz ID is required']);
    exit;
}

$quizId = (int)$_GET['id'];

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Get quiz details
    $stmt = $conn->prepare("
        SELECT * FROM quizzes 
        WHERE quiz_id = ? AND teacher_id = ?
    ");
    
    $stmt->bind_param("ii", $quizId, $_SESSION['user_id']);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $quiz = $result->fetch_assoc();

    if (!$quiz) {
        echo json_encode(['success' => false, 'message' => 'Quiz not found']);
        exit;
    }

    // Get questions for this quiz
    $stmt = $conn->prepare("
        SELECT * FROM quiz_questions 
        WHERE quiz_id = ? 
        ORDER BY order_number
    ");
    
    $stmt->bind_param("i", $quizId);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $questions = $result->fetch_all(MYSQLI_ASSOC);

    // Return both quiz and questions data
    echo json_encode([
        'success' => true,
        'quiz' => $quiz,
        'questions' => $questions
    ]);

} catch (Exception $e) {
    error_log("Error fetching quiz data: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>