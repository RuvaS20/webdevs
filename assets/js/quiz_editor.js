// ../../assets/js/quiz_editor.js
//submission wasn't working
let currentQuestions = [];

function openEditQuizModal(quizId) {
    const modal = document.getElementById('editQuizModal');
    const editQuizForm = document.getElementById('editQuizForm');
    document.getElementById('editQuizId').value = quizId;

    // Fetch quiz data
    fetch(`../../actions/quiz/get_quiz.php?id=${quizId}`)
    .then(response => response.json())
    .then(data => {
        console.log('Full response:', data); // Add this line to see the complete response
        if (data.success) {
            // Populate form with quiz data
            document.getElementById('editQuizTitle').value = data.quiz.title;
            document.getElementById('editQuizDescription').value = data.quiz.description;
            document.getElementById('editQuizDifficulty').value = data.quiz.difficulty_level;
            
            // Load questions
            const questionsContainer = document.getElementById('questionsContainer');
            questionsContainer.innerHTML = ''; // Clear existing questions
            
            data.questions.forEach((question, index) => {
                addQuestionToForm(question, index);
            });

            // Show modal
            modal.style.display = 'block';
        } else {
            alert('Error loading quiz data: ' + (data.message || 'Unknown error')); // Modified to show the error message
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading quiz data: ' + error.message);
    });

    // Close button functionality
    const closeBtn = document.querySelector('.close');
    closeBtn.onclick = function() {
        modal.style.display = 'none';
    };

    // Click outside modal to close
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    };
}

function addQuestionToForm(question = null, index = null) {
    if (index === null) {
        index = document.querySelectorAll('.question-block').length;
    }

    const questionBlock = document.createElement('div');
    questionBlock.className = 'question-block';
    questionBlock.innerHTML = `
        <div class="question-header">
            <h3>Question ${index + 1}</h3>
            <button type="button" class="btn btn-danger" onclick="removeQuestion(this)">Remove</button>
        </div>
        ${question ? `<input type="hidden" name="questions[${index}][question_id]" value="${question.question_id}">` : ''}
        <div class="form-group">
            <label>Question Text</label>
            <input type="text" name="questions[${index}][text]" value="${question ? question.question_text : ''}" required>
        </div>
        <div class="form-group">
            <label>Question Type</label>
            <select name="questions[${index}][type]" onchange="updateQuestionType(this)">
                <option value="multiple_choice" ${question && question.question_type === 'multiple_choice' ? 'selected' : ''}>Multiple Choice</option>
                <option value="true_false" ${question && question.question_type === 'true_false' ? 'selected' : ''}>True/False</option>
                <option value="short_answer" ${question && question.question_type === 'short_answer' ? 'selected' : ''}>Short Answer</option>
            </select>
        </div>
        <div class="options-container" id="options-${index}"></div>
        <div class="form-group">
            <label>Points</label>
            <input type="number" name="questions[${index}][points]" value="${question ? question.points : '1'}" min="1" required>
        </div>
    `;

    document.getElementById('questionsContainer').appendChild(questionBlock);

    // Initialize question type options
    const typeSelect = questionBlock.querySelector('select[name*="type"]');
    updateQuestionType(typeSelect, question);
}

