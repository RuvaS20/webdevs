<?php
session_start();
require_once '../../db/config.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../../auth/login.php');
    exit();
}

// Get available quizzes with specific column selection
$stmt = $pdo->query("SELECT q.quiz_id, q.teacher_id, q.title, q.description, 
                     q.difficulty_level, q.created_date, q.is_active,
                     COALESCE(u.username, 'System') as teacher_name 
                     FROM quizzes q 
                     LEFT JOIN users u ON q.teacher_id = u.user_id 
                     WHERE q.is_active = 1 
                     ORDER BY q.created_date DESC");
$quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
$attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <link href="https://fonts.googleapis.com/css2?family=Arimo:ital,wght@0,400..700;1,400..700&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Arimo", sans-serif;
        }

        body {
            background-color: #052B2B;
            color: #EBE5D5;
            line-height: 1.6;
        }

        .dashboard {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: #041f1f;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 0.5px solid rgba(235, 229, 213, 0.2);
        }

        .header h1 {
            font-family: "DM Serif Display", serif;
            color: #EBE5D5;
            margin: 0;
            font-size: 2.5em;
        }

        .logout-btn {
            color: #EBE5D5;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 8px;
            border: 1px solid #FECE63;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            background: #FECE63;
            color: #041f1f;
        }

        .welcome-section {
            background: #041f1f;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            border: 0.5px solid rgba(235, 229, 213, 0.2);
        }

        .welcome-section h2 {
            font-family: "DM Serif Display", serif;
            color: #FECE63;
            margin-bottom: 10px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: #041f1f;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            border: 0.5px solid rgba(235, 229, 213, 0.2);
        }

        .stat-card h3 {
            font-size: 32px;
            color: #FECE63;
            margin-bottom: 10px;
            font-family: "DM Serif Display", serif;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }

        .dashboard-grid h2 {
            font-family: "DM Serif Display", serif;
            color: #FECE63;
            margin-bottom: 20px;
        }

        .quiz-card {
            background: #041f1f;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            border: 0.5px solid rgba(235, 229, 213, 0.2);
            transition: all 0.3s ease;
        }

        .quiz-card:hover {
            border-color: #FECE63;
            transform: translateY(-2px);
        }

        .quiz-card h3 {
            color: #FECE63;
            margin-bottom: 10px;
            font-size: 18px;
            font-family: "DM Serif Display", serif;
        }

        .quiz-card p {
            color: #EBE5D5;
            margin-bottom: 8px;
            opacity: 0.8;
        }

        .difficulty-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            margin-bottom: 8px;
            text-transform: capitalize;
        }

        .difficulty-easy {
            background-color: #2ecc71;
            color: #fff;
        }

        .difficulty-medium {
            background-color: #f1c40f;
            color: #2c3e50;
        }

        .difficulty-hard {
            background-color: #e74c3c;
            color: #fff;
        }

        .start-quiz-btn {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            border: 1px solid #FECE63;
            color: #EBE5D5;
            transition: all 0.3s;
            margin-top: 10px;
            background-color: transparent;
        }

        .start-quiz-btn:hover {
            background-color: #FECE63;
            color: #041f1f;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .attempt-card {
            background: #041f1f;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
            border: 0.5px solid rgba(235, 229, 213, 0.2);
            transition: all 0.3s ease;
        }

        .attempt-card:hover {
            border-color: #FECE63;
            transform: translateY(-2px);
        }

        .attempt-card h3 {
            color: #FECE63;
            font-family: "DM Serif Display", serif;
            font-size: 16px;
            margin-bottom: 8px;
        }

        .attempt-card .score {
            font-size: 24px;
            font-weight: bold;
            color: #FECE63;
            margin: 10px 0;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            border: 0.5px solid rgba(235, 229, 213, 0.2);
        }

        .alert-success {
            background-color: #041f1f;
            color: #FECE63;
            border-color: #FECE63;
        }

        .alert-error {
            background-color: #041f1f;
            color: #e74c3c;
            border-color: #e74c3c;
        }

        @media (max-width: 768px) {
            .stats-grid, .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .header h1 {
                font-size: 2em;
            }

            .dashboard {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="header">
            <h1>Student Dashboard</h1>
            <a href="../../auth/logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
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
                            <h3><?php echo htmlspecialchars($quiz['title']); ?></h3>
                            <p><?php echo htmlspecialchars($quiz['description']); ?></p>
                            <span class="difficulty-badge difficulty-<?php echo strtolower($quiz['difficulty_level']); ?>">
                                <?php echo ucfirst(htmlspecialchars($quiz['difficulty_level'])); ?>
                            </span>
                            <p><strong>Created by:</strong> <?php echo htmlspecialchars($quiz['teacher_name']); ?></p>
                            <a href="take_quiz.php?id=<?php echo $quiz['quiz_id']; ?>" class="start-quiz-btn">
                                <i class="fas fa-play"></i> Start Quiz
                            </a>
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
                            <div class="score">
                                <?php echo number_format($attempt['total_score'], 1); ?>%
                            </div>
                            <p>
                                <i class="far fa-calendar-alt"></i>
                                <?php echo date('M d, Y H:i', strtotime($attempt['start_time'])); ?>
                            </p>
                            <p>
                                <i class="fas fa-<?php echo $attempt['completion_status'] == 1 ? 'check-circle' : 'clock'; ?>"></i>
                                <?php echo $attempt['completion_status'] == 1 ? 'Completed' : 'In Progress'; ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
