<?php
session_start();
require_once '../../db/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../../auth/login.php');
    exit();
}

$stmt = $pdo->query("SELECT q.*, u.username as teacher_name 
                     FROM quizzes q 
                     JOIN msasa_users u ON q.teacher_id = u.user_id 
                     WHERE q.is_active = 1 
                     ORDER BY q.created_date DESC");
$quizzes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Available Quizzes | Msasa Academy</title>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Arimo:ital,wght@0,400..700;1,400..700&family=DM+Serif+Display:ital@0;1&display=swap');

            * {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
            }

            body {
                font-family: "Arimo", sans-serif;
                background-color: #052B2B;
                color: #EBE5D5;
                line-height: 1.6;
                min-height: 100vh;
            }

            .nav-bar {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 2rem 4rem;
                border-bottom: 0.5px solid rgba(235, 229, 213, 0.4);
                margin-bottom: 2rem;
            }

            .nav-bar h2 {
                color: #FECE63;
                font-family: "DM Serif Display", serif;
                font-weight: 400;
            }

            .nav-bar a {
                text-decoration: none;
            }

            .right-nav {
                display: flex;
                align-items: center;
                gap: 3rem;
            }

            .right-nav a {
                color: #EBE5D5;
                text-decoration: none;
                font-weight: 500;
                padding: 0.5rem 1rem;
                transition: color 0.3s ease;
                letter-spacing: 1px;
                position: relative;
                font-size: 18px;
            }

            .right-nav a::after {
                content: '';
                position: absolute;
                bottom: 0;
                left: 50%;
                transform: translateX(-50%);
                width: 0;
                height: 1px;
                background-color: #EBE5D5;
                transition: width 0.3s ease;
            }

            .right-nav a:hover::after {
                width: 80%;
            }

            .container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 2rem;
            }

            header {
                text-align: center;
                padding: 2rem 0 4rem;
            }

            h1,
            h2 {
                font-family: "DM Serif Display", serif;
                color: #FECE63;
            }

            h1 {
                font-size: 3rem;
                margin-bottom: 1rem;
            }

            .quiz-list {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
                gap: 2rem;
                padding: 0 1rem;
            }

            .quiz-card {
                background: rgba(235, 229, 213, 0.05);
                border-radius: 15px;
                padding: 2rem;
                backdrop-filter: blur(10px);
                border: 1px solid rgba(235, 229, 213, 0.1);
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }

            .quiz-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            }

            .quiz-card h3 {
                color: #FECE63;
                font-family: "DM Serif Display", serif;
                font-size: 1.5rem;
                margin-bottom: 1rem;
            }

            .quiz-card p {
                margin-bottom: 1rem;
                color: rgba(235, 229, 213, 0.8);
            }

            .quiz-meta {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin: 1.5rem 0;
                padding-top: 1rem;
                border-top: 1px solid rgba(235, 229, 213, 0.1);
            }

            .difficulty {
                padding: 0.5rem 1rem;
                border-radius: 20px;
                font-size: 0.9rem;
                background: rgba(254, 206, 99, 0.1);
                color: #FECE63;
            }

            .teacher {
                font-size: 0.9rem;
                color: rgba(235, 229, 213, 0.6);
            }

            .start-btn {
                display: inline-block;
                background: #FECE63;
                color: #052B2B;
                padding: 1rem 2rem;
                border: none;
                border-radius: 30px;
                cursor: pointer;
                text-decoration: none;
                font-weight: 500;
                transition: all 0.3s ease;
                text-align: center;
                width: 100%;
            }

            .start-btn:hover {
                background: #ffd780;
                transform: translateY(-2px);
            }

            .no-quizzes {
                text-align: center;
                padding: 4rem 2rem;
                background: rgba(235, 229, 213, 0.05);
                border-radius: 15px;
                border: 1px solid rgba(235, 229, 213, 0.1);
                margin: 2rem auto;
                max-width: 600px;
            }

            .no-quizzes h3 {
                color: #FECE63;
                font-family: "DM Serif Display", serif;
                font-size: 1.8rem;
                margin-bottom: 1rem;
            }

            .no-quizzes p {
                color: rgba(235, 229, 213, 0.8);
                font-size: 1.1rem;
                margin-bottom: 1.5rem;
            }

            .logout-btn {
                color: #FECE63;
                text-decoration: none;
                padding: 12px 24px;
                border-radius: 8px;
                border: 1px solid #FECE63;
                transition: all 0.3s;
            }
        </style>
    </head>

    <body>
        <nav class="nav-bar">
            <a href="../../index.html">
                <h2>Msasa.</h2>
            </a>
            <div class="right-nav">
                <a href="../forum/index.php">Forum</a>
                <a href="../news.php">News</a>
                <a href="dashboard.php">Profile</a>
                <a href="../../auth/logout.php" class="logout-btn">Logout</a>
            </div>
        </nav>

        <div class="container">
            <header>
                <h1>Available Quizzes</h1>
                <p>Test your knowledge with our interactive climate change quizzes</p>
            </header>

            <?php if (empty($quizzes)): ?>
            <div class="no-quizzes">
                <h3>No Quizzes Available</h3>
                <p>There are currently no quizzes available. Please check back later as our teachers regularly add new
                    content.</p>
            </div>
            <?php else: ?>
            <div class="quiz-list">
                <?php foreach ($quizzes as $quiz): ?>
                <div class="quiz-card">
                    <h3><?php echo htmlspecialchars($quiz['title']); ?></h3>
                    <p><?php echo htmlspecialchars($quiz['description']); ?></p>
                    <div class="quiz-meta">
                        <span class="difficulty"><?php echo htmlspecialchars($quiz['difficulty_level']); ?></span>
                        <span class="teacher">by <?php echo htmlspecialchars($quiz['teacher_name']); ?></span>
                    </div>
                    <a href="take_quiz.php?id=<?php echo $quiz['quiz_id']; ?>" class="start-btn">Start Quiz</a>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </body>
</html>
