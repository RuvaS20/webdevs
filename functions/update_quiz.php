<?php
session_start();
require_once '../db/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit();
}

try {
    $pdo->beginTransaction();

    $quiz_id = $_POST['quiz_id'];
    $stmt = $pdo->prepare("UPDATE quizzes SET title = ?, description = ?, difficulty_level = ? WHERE quiz_id = ? AND teacher_id = ?");
    $stmt->execute([
        $_POST['title'],
        $_POST['description'],
        $_POST['difficulty_level'],
        $quiz_id,
        $_SESSION['user_id']
    ]);
    if (isset($_POST['questions']) && is_array($_POST['questions'])) {
        foreach ($_POST['questions'] as $index => $question) {
            if (isset($question['question_id'])) {
                $stmt = $pdo->prepare("UPDATE quiz_questions SET 
                    question_text = ?,
                    question_type = ?,
                    correct_answer = ?,
                    points = ?,
                    order_number = ?
                    WHERE question_id = ? AND quiz_id = ?");
                $stmt->execute([
                    $question['text'],
                    $question['type'],
                    $question['type'] === 'short_answer' ? $question['correct_answer'] : ($question['correct'] ?? ''),
                    $question['points'],
                    $index + 1,
                    $question['question_id'],
                    $quiz_id
                ]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO quiz_questions (
                    quiz_id, question_text, question_type, correct_answer, points, order_number
                ) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $quiz_id,
                    $question['text'],
                    $question['type'],
                    $question['type'] === 'short_answer' ? $question['correct_answer'] : ($question['correct'] ?? ''),
                    $question['points'],
                    $index + 1
                ]);
            }
        }
    }

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch(PDOException $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
