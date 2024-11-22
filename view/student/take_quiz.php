<?php
session_start();
require_once '../includes/config.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../auth/login.php');
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Keep all your existing styles -->
    <style>
* {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: #f5f7fb;
            color: #2c3e50;
            line-height: 1.6;
        }

        .quiz-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
        }

        .quiz-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .quiz-content {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .question-block {
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            border-left: 5px solid #3498db;
        }

        .question-text {
            font-size: 1.2em;
            margin-bottom: 15px;
            color: #2c3e50;
        }

        input[type="radio"],
        input[type="text"] {
            margin: 8px 0;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
        }

        .option-label {
            display: block;
            padding: 10px 15px;
            margin: 5px 0;
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .option-label:hover {
            background: #e9ecef;
        }

        .submit-btn {
            display: block;
            width: 100%;
            padding: 15px;
            background: #2ecc71;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .submit-btn:hover {
            background: #27ae60;
            transform: translateY(-2px);
        }

        .result-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .result-content {
            position: relative;
            background: white;
            width: 90%;
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .result-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .score {
            font-size: 2em;
            color: #2ecc71;
            margin: 20px 0;
        }

        .question-review {
            margin: 10px 0;
            padding: 15px;
            border-radius: 8px;
        }

        .correct {
            background: #d4edda;
            border-left: 5px solid #28a745;
        }

        .incorrect {
            background: #f8d7da;
            border-left: 5px solid #dc3545;
        }

        @media (max-width: 768px) {
            .quiz-container {
                padding: 10px;
            }

            .quiz-content {
                padding: 20px;
            }
        }    </style>
</head>
<body>
    <div class="quiz-container">
        <div class="quiz-header">
            <h1><?php echo htmlspecialchars($quiz['title']); ?></h1>
            <?php if($quiz['image_url']): ?>
                <img src="../<?php echo htmlspecialchars($quiz['image_url']); ?>" alt="Quiz Cover Image">
            <?php endif; ?>
            <p><?php echo htmlspecialchars($quiz['description']); ?></p>
        </div>

        <!-- Changed to post directly to submit-quiz.php -->
        <form method="POST" action="submit-quiz.php" class="quiz-content" id="quiz-form">
            <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
            
            <?php foreach($questions as $index => $question): ?>
                <div class="question-block">
                    <p class="question-text">
                        <strong>Question <?php echo $index + 1; ?>:</strong> 
                        <?php echo htmlspecialchars($question['question_text']); ?>
                    </p>

                    <?php if($question['image_url']): ?>
                        <img src="../<?php echo htmlspecialchars($question['image_url']); ?>" 
                             alt="Question Image" class="question-image">
                    <?php endif; ?>

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
                <a href="dashboard.php" style="display: block; text-align: center; margin-top: 10px; color: #666;">
                    Cancel and return to dashboard
                </a>
            </div>
        </form>
    </div>

    <script>
    // Add form validation before submission
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
                question.style.border = '2px solid #dc3545';
            } else {
                question.style.border = 'none';
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
