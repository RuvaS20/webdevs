<?php
session_start();
require_once '../../db/database.php';
require_once '../../functions/admin_functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit('Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Invalid request method.";
    header('Location: ../../view/admin/manage_quizzes.php');
    exit();
}

$quiz_id = filter_input(INPUT_POST, 'quiz_id', FILTER_VALIDATE_INT);
$title = trim(filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING));
$description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING));
$difficulty_level = filter_input(INPUT_POST, 'difficulty_level', FILTER_SANITIZE_STRING);

if (!$quiz_id || !$title || !in_array($difficulty_level, ['easy', 'medium', 'hard'])) {
    $_SESSION['error'] = "Invalid input data provided.";
    header('Location: ../../view/admin/manage_quizzes.php');
    exit();
}

try {
    
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("SELECT quiz_id, teacher_id FROM quizzes WHERE quiz_id = ?");
    $stmt->execute([$quiz_id]);
    $quiz = $stmt->fetch();

    if (!$quiz) {
        throw new Exception("Quiz not found.");
    }
   
    $stmt = $pdo->prepare("
        UPDATE quizzes 
        SET 
            title = ?,
            description = ?,
            difficulty_level = ?
        WHERE quiz_id = ?
    ");
    
    $stmt->execute([
        $title,
        $description,
        $difficulty_level,
        $quiz_id
    ]);

    
    if (isset($_POST['questions']) && is_array($_POST['questions'])) {
        
        $stmt = $pdo->prepare("SELECT question_id FROM quiz_questions WHERE quiz_id = ?");
        $stmt->execute([$quiz_id]);
        $existingQuestions = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $updatedQuestions = [];

        foreach ($_POST['questions'] as $index => $question) {
            $questionId = isset($question['question_id']) ? 
                         filter_input(INPUT_POST, "questions[$index][question_id]", FILTER_VALIDATE_INT) : 
                         null;

            $questionData = [
                'question_text' => trim($question['text']),
                'question_type' => $question['type'],
                'points' => isset($question['points']) ? (int)$question['points'] : 1,
                'order_number' => $index + 1
            ];

            if ($question['type'] === 'multiple_choice') {
                $questionData['correct_answer'] = $question['correct'];
                $questionData['options'] = json_encode($question['options']);
            } elseif ($question['type'] === 'true_false') {
                $questionData['correct_answer'] = $question['correct'];
                $questionData['options'] = null;
            } elseif ($question['type'] === 'short_answer') {
                $questionData['correct_answer'] = $question['correct_answer'];
                $questionData['options'] = null;
            }

            if ($questionId && in_array($questionId, $existingQuestions)) {
                
                $stmt = $pdo->prepare("
                    UPDATE quiz_questions 
                    SET 
                        question_text = ?,
                        question_type = ?,
                        correct_answer = ?,
                        options = ?,
                        points = ?,
                        order_number = ?
                    WHERE question_id = ? AND quiz_id = ?
                ");
                
                $stmt->execute([
                    $questionData['question_text'],
                    $questionData['question_type'],
                    $questionData['correct_answer'],
                    $questionData['options'],
                    $questionData['points'],
                    $questionData['order_number'],
                    $questionId,
                    $quiz_id
                ]);

                $updatedQuestions[] = $questionId;
            } else {

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
                
                $stmt->execute([
                    $quiz_id,
                    $questionData['question_text'],
                    $questionData['question_type'],
                    $questionData['correct_answer'],
                    $questionData['options'],
                    $questionData['points'],
                    $questionData['order_number']
                ]);
            }
        }

        
        $questionsToDelete = array_diff($existingQuestions, $updatedQuestions);
        if (!empty($questionsToDelete)) {
            $stmt = $pdo->prepare("
                DELETE qr FROM quiz_responses qr
                INNER JOIN quiz_attempts qa ON qr.attempt_id = qa.attempt_id
                WHERE qr.question_id IN (" . str_repeat('?,', count($questionsToDelete) - 1) . "?)
                AND qa.quiz_id = ?
            ");
            
            $params = array_merge($questionsToDelete, [$quiz_id]);
            $stmt->execute($params);
            $stmt = $pdo->prepare("
                DELETE FROM quiz_questions 
                WHERE question_id IN (" . str_repeat('?,', count($questionsToDelete) - 1) . "?)
                AND quiz_id = ?
            ");
            
            $stmt->execute($params);
        }
    }
    $stmt = $pdo->prepare("
        UPDATE quiz_attempts qa
        SET total_score = (
            SELECT (SUM(qr.points_earned) / SUM(qq.points)) * 100
            FROM quiz_responses qr
            JOIN quiz_questions qq ON qr.question_id = qq.question_id
            WHERE qr.attempt_id = qa.attempt_id
        )
        WHERE quiz_id = ?
    ");
    $stmt->execute([$quiz_id]);
    $pdo->commit();
    $_SESSION['success'] = "Quiz updated successfully.";

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Error updating quiz: " . $e->getMessage());
    $_SESSION['error'] = "Database error while updating quiz.";
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = $e->getMessage();
}

header('Location: ../../view/admin/manage_quizzes.php');
exit();
?>