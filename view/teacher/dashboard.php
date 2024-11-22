<?php
session_start();
require_once '../../db/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../../auth/login.php');
    exit();
}

// Get teacher's quizzes with error checking
try {
    $stmt = $pdo->prepare("
        SELECT 
            q.quiz_id,
            q.title,
            q.description,
            q.difficulty_level,
            q.created_date,
            COUNT(DISTINCT qa.attempt_id) as attempt_count,
            AVG(qa.total_score) as average_score
        FROM quizzes q
        LEFT JOIN quiz_attempts qa ON q.quiz_id = qa.quiz_id
        WHERE q.teacher_id = :teacher_id
        GROUP BY q.quiz_id
        ORDER BY q.created_date DESC
    ");

    $stmt->execute([':teacher_id' => $_SESSION['user_id']]);
    $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);  // Added PDO::FETCH_ASSOC here

    // Get recent quiz attempts
    $stmt = $pdo->prepare("SELECT 
        qa.attempt_id,
        qa.total_score,
        qa.start_time,
        q.title as quiz_title,
        u.username as student_name
        FROM quiz_attempts qa
        JOIN quizzes q ON qa.quiz_id = q.quiz_id
        JOIN users u ON qa.student_id = u.user_id
        WHERE q.teacher_id = :teacher_id
        ORDER BY qa.start_time DESC
        LIMIT 5");
    $stmt->execute([':teacher_id' => $_SESSION['user_id']]);
    $recent_attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);  // Added PDO::FETCH_ASSOC here

} catch(PDOException $e) {
    echo "Database Error: " . $e->getMessage();
    die();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
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

        .header h1 {
            color: #333;
        }

        .create-quiz-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
        }

        .create-quiz-btn:hover {
            background-color: #45a049;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }

        .card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .card h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 1.5em;
        }

        .quiz-list {
            list-style: none;
        }

        .quiz-item {
            border: 1px solid #eee;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 10px;
        }

        .quiz-item h3 {
            color: #444;
            margin-bottom: 5px;
        }

        .quiz-stats {
            display: flex;
            gap: 20px;
            margin: 10px 0;
            color: #666;
            font-size: 0.9em;
        }

        .quiz-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .btn {
            padding: 5px 15px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9em;
        }

        .btn-view {
            background-color: #2196F3;
            color: white;
        }

        .btn-edit {
            background-color: #FFC107;
            color: #000;
        }

        .btn-delete {
            background-color: #f44336;
            color: white;
        }

        .attempt-list {
            list-style: none;
        }

        .attempt-item {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .attempt-item:last-child {
            border-bottom: none;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }

        .stat-card h3 {
            font-size: 2em;
            margin-bottom: 5px;
        }

        .stat-card p {
            font-size: 0.9em;
            opacity: 0.9;
        }

        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="header">
            <h1>Teacher Dashboard</h1>
            <a href="create-quiz.php" class="create-quiz-btn">Create New Quiz</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo count($quizzes); ?></h3>
                <p>Total Quizzes</p>
            </div>
            <div class="stat-card">
                <h3><?php 
                    $total_attempts = 0;
                    foreach($quizzes as $quiz) {
                        $total_attempts += $quiz['attempt_count'];
                    }
                    echo $total_attempts;
                ?></h3>
                <p>Total Attempts</p>
            </div>
            <div class="stat-card">
                <h3><?php 
                    $avg_score = 0;
                    $count = 0;
                    foreach($quizzes as $quiz) {
                        if($quiz['average_score']) {
                            $avg_score += $quiz['average_score'];
                            $count++;
                        }
                    }
                    echo $count ? round($avg_score/$count, 1) : 0;
                ?>%</h3>
                <p>Average Score</p>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="main-content">
                <div class="card">
                    <h2>Your Quizzes</h2>
                    <?php if(empty($quizzes)): ?>
                        <p>You haven't created any quizzes yet.</p>
                    <?php else: ?>
                        <ul class="quiz-list">
                            <?php foreach($quizzes as $quiz): ?>
                                <li class="quiz-item">
                                    <h3><?php echo htmlspecialchars($quiz['title']); ?></h3>
                                    <p><?php echo htmlspecialchars($quiz['description']); ?></p>
                                    <div class="quiz-stats">
                                        <span>Difficulty: <?php echo htmlspecialchars($quiz['difficulty_level']); ?></span>
                                        <span>Attempts: <?php echo $quiz['attempt_count']; ?></span>
                                        <span>Average Score: <?php echo $quiz['average_score'] ? round($quiz['average_score'], 1) : 0; ?>%</span>
                                    </div>
                                    <div class="quiz-actions">
                                        <a href="view-results.php?id=<?php echo $quiz['quiz_id']; ?>" class="btn btn-view">View Results</a>
                                        <a href="edit-quiz.php?id=<?php echo $quiz['quiz_id']; ?>" class="btn btn-edit">Edit Quiz</a>
                                        <button onclick="deleteQuiz(<?php echo $quiz['quiz_id']; ?>)" class="btn btn-delete">Delete</button>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>

            <div class="sidebar">
                <div class="card">
                    <h2>Recent Attempts</h2>
                    <?php if(empty($recent_attempts)): ?>
                        <p>No quiz attempts yet.</p>
                    <?php else: ?>
                        <ul class="attempt-list">
                            <?php foreach($recent_attempts as $attempt): ?>
                                <li class="attempt-item">
                                    <p><strong><?php echo htmlspecialchars($attempt['student_name']); ?></strong></p>
                                    <p>Quiz: <?php echo htmlspecialchars($attempt['quiz_title']); ?></p>
                                    <p>Score: <?php echo $attempt['total_score']; ?></p>
                                    <p>Date: <?php echo date('M d, Y', strtotime($attempt['start_time'])); ?></p>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
    function deleteQuiz(quizId) {
        if(confirm('Are you sure you want to delete this quiz? This action cannot be undone.')) {
            window.location.href = 'delete-quiz.php?id=' + quizId;
        }
    }
    </script>
</body>
</html>
