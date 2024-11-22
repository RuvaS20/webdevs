<?php
session_start();
require_once '../db/database.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../auth/login.php');
    exit();
}

$teacher_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();

        // Create upload directories if they don't exist
        $quiz_upload_dir = __DIR__ . '/../uploads/quiz_images';
        $question_upload_dir = __DIR__ . '/../uploads/question_images';

        if (!file_exists($quiz_upload_dir)) {
            mkdir($quiz_upload_dir, 0755, true);
        }
        if (!file_exists($question_upload_dir)) {
            mkdir($question_upload_dir, 0755, true);
        }

        // Handle Quiz Image Upload
        $image_url = '';
        if (isset($_FILES['quiz_image']) && $_FILES['quiz_image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['quiz_image']['name'];
            $filetype = pathinfo($filename, PATHINFO_EXTENSION);
            
            if (in_array(strtolower($filetype), $allowed)) {
                $new_filename = uniqid('quiz_') . '.' . $filetype;
                $upload_file = $quiz_upload_dir . '/' . $new_filename;
                
                if (move_uploaded_file($_FILES['quiz_image']['tmp_name'], $upload_file)) {
                    $image_url = 'uploads/quiz_images/' . $new_filename;
                }
            }
        }

        // Create Quiz
        $quiz_sql = "INSERT INTO quizzes (teacher_id, title, description, difficulty_level, image_url, created_date, is_active) 
                     VALUES (:teacher_id, :title, :description, :difficulty_level, :image_url, NOW(), 1)";
        $quiz_stmt = $pdo->prepare($quiz_sql);
        $quiz_stmt->execute([
            ':teacher_id' => $teacher_id,
            ':title' => $_POST['title'] ?? '',
            ':description' => $_POST['description'] ?? '',
            ':difficulty_level' => $_POST['difficulty_level'] ?? 'medium',
            ':image_url' => $image_url
        ]);
        
        $quiz_id = $pdo->lastInsertId();

        // Process Questions
        if (isset($_POST['questions']) && is_array($_POST['questions'])) {
            foreach ($_POST['questions'] as $index => $question) {
                if (empty($question['text'])) continue;

                // Handle options and correct answer based on question type
                $options_json = null;
                $correct_answer = '';
                
                switch($question['type']) {
                    case 'multiple_choice':
                        if (isset($question['options'])) {
                            $options_json = json_encode($question['options']);
                            $correct_answer = $question['correct'] ?? '';
                        }
                        break;
                    case 'true_false':
                        $options_json = json_encode(['True', 'False']);
                        $correct_answer = $question['correct'] ?? 'false';
                        break;
                    case 'short_answer':
                        $correct_answer = $question['correct_answer'] ?? '';
                        break;
                }

                // Insert question
                $q_sql = "INSERT INTO quiz_questions (
                    quiz_id, 
                    question_text, 
                    question_type, 
                    correct_answer, 
                    points, 
                    order_number, 
                    options,
                    image_url
                ) VALUES (
                    :quiz_id, 
                    :text, 
                    :type, 
                    :correct_answer, 
                    :points, 
                    :order_number, 
                    :options,
                    :image_url
                )";
                
                $q_stmt = $pdo->prepare($q_sql);
                $q_stmt->execute([
                    ':quiz_id' => $quiz_id,
                    ':text' => $question['text'],
                    ':type' => $question['type'],
                    ':correct_answer' => $correct_answer,
                    ':points' => $question['points'] ?? 1,
                    ':order_number' => $index + 1,
                    ':options' => $options_json,
                    ':image_url' => null
                ]);

                // Handle question image
                if (isset($_FILES['questions']['name'][$index]['image']) && 
                    $_FILES['questions']['error'][$index]['image'] == 0) {
                    
                    $q_image = $_FILES['questions']['name'][$index]['image'];
                    $new_q_filename = uniqid('question_') . '_' . basename($q_image);
                    $q_image_path = $question_upload_dir . '/' . $new_q_filename;
                    
                    if (move_uploaded_file($_FILES['questions']['tmp_name'][$index]['image'], $q_image_path)) {
                        $image_db_path = 'uploads/question_images/' . $new_q_filename;
                        $update_img_sql = "UPDATE quiz_questions SET image_url = :image_url 
                                         WHERE quiz_id = :quiz_id AND order_number = :order_number";
                        $img_stmt = $pdo->prepare($update_img_sql);
                        $img_stmt->execute([
                            ':image_url' => $image_db_path,
                            ':quiz_id' => $quiz_id,
                            ':order_number' => $index + 1
                        ]);
                    }
                }
            }
        }

        $pdo->commit();
        $_SESSION['success'] = "Quiz created successfully!";
        header("Location: dashboard.php");
        exit();

    } catch (Exception $e) {
        if (isset($pdo)) {
            $pdo->rollBack();
        }
        $_SESSION['error'] = "Error creating quiz: " . $e->getMessage();
        error_log("Quiz creation error: " . $e->getMessage());
        header("Location: create-quiz.php");
        exit();
    }
} else {
    header("Location: create-quiz.php");
    exit();
}
