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

// Get quiz details
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

// Get all attempts for this quiz
$stmt = $pdo->prepare("SELECT 
    qa.*,
    u.username,
    u.email
    FROM quiz_attempts qa
    JOIN users u ON qa.student_id = u.user_id
    WHERE qa.quiz_id = :quiz_id
    ORDER BY qa.start_time DESC");
$stmt->execute([':quiz_id' => $quiz_id]);
$attempts = $stmt->fetchAll();

// Calculate summary statistics
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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f4f6f8;
            color: #333;
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
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .quiz-info {
            color: #666;
            margin-bottom: 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-card h3 {
            font-size: 24px;
            color: #3c8f3c;
            margin-bottom: 5px;
        }

        .stat-card p {
            color: #666;
            font-size: 14px;
        }

        .results-table {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }

        tr:hover {
            background-color: #f8f9fa;
        }

        .btn {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .btn-view {
            background-color: #3c8f3c;
            color: white;
        }

        .btn-view:hover {
            background-color: #2d682d;
        }

        .score-cell {
            font-weight: 600;
        }

        .high-score {
            color: #2ecc71;
        }

        .medium-score {
            color: #f1c40f;
        }

        .low-score {
            color: #e74c3c;
        }

        .date-cell {
            color: #666;
            font-size: 14px;
        }

        .empty-message {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }

        .back-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #34495e;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
            transition: background-color 0.3s;
        }

        .back-button:hover {
            background-color: #2c3e50;
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
                font-size: 20px;
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
                                    <div style="font-size: 12px; color: #666;">
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
                                    <a href="view-attempt.php?id=<?php echo $attempt['attempt_id']; ?>" class="btn btn-view">View Details</a>
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
