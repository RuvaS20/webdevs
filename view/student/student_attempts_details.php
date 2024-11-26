<?php
session_start();
require_once '../../db/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../../auth/login.php');
    exit();
}

$attempt_id = $_GET['id'] ?? null;
if (!$attempt_id) {
    header('Location: dashboard.php');
    exit();
}

$stmt = $pdo->prepare("
    SELECT 
        qa.*,
        q.title as quiz_title,
        q.difficulty_level,
        q.description
    FROM quiz_attempts qa
    JOIN quizzes q ON qa.quiz_id = q.quiz_id
    WHERE qa.attempt_id = :attempt_id
    AND qa.student_id = :student_id
");
$stmt->execute([
    ':attempt_id' => $attempt_id,
    ':student_id' => $_SESSION['user_id']
]);
$attempt = $stmt->fetch();

if (!$attempt) {
    header('Location: dashboard.php');
    exit();
}

$stmt = $pdo->prepare("
    SELECT 
        qr.*,
        qq.question_text,
        qq.question_type,
        qq.correct_answer,
        qq.points
    FROM quiz_responses qr
    JOIN quiz_questions qq ON qr.question_id = qq.question_id
    WHERE qr.attempt_id = :attempt_id
    ORDER BY qq.order_number
");
$stmt->execute([':attempt_id' => $attempt_id]);
$responses = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Attempt Details</title>
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

        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
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

        .header {
            background: #041f1f;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            border: 0.5px solid rgba(235, 229, 213, 0.2);
        }

        .header h1 {
            font-family: "DM Serif Display", serif;
            color: #EBE5D5;
            font-size: 2.5em;
            margin-bottom: 15px;
        }

        .quiz-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 20px;
        }

        .info-card {
            background: #052B2B;
            padding: 15px;
            border-radius: 8px;
            border: 0.5px solid rgba(235, 229, 213, 0.2);
        }

        .score-display {
            font-size: 36px;
            color: #FECE63;
            margin: 15px 0;
            font-family: "DM Serif Display", serif;
        }

        .questions-container {
            margin-top: 30px;
        }

        .question-card {
            background: #041f1f;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            border: 0.5px solid rgba(235, 229, 213, 0.2);
            transition: all 0.3s ease;
        }

        .question-card:hover {
            transform: translateY(-2px);
            border-color: #FECE63;
        }

        .question-card.correct {
            border-left: 4px solid #FECE63;
        }

        .question-card.incorrect {
            border-left: 4px solid #e74c3c;
        }

        .question-text {
            font-size: 1.1em;
            color: #FECE63;
            margin-bottom: 15px;
            font-family: "DM Serif Display", serif;
        }

        .response-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid rgba(235, 229, 213, 0.1);
        }

        .response-group {
            background: #052B2B;
            padding: 10px 15px;
            border-radius: 6px;
        }

        .response-group h4 {
            color: #FECE63;
            margin-bottom: 5px;
            font-size: 0.9em;
        }

        .response-text {
            font-size: 1.1em;
        }

        .correct-text {
            color: #FECE63;
        }

        .incorrect-text {
            color: #e74c3c;
        }

        .points-info {
            margin-top: 10px;
            font-size: 0.9em;
            color: #EBE5D5;
            opacity: 0.8;
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .quiz-info {
                grid-template-columns: 1fr;
            }

            .response-details {
                grid-template-columns: 1fr;
            }

            .header h1 {
                font-size: 2em;
            }

            .score-display {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="dashboard.php" class="back-button">← Back to Dashboard</a>

        <div class="header">
            <h1><?php echo htmlspecialchars($attempt['quiz_title']); ?></h1>
            <div class="quiz-info">
                <div class="info-card">
                    <h3>Quiz Details</h3>
                    <p><?php echo htmlspecialchars($attempt['description']); ?></p>
                    <p>Difficulty: <?php echo htmlspecialchars($attempt['difficulty_level']); ?></p>
                </div>
                <div class="info-card">
                    <h3>Your Results</h3>
                    <div class="score-display"><?php echo number_format($attempt['total_score'], 1); ?>%</div>
                    <p>Completed: <?php echo date('M d, Y g:i A', strtotime($attempt['end_time'])); ?></p>
                    <p>Time Taken: <?php 
                        $start = new DateTime($attempt['start_time']);
                        $end = new DateTime($attempt['end_time']);
                        $interval = $start->diff($end);
                        echo $interval->format('%H:%I:%S');
                    ?></p>
                </div>
            </div>
        </div>

        <div class="questions-container">
            <?php foreach($responses as $index => $response): ?>
                <div class="question-card <?php echo $response['is_correct'] ? 'correct' : 'incorrect'; ?>">
                    <div class="question-text">
                        Question <?php echo $index + 1; ?>: <?php echo htmlspecialchars($response['question_text']); ?>
                    </div>
                    
                    <div class="response-details">
                        <div class="response-group">
                            <h4>Your Answer</h4>
                            <div class="response-text <?php echo $response['is_correct'] ? 'correct-text' : 'incorrect-text'; ?>">
                                <?php echo htmlspecialchars($response['user_answer']); ?>
                            </div>
                        </div>
                        
                        <div class="response-group">
                            <h4>Correct Answer</h4>
                            <div class="response-text correct-text">
                                <?php echo htmlspecialchars($response['correct_answer']); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="points-info">
                        Points: <?php echo $response['points_earned']; ?> / <?php echo $response['points']; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
