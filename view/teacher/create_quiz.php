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
            font-family: "DM Serif Display", serif;
            color: #EBE5D5;
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .quiz-form {
            background: #041f1f;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            border: 0.5px solid rgba(235, 229, 213, 0.2);
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #FECE63;
        }

        input[type="text"],
        textarea,
        select,
        input[type="number"] {
            width: 100%;
            padding: 12px;
            background-color: #052B2B;
            border: 2px solid rgba(235, 229, 213, 0.2);
            border-radius: 8px;
            font-size: 16px;
            color: #EBE5D5;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        textarea:focus,
        select:focus,
        input[type="number"]:focus {
            border-color: #FECE63;
            outline: none;
        }

        .question-block {
            background: #052B2B;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid rgba(235, 229, 213, 0.2);
        }

        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .question-header h3 {
            color: #FECE63;
            font-family: "DM Serif Display", serif;
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
            background: #052B2B;
            color: #EBE5D5;
            border: 1px solid #FECE63;
        }

        .btn-success {
            background: #FECE63;
            color: #052B2B;
        }

        .btn-danger {
            background: #e74c3c;
            color: #EBE5D5;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .options-container {
            margin-top: 15px;
        }

        .option-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            color: #EBE5D5;
        }

        .option-row input[type="radio"] {
            accent-color: #FECE63;
        }

        .option-row input[type="text"] {
            flex: 1;
        }

        select {
            background-color: #052B2B;
            color: #EBE5D5;
            cursor: pointer;
        }

        select option {
            background-color: #052B2B;
            color: #EBE5D5;
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .quiz-form {
                padding: 20px;
            }
            
            h1 {
                font-size: 2em;
            }
        }

        .question-block:hover {
            border-color: #FECE63;
            transition: border-color 0.3s ease;
        }

        input::placeholder {
            color: rgba(235, 229, 213, 0.5);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="quiz-header">
            <h1>Create New Quiz</h1>
            <p>Design your quiz questions and set up the parameters below</p>
        </div>

        <form class="quiz-form" id="quizForm" action="../../functions/save_quiz.php" method="POST">
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
                    </select>
                </div>
                <div class="options-container" id="options-${questionCount}"></div>
                <div class="form-group">
                    <label>Points</label>
                    <input type="number" name="questions[${questionCount}][points]" value="1" min="1" required>
                </div>
            `;
            questionContainer.appendChild(newQuestionBlock);
            
            // Initialize the question type for the new question
            const select = newQuestionBlock.querySelector('select');
            updateQuestionType(select);
            
            questionCount++;
        }

        function removeQuestion(button) {
            button.closest('.question-block').remove();
            updateQuestionNumbers();
        }

        function updateQuestionNumbers() {
            const questions = document.querySelectorAll('.question-block');
            questions.forEach((question, index) => {
                question.querySelector('h3').textContent = `Question ${index + 1}`;
                
                // Update name attributes
                const inputs = question.querySelectorAll('input[name*="questions["], select[name*="questions["]');
                inputs.forEach(input => {
                    input.name = input.name.replace(/questions\[\d+\]/, `questions[${index}]`);
                });
                
                // Update options container ID
                const optionsContainer = question.querySelector('.options-container');
                if (optionsContainer) {
                    optionsContainer.id = `options-${index}`;
                }
            });
            questionCount = questions.length;
        }

        function updateQuestionType(select) {
            const questionBlock = select.closest('.question-block');
            const questionIndex = Array.from(document.querySelectorAll('.question-block')).indexOf(questionBlock);
            const optionsContainer = document.getElementById(`options-${questionIndex}`);
            
            if (select.value === 'multiple_choice') {
                optionsContainer.innerHTML = `
                    <div class="option-row">
                        <input type="radio" name="questions[${questionIndex}][correct]" value="A" required> A. 
                        <input type="text" name="questions[${questionIndex}][options][A]" required>
                    </div>
                    <div class="option-row">
                        <input type="radio" name="questions[${questionIndex}][correct]" value="B" required> B. 
                        <input type="text" name="questions[${questionIndex}][options][B]" required>
                    </div>
                    <div class="option-row">
                        <input type="radio" name="questions[${questionIndex}][correct]" value="C" required> C. 
                        <input type="text" name="questions[${questionIndex}][options][C]" required>
                    </div>
                    <div class="option-row">
                        <input type="radio" name="questions[${questionIndex}][correct]" value="D" required> D. 
                        <input type="text" name="questions[${questionIndex}][options][D]" required>
                    </div>
                `;
            } else if (select.value === 'true_false') {
                optionsContainer.innerHTML = `
                    <div class="option-row">
                        <input type="radio" name="questions[${questionIndex}][correct]" value="true" required> True
                    </div>
                    <div class="option-row">
                        <input type="radio" name="questions[${questionIndex}][correct]" value="false" required> False
                    </div>
                `;
            }
        }

        // Initialize the first question's type
        document.addEventListener('DOMContentLoaded', function() {
            const firstQuestionType = document.querySelector('select[name="questions[0][type]"]');
            updateQuestionType(firstQuestionType);
        });

        // Form submission handling
        document.getElementById('quizForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('../../functions/save_quiz.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                window.location.href = 'dashboard.php';
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error creating quiz. Please try again.');
            });
        });
    </script>
</body>
</html>
