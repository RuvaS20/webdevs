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

// Calculate statistics
$completed_attempts = 0;
$total_score = 0;
$highest_score = 0;

foreach($attempts as $attempt) {
    if($attempt['completion_status'] == 1) {
        $completed_attempts++;
        $total_score += (float)$attempt['total_score'];
        $highest_score = max($highest_score, (float)$attempt['total_score']);
    }
}

$average_score = $completed_attempts > 0 ? $total_score / $completed_attempts : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f4f6f8;
            color: #2c3e50;
            line-height: 1.6;
        }

        .dashboard {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            color: #2c3e50;
            margin: 0;
            font-size: 24px;
        }

        .logout-btn {
            color: #666;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .logout-btn:hover {
            background-color: #f0f0f0;
        }

        .welcome-section {
            background: #d1fae5;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .welcome-section h2 {
            color: #065f46;
            margin-bottom: 10px;
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

        .stat-card h3 {
            font-size: 32px;
            color: #3498db;
            margin-bottom: 10px;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }

        .quiz-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .quiz-card h3 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 18px;
        }

        .quiz-card p {
            color: #666;
            margin-bottom: 8px;
        }

        .start-quiz-btn {
            display: inline-block;
            background: #3498db;
            color: white;
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s;
            margin-top: 10px;
        }

        .start-quiz-btn:hover {
            background: #2980b9;
        }

        .attempt-card {
            background: #fff3cd;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
        }

        .attempt-card .score {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin: 10px 0;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
        }

        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
        }

        @media (max-width: 768px) {
            .stats-grid, .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="header">
            <h1>Student Dashboard</h1>
            <a href="../forum/index.php">Forum</a>
            <a href="../../auth/logout.php" class="logout-btn">Logout</a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']); 
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']); 
                ?>
            </div>
        <?php endif; ?>

        <div class="welcome-section">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
            <p>Ready to learn? Check out the available quizzes below.</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo $completed_attempts; ?></h3>
                <p>Quizzes Completed</p>
            </div>
            <div class="stat-card">
                <h3><?php echo number_format($average_score, 1); ?>%</h3>
                <p>Average Score</p>
            </div>
            <div class="stat-card">
                <h3><?php echo number_format($highest_score, 1); ?>%</h3>
                <p>Highest Score</p>
            </div>
        </div>

        <div class="dashboard-grid">
            <div>
                <h2>Available Quizzes</h2>
                <?php if(empty($quizzes)): ?>
                    <p>No quizzes available at the moment.</p>
                <?php else: ?>
                    <?php foreach($quizzes as $quiz): ?>
                        <div class="quiz-card">
                            <?php if(!empty($quiz['image_url'])): ?>
                                <img src="../<?php echo htmlspecialchars($quiz['image_url']); ?>" 
                                     alt="Quiz Cover" style="max-width: 200px; margin-bottom: 10px;">
                            <?php endif; ?>
                            <h3><?php echo htmlspecialchars($quiz['title']); ?></h3>
                            <p><?php echo htmlspecialchars($quiz['description']); ?></p>
                            <p><strong>Difficulty:</strong> <?php echo htmlspecialchars($quiz['difficulty_level']); ?></p>
                            <p><strong>Created by:</strong> <?php echo htmlspecialchars($quiz['teacher_name']); ?></p>
                            <a href="take_quiz.php?id=<?php echo $quiz['quiz_id']; ?>" class="start-quiz-btn">Start Quiz</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div>
                <h2>My Recent Attempts</h2>
                <?php if(empty($attempts)): ?>
                    <p>You haven't taken any quizzes yet.</p>
                <?php else: ?>
                    <?php foreach($attempts as $attempt): ?>
                        <div class="attempt-card">
                            <h3><?php echo htmlspecialchars($attempt['quiz_title']); ?></h3>
                            <div class="score"><?php echo number_format($attempt['total_score'], 1); ?>%</div>
                            <p>Date: <?php echo date('M d, Y H:i', strtotime($attempt['start_time'])); ?></p>
                            <p>Status: <?php echo $attempt['completion_status'] == 1 ? 'Completed' : 'In Progress'; ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
