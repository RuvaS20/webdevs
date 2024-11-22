<?php
session_start();
require_once '../../db/database.php';

// Check if the user is logged in as a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../../auth/login.php');
    exit();
}

$teacher_id = $_SESSION['user_id']; // Get the teacher's ID from the session
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Quiz</title>
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

        .container {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
        }

        .quiz-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .quiz-header h1 {
            color: #2c3e50;
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .quiz-form {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #34495e;
        }

        input[type="text"],
        textarea,
        select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        textarea:focus,
        select:focus {
            border-color: #3498db;
            outline: none;
        }

        .question-block {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #e9ecef;
        }

        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #3498db;
            color: white;
        }

        .btn-success {
            background: #2ecc71;
            color: white;
        }

        .btn-danger {
            background: #e74c3c;
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .options-container {
            margin-top: 15px;
        }

        .option-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .quiz-form {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="quiz-header">
            <h1>Create New Quiz</h1>
            <p>Design your quiz questions and set up the parameters below</p>
        </div>

        <form class="quiz-form" action="save-quiz.php" method="POST">
            <!-- Hidden input to send teacher_id to save-quiz.php -->
            <input type="hidden" name="teacher_id" value="<?php echo $teacher_id; ?>">

            <div class="form-group">
                <label for="title">Quiz Title</label>
                <input type="text" id="title" name="title" required placeholder="Enter quiz title">
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="3" placeholder="Describe your quiz"></textarea>
            </div>

            <div class="form-group">
                <label for="difficulty">Difficulty Level</label>
                <select id="difficulty" name="difficulty_level">
                    <option value="easy">Easy</option>
                    <option value="medium">Medium</option>
                    <option value="hard">Hard</option>
                </select>
            </div>

            <div id="questions-container">
                <div class="question-block">
                    <div class="question-header">
                        <h3>Question 1</h3>
                        <button type="button" class="btn btn-danger" onclick="removeQuestion(this)">Remove</button>
                    </div>

                    <div class="form-group">
                        <label>Question Text</label>
                        <input type="text" name="questions[0][text]" required placeholder="Enter your question">
                    </div>

                    <div class="form-group">
                        <label>Question Type</label>
                        <select name="questions[0][type]" onchange="updateQuestionType(this)">
                            <option value="multiple_choice">Multiple Choice</option>
                            <option value="true_false">True/False</option>
                            <option value="short_answer">Short Answer</option>
                        </select>
                    </div>

                    <div class="options-container" id="options-0"></div>

                    <div class="form-group">
                        <label>Points</label>
                        <input type="number" name="questions[0][points]" value="1" min="1" required>
                    </div>
                </div>
            </div>

            <button type="button" class="btn btn-primary" onclick="addQuestion()">Add Question</button>
            <button type="submit" class="btn btn-success">Create Quiz</button>
        </form>
    </div>

    <script>
        let questionCount = 1;

        function addQuestion() {
            const questionContainer = document.getElementById('questions-container');
            const newQuestionBlock = document.createElement('div');
            newQuestionBlock.classList.add('question-block');
            newQuestionBlock.innerHTML = `
                <div class="question-header">
                    <h3>Question ${questionCount + 1}</h3>
                    <button type="button" class="btn btn-danger" onclick="removeQuestion(this)">Remove</button>
                </div>
                <div class="form-group">
                    <label>Question Text</label>
                    <input type="text" name="questions[${questionCount}][text]" required placeholder="Enter your question">
                </div>
                <div class="form-group">
                    <label>Question Type</label>
                    <select name="questions[${questionCount}][type]" onchange="updateQuestionType(this)">
                        <option value="multiple_choice">Multiple Choice</option>
                        <option value="true_false">True/False</option>
                        <option value="short_answer">Short Answer</option>
                    </select>
                </div>
                <div class="options-container" id="options-${questionCount}"></div>
                <div class="form-group">
                    <label>Points</label>
                    <input type="number" name="questions[${questionCount}][points]" value="1" min="1" required>
                </div>
            `;
            questionContainer.appendChild(newQuestionBlock);
            questionCount++;
        }

        function removeQuestion(button) {
            button.closest('.question-block').remove();
            questionCount--;
        }

        function updateQuestionType(select) {
            const questionIndex = select.name.match(/\d+/)[0];
            const optionsContainer = document.getElementById(`options-${questionIndex}`);
            if (select.value === 'multiple_choice') {
                optionsContainer.innerHTML = `
                    <div class="option-row">
                        <input type="radio" name="questions[${questionIndex}][correct]" value="A"> A. <input type="text" name="questions[${questionIndex}][options][A]" required>
                    </div>
                    <div class="option-row">
                        <input type="radio" name="questions[${questionIndex}][correct]" value="B"> B. <input type="text" name="questions[${questionIndex}][options][B]" required>
                    </div>
                `;
            } else if (select.value === 'true_false') {
                optionsContainer.innerHTML = `
                    <div class="option-row">
                        <input type="radio" name="questions[${questionIndex}][correct]" value="true"> True
                    </div>
                    <div class="option-row">
                        <input type="radio" name="questions[${questionIndex}][correct]" value="false"> False
                    </div>
                `;
            } else if (select.value === 'short_answer') {
                optionsContainer.innerHTML = `
                    <div class="form-group">
                        <label>Correct Answer</label>
                        <input type="text" name="questions[${questionIndex}][correct_answer]" required placeholder="Enter the correct answer">
                    </div>
                `;
            }
        }
    </script>
</body>
</html>
