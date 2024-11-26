<?php
session_start();
require_once '../../db/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../../auth/login.php');
    exit();
}

$quiz_id = $_GET['id'] ?? null;
if (!$quiz_id) {
    header('Location: dashboard.php');
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE quiz_id = :quiz_id AND teacher_id = :teacher_id");
$stmt->execute([
    ':quiz_id' => $quiz_id,
    ':teacher_id' => $_SESSION['user_id']
]);
$quiz = $stmt->fetch();

if (!$quiz) {
    header('Location: dashboard.php');
    exit();
}

$stmt = $pdo->prepare("SELECT 
    qa.*,
    u.username,
    u.email
    FROM quiz_attempts qa
    JOIN msasa_users u ON qa.student_id = u.user_id
    WHERE qa.quiz_id = :quiz_id
    ORDER BY qa.start_time DESC");
$stmt->execute([':quiz_id' => $quiz_id]);
$attempts = $stmt->fetchAll();

$total_attempts = count($attempts);
$total_score = 0;
$highest_score = 0;
$lowest_score = PHP_INT_MAX;

foreach($attempts as $attempt) {
    $total_score += $attempt['total_score'];
    $highest_score = max($highest_score, $attempt['total_score']);
    $lowest_score = min($lowest_score, $attempt['total_score']);
}

$average_score = $total_attempts > 0 ? round($total_score / $total_attempts, 1) : 0;
$lowest_score = $total_attempts > 0 ? $lowest_score : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Results - <?php echo htmlspecialchars($quiz['title']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Arimo:ital,wght@0,400..700;1,400..700&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">
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

        .results-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
        }

        .header {
            margin-bottom: 30px;
        }

        .header h1 {
            font-family: "DM Serif Display", serif;
            font-size: 2.5em;
            color: #EBE5D5;
            margin-bottom: 10px;
        }

        .quiz-info {
            color: #EBE5D5;
            margin-bottom: 20px;
            opacity: 0.8;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: #041f1f;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            text-align: center;
            border: 0.5px solid rgba(235, 229, 213, 0.2);
        }

        .stat-card h3 {
            font-size: 24px;
            color: #FECE63;
            margin-bottom: 5px;
            font-family: "DM Serif Display", serif;
        }

        .stat-card p {
            color: #EBE5D5;
            font-size: 14px;
        }

        .results-table {
            background: #041f1f;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            overflow: hidden;
            border: 0.5px solid rgba(235, 229, 213, 0.2);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid rgba(235, 229, 213, 0.1);
        }

        th {
            background-color: #052B2B;
            font-weight: 600;
            color: #FECE63;
        }

        tr:hover {
            background-color: #052B2B;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
            font-weight: 500;
        }

        .btn-view {
            background: #052B2B;
            color: #EBE5D5;
            border: 1px solid #FECE63;
        }

        .btn-view:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .score-cell {
            font-weight: 600;
        }

        .high-score {
            color: #FECE63;
        }

        .medium-score {
            color: #EBE5D5;
        }

        .low-score {
            color: #e74c3c;
        }

        .date-cell {
            color: #EBE5D5;
            opacity: 0.8;
            font-size: 14px;
        }

        .empty-message {
            text-align: center;
            padding: 40px;
            color: #EBE5D5;
            font-style: italic;
        }

        .back-button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #041f1f;
            color: #EBE5D5;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #FECE63;
            transition: all 0.3s;
        }

        .back-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .results-container {
                padding: 10px;
            }

            th, td {
                padding: 10px;
            }

            .header h1 {
                font-size: 2em;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="results-container">
        <a href="dashboard.php" class="back-button">← Back to Dashboard</a>
        
        <div class="header">
            <h1><?php echo htmlspecialchars($quiz['title']); ?> - Results</h1>
            <div class="quiz-info">
                <p>Difficulty: <?php echo htmlspecialchars($quiz['difficulty_level']); ?></p>
                <p>Created: <?php echo date('F j, Y', strtotime($quiz['created_date'])); ?></p>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo $total_attempts; ?></h3>
                <p>Total Attempts</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $average_score; ?>%</h3>
                <p>Average Score</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $highest_score; ?>%</h3>
                <p>Highest Score</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $lowest_score; ?>%</h3>
                <p>Lowest Score</p>
            </div>
        </div>

        <div class="results-table">
            <?php if(empty($attempts)): ?>
                <div class="empty-message">
                    <p>No attempts yet for this quiz.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Score</th>
                            <th>Date</th>
                            <th>Time Taken</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($attempts as $attempt): ?>
                            <tr>
                                <td>
                                    <div><?php echo htmlspecialchars($attempt['username']); ?></div>
                                    <div style="font-size: 12px; color: #EBE5D5; opacity: 0.8;">
                                        <?php echo htmlspecialchars($attempt['email']); ?>
                                    </div>
                                </td>
                                <td class="score-cell <?php 
                                    if($attempt['total_score'] >= 80) echo 'high-score';
                                    else if($attempt['total_score'] >= 60) echo 'medium-score';
                                    else echo 'low-score';
                                ?>">
                                    <?php echo $attempt['total_score']; ?>%
                                </td>
                                <td class="date-cell">
                                    <?php echo date('M d, Y g:i A', strtotime($attempt['start_time'])); ?>
                                </td>
                                <td><?php 
                                    $start = new DateTime($attempt['start_time']);
                                    $end = new DateTime($attempt['end_time']);
                                    $interval = $start->diff($end);
                                    echo $interval->format('%H:%I:%S');
                                ?></td>
                                <td>
                                    <?php echo $attempt['completion_status'] ? 'Completed' : 'In Progress'; ?>
                                </td>
                                <td>
                                    <a href="view_attempt.php?id=<?php echo $attempt['attempt_id']; ?>" class="btn btn-view">View Details</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
