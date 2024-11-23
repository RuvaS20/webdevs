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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Msasa Academy</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Arimo:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- Your CSS -->
    <style> 
        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Arimo", sans-serif;
            background-color: #052B2B;
            color: #EBE5D5;
            line-height: 1.6;
        }

        /* Dashboard Container */
        .dashboard {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 2rem 4rem;
        }

        /* Header Styles */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 2rem;
            border-radius: 20px;
            margin-bottom: 2rem;
            background: rgba(235, 229, 213, 0.05);
            border: 0.5px solid rgba(235, 229, 213, 0.2);
            backdrop-filter: blur(8px);
        }

        .header h1 {
            font-family: "DM Serif Display", serif;
            color: #FECE63;
            font-size: 2.5rem;
            margin: 0;
        }

        .header a {
            color: #EBE5D5;
            text-decoration: none;
            padding: 0.8rem 1.5rem;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .header a:hover {
            background: rgba(235, 229, 213, 0.1);
            transform: translateY(-2px);
        }

        .logout-btn {
            background-color: #FECE63;
            color: #3A4E3C !important;
        }

        .logout-btn:hover{
            background-color: #FECE63;
            opacity: 75%;
        }

        /* Welcome Section */
        .welcome-section {
            background: rgba(254, 206, 99, 0.1);
            padding: 2rem;
            border-radius: 20px;
            margin-bottom: 3rem;
            border: 0.5px solid rgba(254, 206, 99, 0.3);
        }

        .welcome-section h2 {
            color: #FECE63;
            font-family: "DM Serif Display", serif;
            margin-bottom: 1rem;
            font-size: 2rem;
        }

        .welcome-section p {
            color: #EBE5D5;
            opacity: 0.9;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: rgba(235, 229, 213, 0.05);
            padding: 2rem;
            border-radius: 20px;
            text-align: center;
            border: 0.5px solid rgba(235, 229, 213, 0.2);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card h3 {
            font-size: 2.5rem;
            color: #FECE63;
            margin-bottom: 1rem;
            font-family: "DM Serif Display", serif;
        }

        .stat-card p {
            color: #EBE5D5;
            opacity: 0.8;
        }

        /* Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }

        .dashboard-grid > div > h2 {
            color: #FECE63;
            font-family: "DM Serif Display", serif;
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
        }

        /* Quiz Cards */
        .quiz-card {
            background: rgba(235, 229, 213, 0.05);
            padding: 2rem;
            border-radius: 20px;
            margin-bottom: 1.5rem;
            border: 0.5px solid rgba(235, 229, 213, 0.2);
            transition: transform 0.3s ease;
        }

        .quiz-card:hover {
            transform: translateX(10px);
        }

        .quiz-card h3 {
            color: #FECE63;
            margin-bottom: 1rem;
            font-family: "DM Serif Display", serif;
            font-size: 1.5rem;
        }

        .quiz-card p {
            color: #EBE5D5;
            margin-bottom: 0.8rem;
            opacity: 0.9;
        }

        .quiz-card img {
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }

        .start-quiz-btn {
            display: inline-block;
            background: #FECE63;
            color: #3A4E3C;
            padding: 0.8rem 1.5rem;
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
            margin-top: 1rem;
        }

        .start-quiz-btn:hover {
            transform: translateY(-2px);
        }

        /* Attempt Cards */
        .attempt-card {
            background: rgba(254, 206, 99, 0.1);
            padding: 1.5rem;
            border-radius: 20px;
            margin-bottom: 1.5rem;
            border: 0.5px solid rgba(254, 206, 99, 0.2);
        }

        .attempt-card h3 {
            color: #FECE63;
            margin-bottom: 0.8rem;
            font-family: "DM Serif Display", serif;
        }

        .attempt-card .score {
            font-size: 2rem;
            font-weight: bold;
            color: #FECE63;
            margin: 1rem 0;
            font-family: "DM Serif Display", serif;
        }

        /* Alerts */
        .alert {
            padding: 1.5rem;
            margin-bottom: 2rem;
            border-radius: 20px;
            backdrop-filter: blur(8px);
        }

        .alert-success {
            background: rgba(209, 250, 229, 0.1);
            border: 0.5px solid rgba(209, 250, 229, 0.3);
            color: #EBE5D5;
        }

        .alert-error {
            background: rgba(254, 226, 226, 0.1);
            border: 0.5px solid rgba(254, 226, 226, 0.3);
            color: #EBE5D5;
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .dashboard {
                padding: 1rem;
            }

            .stats-grid, 
            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .header h1 {
                font-size: 2rem;
            }

            .stat-card h3 {
                font-size: 2rem;
            }

            .quiz-card:hover {
                transform: none;
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
