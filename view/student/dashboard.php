<?php
session_start();
require_once '../../db/database.php';

// Check if user is logged in and is a student
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

// Get student's quiz attempts
$stmt = $pdo->prepare("SELECT 
    qa.*, 
    q.title as quiz_title,
    q.difficulty_level
    FROM quiz_attempts qa
    JOIN quizzes q ON qa.quiz_id = q.quiz_id
    WHERE qa.student_id = :student_id
    ORDER BY qa.start_time DESC");
$stmt->execute([':student_id' => $_SESSION['user_id']]);
$attempts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f4f6f8;
        }

        .dashboard {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }

        .welcome-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .quiz-section {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }

        .available-quizzes, .my-attempts {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .quiz-card {
            border: 1px solid #eee;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .quiz-card h3 {
            margin-bottom: 10px;
            color: #333;
        }

        .start-quiz-btn {
            background: #4CAF50;
            color: white;
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
        }

        .attempt-card {
            border: 1px solid #eee;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 10px;
        }

        .score {
            font-size: 24px;
            font-weight: bold;
            color: #4CAF50;
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .quiz-section {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="header">
            <h1>Student Dashboard</h1>
            <a href="../auth/logout.php" style="color: #666; text-decoration: none;">Logout</a>
        </div>

        <div class="welcome-section">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
            <p>Ready to learn? Check out the available quizzes below.</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo count($attempts); ?></h3>
                <p>Quizzes Taken</p>
            </div>
            <div class="stat-card">
                <h3><?php 
                    $total_score = 0;
                    $completed_attempts = 0;
                    foreach($attempts as $attempt) {
                        if($attempt['completion_status']) {
                            $total_score += $attempt['total_score'];
                            $completed_attempts++;
                        }
                    }
                    echo $completed_attempts > 0 ? round($total_score / $completed_attempts) : 0;
                ?>%</h3>
                <p>Average Score</p>
            </div>
            <div class="stat-card">
                <h3><?php 
                    $highest_score = 0;
                    foreach($attempts as $attempt) {
                        $highest_score = max($highest_score, $attempt['total_score']);
                    }
                    echo $highest_score;
                ?>%</h3>
                <p>Highest Score</p>
            </div>
        </div>

        <div class="quiz-section">
            <div class="available-quizzes">
                <h2>Available Quizzes</h2>
                <?php if(empty($quizzes)): ?>
                    <p>No quizzes available at the moment.</p>
                <?php else: ?>
                    <?php foreach($quizzes as $quiz): ?>
                        <div class="quiz-card">
                            <h3><?php echo htmlspecialchars($quiz['title']); ?></h3>
                            <p><?php echo htmlspecialchars($quiz['description']); ?></p>
                            <p>Difficulty: <?php echo htmlspecialchars($quiz['difficulty_level']); ?></p>
                            <p>Created by: <?php echo htmlspecialchars($quiz['teacher_name']); ?></p>
                            <a href="take-quiz.php?id=<?php echo $quiz['quiz_id']; ?>" class="start-quiz-btn">Start Quiz</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="my-attempts">
                <h2>My Recent Attempts</h2>
                <?php if(empty($attempts)): ?>
                    <p>You haven't taken any quizzes yet.</p>
                <?php else: ?>
                    <?php foreach($attempts as $attempt): ?>
                        <div class="attempt-card">
                            <h3><?php echo htmlspecialchars($attempt['quiz_title']); ?></h3>
                            <p class="score"><?php echo $attempt['total_score']; ?>%</p>
                            <p>Date: <?php echo date('M d, Y', strtotime($attempt['start_time'])); ?></p>
                            <p>Status: <?php echo $attempt['completion_status'] ? 'Completed' : 'In Progress'; ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
