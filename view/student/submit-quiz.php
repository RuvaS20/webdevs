<?php
session_start();
require_once '../../db/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    try {
        $pdo->beginTransaction();

        // Create quiz attempt
        $stmt = $pdo->prepare("INSERT INTO quiz_attempts (student_id, quiz_id, start_time, end_time) 
                              VALUES (:student_id, :quiz_id, :start_time, NOW())");
        
        $stmt->execute([
            ':student_id' => $_SESSION['user_id'],
            ':quiz_id' => $_POST['quiz_id'],
            ':start_time' => date('Y-m-d H:i:s')
        ]);

        $attempt_id = $pdo->lastInsertId();
        $total_score = 0;

        // Process each answer
        foreach ($_POST['answers'] as $question_id => $answer) {
            // Get correct answer and points
            $stmt = $pdo->prepare("SELECT correct_answer, points FROM quiz_questions WHERE question_id = :question_id");
            $stmt->execute([':question_id' => $question_id]);
            $question = $stmt->fetch();

            $is_correct = strtolower($answer) === strtolower($question['correct_answer']);
            $points_earned = $is_correct ? $question['points'] : 0;
            $total_score += $points_earned;

            // Save response
            $stmt = $pdo->prepare("INSERT INTO quiz_responses (attempt_id, question_id, user_answer, is_correct, points_earned) 
                                  VALUES (:attempt_id, :question_id, :user_answer, :is_correct, :points_earned)");
            
            $stmt->execute([
                ':attempt_id' => $attempt_id,
                ':question_id' => $question_id,
                ':user_answer' => $answer,
                ':is_correct' => $is_correct,
                ':points_earned' => $points_earned
            ]);
        }

        // Update total score
        $stmt = $pdo->prepare("UPDATE quiz_attempts SET total_score = :total_score, completion_status = 1 
                              WHERE attempt_id = :attempt_id");
        $stmt->execute([
            ':total_score' => $total_score,
            ':attempt_id' => $attempt_id
        ]);

        $pdo->commit();
        $_SESSION['success'] = "Quiz submitted successfully! Your score: $total_score";
        header('Location: quizzes.php');
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Error submitting quiz: " . $e->getMessage();
        header('Location: quizzes.php');
        exit();
    }
}
?>
