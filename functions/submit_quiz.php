<?php
session_start();
require_once '../../db/config.php';

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
                SELECT question_text, question_type, correct_answer, points
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
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #041f1f;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            border: 0.5px solid rgba(235, 229, 213, 0.2);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(235, 229, 213, 0.2);
        }

        .header h1 {
            font-family: "DM Serif Display", serif;
            color: #FECE63;
            margin-bottom: 15px;
        }

        .score {
            font-size: 48px;
            color: #FECE63;
            margin: 20px 0;
            font-family: "DM Serif Display", serif;
        }

        .question-review {
            padding: 15px;
            margin: 15px 0;
            border-radius: 8px;
            background: #052B2B;
        }

        .correct {
            border-left: 4px solid #2ecc71;
        }

        .incorrect {
            border-left: 4px solid #e74c3c;
        }

        .question-text {
            font-weight: bold;
            margin-bottom: 10px;
            color: #FECE63;
        }

        .buttons {
            margin-top: 30px;
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            color: #EBE5D5;
            font-weight: 500;
            transition: all 0.3s;
            background: #052B2B;
            border: 1px solid #FECE63;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }
            
            .buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                text-align: center;
            }
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
            <a href="take-quiz.php?id=<?php echo htmlspecialchars($_POST['quiz_id']); ?>" class="btn">Take Quiz Again</a>
            <a href="dashboard.php" class="btn">Back to Dashboard</a>
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
