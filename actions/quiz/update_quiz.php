<?php
require_once '../../db/database.php';
require_once '../../utils/session_helper.php';

if (!isTeacher()) {
    http_response_code(403);
    exit('Unauthorized');
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    http_response_code(400);
    exit('Invalid data');
}

try {
    $pdo->beginTransaction();
    
    // Update quiz details
    $stmt = $pdo->prepare("
        UPDATE quizzes 
        SET title = ?, description = ?, difficulty_level = ?
        WHERE quiz_id = ? AND teacher_id = ?
    ");
    $stmt->execute([
        $data['title'],
        $data['description'],
        $data['difficulty_level'],
        $data['quiz_id'],
        $_SESSION['user_id']
    ]);
    
    // Delete existing questions
    $stmt = $pdo->prepare("DELETE FROM quiz_questions WHERE quiz_id = ?");
    $stmt->execute([$data['quiz_id']]);
    
    // Insert updated questions
    $stmt = $pdo->prepare("
        INSERT INTO quiz_questions 
        (quiz_id, question_text, question_type, correct_answer, points, order_number)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    foreach ($data['questions'] as $index => $question) {
        $stmt->execute([
            $data['quiz_id'],
            $question['question_text'],
            $question['question_type'],
            $question['correct_answer'],
            $question['points'] ?? 1,
            $index + 1
        ]);
    }
    
    $pdo->commit();
    echo json_encode(['success' => true]);
    
} catch(PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    exit('Database error: ' . $e->getMessage());
}
?>