function updateQuestionType(select, existingQuestion = null) {
    const questionBlock = select.closest('.question-block');
    const questionIndex = Array.from(document.querySelectorAll('.question-block')).indexOf(questionBlock);
    const optionsContainer = questionBlock.querySelector('.options-container');
    
    switch(select.value) {
        case 'multiple_choice':
            optionsContainer.innerHTML = `
                <div class="option-row">
                    <input type="radio" name="questions[${questionIndex}][correct]" value="A" ${existingQuestion && existingQuestion.correct_answer === 'A' ? 'checked' : ''} required> A. 
                    <input type="text" name="questions[${questionIndex}][options][A]" required>
                </div>
                <div class="option-row">
                    <input type="radio" name="questions[${questionIndex}][correct]" value="B" ${existingQuestion && existingQuestion.correct_answer === 'B' ? 'checked' : ''} required> B. 
                    <input type="text" name="questions[${questionIndex}][options][B]" required>
                </div>
                <div class="option-row">
                    <input type="radio" name="questions[${questionIndex}][correct]" value="C" ${existingQuestion && existingQuestion.correct_answer === 'C' ? 'checked' : ''} required> C. 
                    <input type="text" name="questions[${questionIndex}][options][C]" required>
                </div>
                <div class="option-row">
                    <input type="radio" name="questions[${questionIndex}][correct]" value="D" ${existingQuestion && existingQuestion.correct_answer === 'D' ? 'checked' : ''} required> D. 
                    <input type="text" name="questions[${questionIndex}][options][D]" required>
                </div>
            `;
            break;
            
        case 'true_false':
            optionsContainer.innerHTML = `
                <div class="option-row">
                    <input type="radio" name="questions[${questionIndex}][correct]" value="true" ${existingQuestion && existingQuestion.correct_answer === 'true' ? 'checked' : ''} required> True
                </div>
                <div class="option-row">
                    <input type="radio" name="questions[${questionIndex}][correct]" value="false" ${existingQuestion && existingQuestion.correct_answer === 'false' ? 'checked' : ''} required> False
                </div>
            `;
            break;
            
        case 'short_answer':
            optionsContainer.innerHTML = `
                <div class="form-group">
                    <label>Correct Answer</label>
                    <input type="text" name="questions[${questionIndex}][correct_answer]" value="${existingQuestion ? existingQuestion.correct_answer : ''}" required>
                </div>
            `;
            break;
    }
}

function removeQuestion(button) {
    button.closest('.question-block').remove();
    updateQuestionNumbers();
}

function updateQuestionNumbers() {
    document.querySelectorAll('.question-block').forEach((block, index) => {
        // Update question number in header
        block.querySelector('h3').textContent = `Question ${index + 1}`;
        
        // Update all input names to reflect new index
        block.querySelectorAll('[name*="questions["]').forEach(input => {
            input.name = input.name.replace(/questions\[\d+\]/, `questions[${index}]`);
        });
        
        // Update options container ID
        const optionsContainer = block.querySelector('.options-container');
        if (optionsContainer) {
            optionsContainer.id = `options-${index}`;
        }
    });
}

// Add this at the top with your other event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Form submission
    const editQuizForm = document.getElementById('editQuizForm');
    if (editQuizForm) {
        editQuizForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Form submitted'); // Debug log
            
            // Log the current form data
            console.log('Quiz ID:', document.getElementById('editQuizId').value);
            console.log('Title:', document.getElementById('editQuizTitle').value);
            console.log('Description:', document.getElementById('editQuizDescription').value);
            console.log('Difficulty:', document.getElementById('editQuizDifficulty').value);
            console.log('Questions:', currentQuestions);
            
            const formData = {
                quiz_id: document.getElementById('editQuizId').value,
                title: document.getElementById('editQuizTitle').value,
                description: document.getElementById('editQuizDescription').value,
                difficulty_level: document.getElementById('editQuizDifficulty').value,
                questions: currentQuestions
            };
            
            console.log('Sending data:', formData); // Debug log
            
            fetch('../../actions/quiz/update_quiz.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            })
            .then(response => {
                console.log('Response status:', response.status); // Debug log
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data); // Debug log
                if (data.success) {
                    alert('Quiz updated successfully!');
                    window.location.reload();
                } else {
                    alert(data.message || 'Error updating quiz');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating quiz: ' + error.message);
            });
        });
    } else {
        console.error('Edit quiz form not found!'); // Debug log
    }
});

// Add new question button handler
document.getElementById('addQuestionBtn').addEventListener('click', function() {
    addQuestionToForm();
});

// Form submission
document.getElementById('editQuizForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Create the data object
    const formData = {
        quiz_id: document.getElementById('editQuizId').value,
        title: document.getElementById('editQuizTitle').value,
        description: document.getElementById('editQuizDescription').value,
        difficulty_level: document.getElementById('editQuizDifficulty').value,
        questions: currentQuestions // Make sure this variable is accessible
    };
    
    fetch('../../actions/quiz/update_quiz.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Quiz updated successfully!');
            window.location.reload();
        } else {
            alert(data.message || 'Error updating quiz');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating quiz: ' + error.message);
    });
});