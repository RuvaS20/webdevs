<?php
session_start();
require_once '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    try {
        $pdo->beginTransaction();

        // Get quiz total points
        $stmt = $pdo->prepare("SELECT SUM(points) as total_points FROM quiz_questions WHERE quiz_id = :quiz_id");
        $stmt->execute([':quiz_id' => $_POST['quiz_id']]);
        $quiz_total = $stmt->fetch();
        $max_points = $quiz_total['total_points'];

        // Create quiz attempt
        $stmt = $pdo->prepare("INSERT INTO quiz_attempts (
            student_id, 
            quiz_id, 
            start_time, 
            end_time,
            completion_status,
            total_score
        ) VALUES (
            :student_id, 
            :quiz_id, 
            :start_time, 
            NOW(),
            1,
            0
        )");
        
        $stmt->execute([
            ':student_id' => $_SESSION['user_id'],
            ':quiz_id' => $_POST['quiz_id'],
            ':start_time' => date('Y-m-d H:i:s')
        ]);
        
        $attempt_id = $pdo->lastInsertId();
        $total_points_earned = 0;
        $questions_data = [];

        // Process each answer
        foreach ($_POST['answers'] as $question_id => $answer) {
            $stmt = $pdo->prepare("
                SELECT question_text, question_type, correct_answer, points, options 
                FROM quiz_questions 
                WHERE question_id = :question_id
            ");
            $stmt->execute([':question_id' => $question_id]);
            $question = $stmt->fetch();

            $is_correct = false;
            switch($question['question_type']) {
                case 'multiple_choice':
                    $is_correct = strcasecmp($answer, $question['correct_answer']) === 0;
                    break;
                case 'true_false':
                    $is_correct = strcasecmp($answer, $question['correct_answer']) === 0;
                    break;
                case 'short_answer':
                    $is_correct = strcasecmp(trim($answer), trim($question['correct_answer'])) === 0;
                    break;
            }

            $points_earned = $is_correct ? $question['points'] : 0;
            $total_points_earned += $points_earned;

            // Save response
            $stmt = $pdo->prepare("INSERT INTO quiz_responses (
                attempt_id, 
                question_id, 
                user_answer, 
                is_correct, 
                points_earned
            ) VALUES (
                :attempt_id, 
                :question_id, 
                :user_answer, 
                :is_correct, 
                :points_earned
            )");
            
            $stmt->execute([
                ':attempt_id' => $attempt_id,
                ':question_id' => $question_id,
                ':user_answer' => $answer,
                ':is_correct' => $is_correct ? 1 : 0,
                ':points_earned' => $points_earned
            ]);

            // Store for display
            $questions_data[] = [
                'question_text' => $question['question_text'],
                'your_answer' => $answer,
                'correct_answer' => $question['correct_answer'],
                'is_correct' => $is_correct,
                'points_earned' => $points_earned,
                'max_points' => $question['points']
            ];
        }

        // Calculate and update score
        $percentage_score = ($max_points > 0) ? ($total_points_earned / $max_points) * 100 : 0;
        $stmt = $pdo->prepare("UPDATE quiz_attempts 
                              SET total_score = :total_score 
                              WHERE attempt_id = :attempt_id");
        $stmt->execute([
            ':total_score' => $percentage_score,
            ':attempt_id' => $attempt_id
        ]);

        $pdo->commit();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Results</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: #f4f6f8;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .score {
            font-size: 48px;
            color: #2ecc71;
            margin: 20px 0;
            text-align: center;
        }

        .question-review {
            padding: 15px;
            margin: 15px 0;
            border-radius: 8px;
        }

        .correct {
            background-color: #d4edda;
            border-left: 4px solid #28a745;
        }

        .incorrect {
            background-color: #f8d7da;
            border-left: 4px solid #dc3545;
        }

        .question-text {
            font-weight: bold;
            margin-bottom: 10px;
        }

        .buttons {
            margin-top: 30px;
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            color: white;
            font-weight: 500;
        }

        .btn-retake {
            background-color: #3498db;
        }

        .btn-dashboard {
            background-color: #95a5a6;
        }

        .btn:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Quiz Results</h1>
            <div class="score"><?php echo round($percentage_score, 1); ?>%</div>
            <p>Points earned: <?php echo $total_points_earned; ?> out of <?php echo $max_points; ?></p>
        </div>

        <?php foreach ($questions_data as $question): ?>
            <div class="question-review <?php echo $question['is_correct'] ? 'correct' : 'incorrect'; ?>">
                <div class="question-text"><?php echo htmlspecialchars($question['question_text']); ?></div>
                <p>Your answer: <?php echo htmlspecialchars($question['your_answer']); ?></p>
                <p>Correct answer: <?php echo htmlspecialchars($question['correct_answer']); ?></p>
                <p>Points: <?php echo $question['points_earned']; ?>/<?php echo $question['max_points']; ?></p>
            </div>
        <?php endforeach; ?>

        <div class="buttons">
            <a href="take-quiz.php?id=<?php echo htmlspecialchars($_POST['quiz_id']); ?>" class="btn btn-retake">Take Quiz Again</a>
            <a href="dashboard.php" class="btn btn-dashboard">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>

<?php
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Error submitting quiz: " . $e->getMessage();
        header("Location: dashboard.php");
        exit();
    }
} else {
    header("Location: dashboard.php");
    exit();
}
?>
