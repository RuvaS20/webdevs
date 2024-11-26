<?php
session_start();
require_once '../db/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        throw new Exception('Invalid data format');
    }

    $pdo->beginTransaction();
    $stmt = $pdo->prepare("
        INSERT INTO quizzes (teacher_id, title, description, difficulty_level, created_date, is_active) 
        VALUES (?, ?, ?, ?, NOW(), 1)
    ");
    
    $stmt->execute([
        $_SESSION['user_id'],
        $data['title'],
        $data['description'],
        $data['difficulty_level']
    ]);
    $quiz_id = $pdo->lastInsertId();
    if (!empty($data['questions'])) {
        $stmt = $pdo->prepare("
            INSERT INTO quiz_questions (
                quiz_id, 
                question_text, 
                question_type, 
                correct_answer,
                options,
                points, 
                order_number
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($data['questions'] as $index => $question) {
            $options = null;
            if ($question['type'] === 'multiple_choice') {
                $options = json_encode($question['options']);
            } elseif ($question['type'] === 'true_false') {
                $options = json_encode(['true', 'false']);
            }

            $stmt->execute([
                $quiz_id,
                $question['text'],
                $question['type'],
                $question['correct_answer'],
                $options,
                $question['points'],
                $index + 1
            ]);
        }
    }
    
    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Quiz created successfully']);

} catch (Exception $e) 
{
    $pdo->rollBack();
    error_log("Error creating quiz: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error creating quiz: ' . $e->getMessage()
    ]);
}
