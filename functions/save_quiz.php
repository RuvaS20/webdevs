<?php
session_start();
require_once '../db/database.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!$pdo) {
    die("Database connection failed");
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../auth/login.php');
    exit();
}

$teacher_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();
        
        // Create Quiz
        $quiz_sql = "INSERT INTO quizzes (
            teacher_id, 
            title, 
            description, 
            difficulty_level, 
            created_date, 
            is_active
        ) VALUES (
            :teacher_id,
            :title,
            :description,
            :difficulty_level,
            NOW(),
            1
        )";
        
        $quiz_stmt = $pdo->prepare($quiz_sql);
        $quiz_stmt->execute([
            ':teacher_id' => $teacher_id,
            ':title' => $_POST['title'] ?? '',
            ':description' => $_POST['description'] ?? '',
            ':difficulty_level' => $_POST['difficulty_level'] ?? 'medium'
        ]);
        
        $quiz_id = $pdo->lastInsertId();
        
        // Process Questions
        if (isset($_POST['questions']) && is_array($_POST['questions'])) {
            foreach ($_POST['questions'] as $index => $question) {
                if (empty($question['text'])) continue;
                
                $correct_answer = '';
                switch($question['type']) {
                    case 'multiple_choice':
                        $correct_answer = $question['correct'] ?? '';
                        break;
                    case 'true_false':
                        $correct_answer = $question['correct'] ?? 'false';
                        break;
                    case 'short_answer':
                        $correct_answer = $question['correct_answer'] ?? '';
                        break;
                }

                $q_sql = "INSERT INTO quiz_questions (
                    quiz_id,
                    question_text,
                    question_type,
                    correct_answer,
                    points,
                    order_number
                ) VALUES (
                    :quiz_id,
                    :text,
                    :type,
                    :correct_answer,
                    :points,
                    :order_number
                )";
                
                $q_stmt = $pdo->prepare($q_sql);
                $q_stmt->execute([
                    ':quiz_id' => $quiz_id,
                    ':text' => $question['text'],
                    ':type' => $question['type'],
                    ':correct_answer' => $correct_answer,
                    ':points' => $question['points'] ?? 1,
                    ':order_number' => $index + 1
                ]);
            }
        }
        
        $pdo->commit();
        $_SESSION['success'] = "Quiz created successfully!";
        header("Location: ../view/teacher/dashboard.php");
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Database Error: " . $e->getMessage());
        $_SESSION['error'] = "Error creating quiz: " . $e->getMessage();
        header("Location: ../view/teacher/create_quiz.php");
        exit();
    }
} else {
    header("Location: ../view/teacher/create_quiz.php");
    exit();
}
?>
