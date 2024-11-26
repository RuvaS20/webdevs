<?php
session_start();
require_once '../../db/config.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['attempt_id'])) {
    header('Location: quizzes.php');
    exit();
}

try {
    $stmt = $pdo->prepare("
        SELECT qa.*, q.title, q.description
        FROM quiz_attempts qa 
        JOIN quizzes q ON qa.quiz_id = q.quiz_id
        WHERE qa.attempt_id = :attempt_id AND qa.student_id = :student_id
    ");
    
    $stmt->execute([
        ':attempt_id' => $_GET['attempt_id'],
        ':student_id' => $_SESSION['user_id']
    ]);
    
    $attempt = $stmt->fetch();

    $stmt = $pdo->prepare("
        SELECT qr.*, qq.question_text, qq.question_type, qq.correct_answer, qq.options
        FROM quiz_responses qr
        JOIN quiz_questions qq ON qr.question_id = qq.question_id
        WHERE qr.attempt_id = :attempt_id
        ORDER BY qq.order_number
    ");
    
    $stmt->execute([':attempt_id' => $_GET['attempt_id']]);
    $responses = $stmt->fetchAll();

} catch (PDOException $e) {
    $_SESSION['error'] = "Error retrieving quiz results.";
    header('Location: quizzes.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Results</title>
    <style>
        .correct { color: green; }
        .incorrect { color: red; }
        .response-card {
            border: 1px solid #ddd;
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
        }
        .score {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo htmlspecialchars($attempt['title']); ?> - Results</h1>
        
        <div class="score">
            Final Score: <?php echo number_format($attempt['total_score'], 1); ?>%
        </div>

        <div class="responses">
            <?php foreach ($responses as $response): ?>
                <div class="response-card <?php echo $response['is_correct'] ? 'correct' : 'incorrect'; ?>">
                    <h3><?php echo htmlspecialchars($response['question_text']); ?></h3>
                    <p>Your answer: <?php echo htmlspecialchars($response['user_answer']); ?></p>
                    <p>Correct answer: <?php echo htmlspecialchars($response['correct_answer']); ?></p>
                    <p>Points earned: <?php echo $response['points_earned']; ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <a href="quizzes.php" class="btn">Back to Quizzes</a>
    </div>
</body>
</html>
