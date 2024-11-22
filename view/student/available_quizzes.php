<?php
session_start();
require_once '../../db/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../../auth/login.php');
    exit();
}

// Get available quizzes
$stmt = $pdo->query("SELECT q.*, u.username as teacher_name 
                     FROM quizzes q 
                     JOIN users u ON q.teacher_id = u.user_id 
                     WHERE q.is_active = 1 
                     ORDER BY q.created_date DESC");
$quizzes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Quizzes</title>
    <style>
        .quiz-list {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }
        .quiz-card {
            border: 1px solid #ddd;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .start-btn {
            background: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="quiz-list">
        <h2>Available Quizzes</h2>
        <?php foreach ($quizzes as $quiz): ?>
            <div class="quiz-card">
                <h3><?php echo htmlspecialchars($quiz['title']); ?></h3>
                <p><?php echo htmlspecialchars($quiz['description']); ?></p>
                <p>Difficulty: <?php echo htmlspecialchars($quiz['difficulty_level']); ?></p>
                <p>Created by: <?php echo htmlspecialchars($quiz['teacher_name']); ?></p>
                <a href="take-quiz.php?id=<?php echo $quiz['quiz_id']; ?>" class="start-btn">Start Quiz</a>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
