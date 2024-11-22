<?php
session_start();
require_once '../../db/database.php';

// Check if the user is logged in as a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../../auth/login.php');
    exit();
}

$teacher_id = $_SESSION['user_id'];  // Get the teacher's ID from the session
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Quiz</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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

        .image-preview {
            max-width: 200px;
            margin: 10px 0;
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

        .correct-answer {
            background: #d4edda;
            border-color: #c3e6cb;
        }

        .image-upload {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 15px;
            padding: 15px;
            border: 2px dashed #cbd5e0;
            border-radius: 8px;
            cursor: pointer;
        }

        .image-upload:hover {
            border-color: #3498db;
        }

        .image-upload i {
            font-size: 24px;
            color: #3498db;
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

        <form class="quiz-form" action="save-quiz.php" method="POST" enctype="multipart/form-data">
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
                <label for="quiz-image">Quiz Cover Image</label>
                <div class="image-upload" onclick="document.getElementById('quiz-image').click()">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <div>
                        <p>Drop your image here or click to upload</p>
                        <p style="font-size: 12px; color: #666;">Supports: JPG, PNG (Max 2MB)</p>
                    </div>
                </div>
                <input type="file" id="quiz-image" name="quiz_image" style="display: none" accept="image/*">
                <div id="image-preview" class="image-preview"></div>
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
                        <button type="button" class="btn btn-danger" onclick="removeQuestion(this)">
                            <i class="fas fa-trash"></i>
                        </button>
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

                    <div class="options-container" id="options-0">
                        <!-- Options will be dynamically added here -->
                    </div>

                    <div class="form-group">
                        <label>Points</label>
                        <input type="number" name="questions[0][points]" value="1" min="1" required>
                    </div>

                    <div class="form-group">
                        <label>Question Image (Optional)</label>
                        <div class="image-upload" onclick="document.getElementById('question-0-image').click()">
                            <i class="fas fa-image"></i>
                            <p>Add an image to this question</p>
                        </div>
                        <input type="file" id="question-0-image" name="questions[0][image]" 
                               style="display: none" accept="image/*" onchange="previewImage(this)">
                        <div class="image-preview" id="preview-0"></div>
                    </div>
                </div>
            </div>

            <button type="button" class="btn btn-primary" onclick="addQuestion()" style="margin-right: 10px;">
                <i class="fas fa-plus"></i> Add Question
            </button>
            <button type="submit" class="btn btn-success">
                <i class="fas fa-check"></i> Create Quiz
            </button>
        </form>
    </div>

    <script>
        let questionCount = 1;

        // Function to add new question block
        function addQuestion() {
            const questionContainer = document.getElementById('questions-container');
            const newQuestionBlock = document.createElement('div');
            newQuestionBlock.classList.add('question-block');
            newQuestionBlock.innerHTML = `
                <div class="question-header">
                    <h3>Question ${questionCount + 1}</h3>
                    <button type="button" class="btn btn-danger" onclick="removeQuestion(this)">
                        <i class="fas fa-trash"></i>
                    </button>
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

                <div class="options-container" id="options-${questionCount}">
                    <!-- Options will be dynamically added here -->
                </div>

                <div class="form-group">
                    <label>Points</label>
                    <input type="number" name="questions[${questionCount}][points]" value="1" min="1" required>
                </div>

                <div class="form-group">
                    <label>Question Image (Optional)</label>
                    <div class="image-upload" onclick="document.getElementById('question-${questionCount}-image').click()">
                        <i class="fas fa-image"></i>
                        <p>Add an image to this question</p>
                    </div>
                    <input type="file" id="question-${questionCount}-image" name="questions[${questionCount}][image]" 
                           style="display: none" accept="image/*" onchange="previewImage(this)">
                    <div class="image-preview" id="preview-${questionCount}"></div>
                </div>
            `;
            questionContainer.appendChild(newQuestionBlock);
            questionCount++;
        }

        // Function to remove question
        function removeQuestion(button) {
            button.closest('.question-block').remove();
            questionCount--;
        }

        // Function to handle image preview
        function previewImage(input) {
            const previewId = input.id.replace('image', 'preview');
            const preview = document.getElementById(previewId);

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Image Preview" style="max-width: 100%; height: auto;">`;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Function to handle question type change (e.g. multiple-choice)
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
                    <div class="option-row">
                        <input type="radio" name="questions[${questionIndex}][correct]" value="C"> C. <input type="text" name="questions[${questionIndex}][options][C]" required>
                    </div>
                    <div class="option-row">
                        <input type="radio" name="questions[${questionIndex}][correct]" value="D"> D. <input type="text" name="questions[${questionIndex}][options][D]" required>
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
// Initialize first question's type
window.addEventListener('DOMContentLoaded', function() {
    const firstQuestionType = document.querySelector('select[name="questions[0][type]"]');
    if (firstQuestionType) {
        updateQuestionType(firstQuestionType);
    }
});

// Handle quiz image preview
document.getElementById('quiz-image').addEventListener('change', function(e) {
    const preview = document.getElementById('image-preview');
    if (e.target.files && e.target.files[0]) {
        const reader = new FileReader();
        reader.onload = function(event) {
            preview.innerHTML = `<img src="${event.target.result}" alt="Quiz Cover Preview" style="max-width: 200px;">`;
        };
        reader.readAsDataURL(e.target.files[0]);
    }
});
    </script>
</body>
</html>
