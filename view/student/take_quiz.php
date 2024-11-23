<?php
session_start();
require_once '../../db/config.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../../auth/login.php');
    exit();
}

// Get quiz details
$quiz_id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE quiz_id = ?");
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch();

if (!$quiz) {
    $_SESSION['error'] = "Quiz not found.";
    header('Location: dashboard.php');
    exit();
}

// Get questions
$stmt = $pdo->prepare("SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY order_number");
$stmt->execute([$quiz_id]);
$questions = $stmt->fetchAll();

if (empty($questions)) {
    $_SESSION['error'] = "This quiz has no questions.";
    header('Location: dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($quiz['title']); ?></title>
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

        .quiz-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
        }

        .quiz-header {
            background: #041f1f;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            border: 0.5px solid rgba(235, 229, 213, 0.2);
            text-align: center;
        }

        .quiz-header h1 {
            font-family: "DM Serif Display", serif;
            color: #FECE63;
            margin-bottom: 15px;
        }

        .quiz-content {
            background: #041f1f;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            border: 0.5px solid rgba(235, 229, 213, 0.2);
        }

        .question-block {
            margin-bottom: 30px;
            padding: 20px;
            background: #052B2B;
            border-radius: 10px;
            border-left: 5px solid #FECE63;
        }

        .question-text {
            font-size: 1.2em;
            margin-bottom: 15px;
            color: #EBE5D5;
            font-family: "DM Serif Display", serif;
        }

        input[type="text"] {
            width: 100%;
            padding: 12px;
            background: #041f1f;
            border: 1px solid rgba(235, 229, 213, 0.2);
            border-radius: 8px;
            color: #EBE5D5;
            font-size: 16px;
        }

        input[type="text"]:focus {
            border-color: #FECE63;
            outline: none;
        }

        .option-label {
            display: block;
            padding: 12px 15px;
            margin: 8px 0;
            background: #041f1f;
            border: 1px solid rgba(235, 229, 213, 0.2);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #EBE5D5;
        }

        .option-label:hover {
            border-color: #FECE63;
            transform: translateY(-2px);
        }

        .submit-btn {
            display: block;
            width: 100%;
            padding: 15px;
            background: #041f1f;
            color: #EBE5D5;
            border: 1px solid #FECE63;
            border-radius: 8px;
            font-size: 1.1em;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .quiz-footer {
            margin-top: 20px;
            text-align: center;
        }

        .quiz-footer p {
            color: #EBE5D5;
            opacity: 0.8;
            margin-bottom: 10px;
        }

        .quiz-footer a {
            color: #FECE63;
            text-decoration: none;
            transition: opacity 0.3s ease;
        }

        .quiz-footer a:hover {
            opacity: 0.8;
        }

        @media (max-width: 768px) {
            .quiz-container {
                padding: 10px;
            }

            .quiz-content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="quiz-container">
        <div class="quiz-header">
            <h1><?php echo htmlspecialchars($quiz['title']); ?></h1>
            <p><?php echo htmlspecialchars($quiz['description']); ?></p>
        </div>

        <form method="POST" action="submit-quiz.php" class="quiz-content" id="quiz-form">
            <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
            
            <?php foreach($questions as $index => $question): ?>
                <div class="question-block">
                    <p class="question-text">
                        <strong>Question <?php echo $index + 1; ?>:</strong> 
                        <?php echo htmlspecialchars($question['question_text']); ?>
                    </p>

                    <?php
                    switch($question['question_type']) {
                        case 'multiple_choice':
                            $options = json_decode($question['options'], true);
                            if ($options) {
                                foreach($options as $key => $option):
                    ?>
                            <label class="option-label">
                                <input type="radio" name="answers[<?php echo $question['question_id']; ?>]" 
                                       value="<?php echo $key; ?>" required>
                                <?php echo htmlspecialchars($option); ?>
                            </label>
                    <?php 
                                endforeach;
                            }
                            break;

                        case 'true_false':
                    ?>
                            <label class="option-label">
                                <input type="radio" name="answers[<?php echo $question['question_id']; ?>]" 
                                       value="true" required> True
                            </label>
                            <label class="option-label">
                                <input type="radio" name="answers[<?php echo $question['question_id']; ?>]" 
                                       value="false" required> False
                            </label>
                    <?php
                            break;

                        case 'short_answer':
                    ?>
                            <input type="text" name="answers[<?php echo $question['question_id']; ?>]" 
                                   placeholder="Enter your answer" required>
                    <?php
                            break;
                    }
                    ?>
                </div>
            <?php endforeach; ?>

            <div class="quiz-footer">
                <p><small>Make sure to answer all questions before submitting.</small></p>
                <button type="submit" class="submit-btn">Submit Quiz</button>
                <a href="dashboard.php">Cancel and return to dashboard</a>
            </div>
        </form>
    </div>

    <script>
    document.getElementById('quiz-form').addEventListener('submit', function(e) {
        let allAnswered = true;
        const questions = this.querySelectorAll('.question-block');
        
        questions.forEach(question => {
            const inputs = question.querySelectorAll('input[type="radio"], input[type="text"]');
            let questionAnswered = false;
            
            inputs.forEach(input => {
                if(input.type === 'radio' && input.checked) questionAnswered = true;
                if(input.type === 'text' && input.value.trim() !== '') questionAnswered = true;
            });
            
            if(!questionAnswered) {
                allAnswered = false;
                question.style.borderColor = '#dc3545';
            } else {
                question.style.borderColor = '#FECE63';
            }
        });
        
        if(!allAnswered) {
            e.preventDefault();
            alert('Please answer all questions before submitting.');
            window.scrollTo(0, 0);
        } else if(!confirm('Are you sure you want to submit your quiz? You cannot change your answers after submission.')) {
            e.preventDefault();
        }
    });
    </script>
</body>
</html>
