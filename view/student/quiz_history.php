<?php
session_start();
require_once '../../db/database.php';

// Check if the user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../../auth/login.php');
    exit();
}

// Fetch the student's quiz history
$stmt = $pdo->prepare("
    SELECT 
        qa.attempt_id,
        q.title AS quiz_title,
        q.difficulty_level,
        qa.total_score,
        qa.completion_status,
        qa.start_time,
        qa.end_time
    FROM 
        quiz_attempts qa
    JOIN 
        quizzes q ON qa.quiz_id = q.quiz_id
    WHERE 
        qa.student_id = :student_id
    ORDER BY 
        qa.start_time DESC
");
$stmt->execute([':student_id' => $_SESSION['user_id']]);
$quiz_history = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz History</title>
    <style>
        body {
            background-color: #052B2B;
            color: #EBE5D5;
            font-family: "Arimo", sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #041F1F;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }

        h1 {
            text-align: center;
            color: #FECE63;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid rgba(235, 229, 213, 0.2);
        }

        th {
            color: #FECE63;
        }

        .status {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.9rem;
            font-weight: bold;
        }

        .completed {
            background-color: #4CAF50;
            color: white;
        }

        .in-progress {
            background-color: #E57373;
            color: white;
        }

        .no-history {
            text-align: center;
            margin: 20px 0;
            font-size: 1.1rem;
            color: #FECE63;
        }

        a {
            color: #FECE63;
            text-decoration: none;
            font-weight: bold;
        }

        a:hover {
            text-decoration: underline;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Quiz History</h1>

        <?php if (empty($quiz_history)): ?>
            <p class="no-history">You haven't taken any quizzes yet.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Quiz Title</th>
                        <th>Difficulty</th>
                        <th>Score</th>
                        <th>Status</th>
                        <th>Date Taken</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($quiz_history as $quiz): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($quiz['quiz_title']); ?></td>
                            <td><?php echo htmlspecialchars($quiz['difficulty_level']); ?></td>
                            <td><?php echo $quiz['completion_status'] ? $quiz['total_score'] . '%' : 'N/A'; ?></td>
                            <td>
                                <span class="status <?php echo $quiz['completion_status'] ? 'completed' : 'in-progress'; ?>">
                                    <?php echo $quiz['completion_status'] ? 'Completed' : 'In Progress'; ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($quiz['start_time'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <a href="dashboard.php" class="back-link">Back to Dashboard</a>
    </div>
</body>
</html>
