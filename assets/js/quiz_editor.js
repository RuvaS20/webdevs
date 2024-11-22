let currentQuestions = [];

function openEditQuizModal(quizId) {
    const modal = document.getElementById('editQuizModal');
    modal.style.display = 'block';
    loadQuizData(quizId);
}

function closeModal() {
    const modal = document.getElementById('editQuizModal');
    modal.style.display = 'none';
}

async function loadQuizData(quizId) {
    try {
        const response = await fetch(`../../actions/quiz/get_quiz.php?id=${quizId}`);
        const data = await response.json();
        
        document.getElementById('editQuizId').value = data.quiz_id;
        document.getElementById('editQuizTitle').value = data.title;
        document.getElementById('editQuizDescription').value = data.description;
        document.getElementById('editQuizDifficulty').value = data.difficulty_level;
        
        currentQuestions = data.questions;
        renderQuestions();
    } catch (error) {
        console.error('Error loading quiz:', error);
        alert('Error loading quiz data');
    }
}

function renderQuestions() {
    const container = document.getElementById('questionsContainer');
    container.innerHTML = '';
    
    currentQuestions.forEach((question, index) => {
        const questionBlock = document.createElement('div');
        questionBlock.className = 'question-block';
        questionBlock.innerHTML = `
            <div class="form-group">
                <span class="remove-question" onclick="removeQuestion(${index})">&times;</span>
                <label>Question ${index + 1}</label>
                <input type="text" value="${question.question_text}" 
                       onchange="updateQuestion(${index}, 'question_text', this.value)" required>
                
                <label>Question Type</label>
                <select onchange="updateQuestionType(${index}, this.value)">
                    <option value="multiple_choice" ${question.question_type === 'multiple_choice' ? 'selected' : ''}>Multiple Choice</option>
                    <option value="true_false" ${question.question_type === 'true_false' ? 'selected' : ''}>True/False</option>
                </select>
                
                <div class="answer-options">
                    ${renderAnswerOptions(question, index)}
                </div>
            </div>
        `;
        container.appendChild(questionBlock);
    });
}

function renderAnswerOptions(question, questionIndex) {
    if (question.question_type === 'true_false') {
        return `
            <div class="option-block">
                <input type="radio" name="correct_${questionIndex}" 
                       ${question.correct_answer === 'true' ? 'checked' : ''}
                       onchange="updateAnswer(${questionIndex}, 'true')">
                <label>True</label>
            </div>
            <div class="option-block">
                <input type="radio" name="correct_${questionIndex}" 
                       ${question.correct_answer === 'false' ? 'checked' : ''}
                       onchange="updateAnswer(${questionIndex}, 'false')">
                <label>False</label>
            </div>
        `;
    }
    
    // Multiple choice
    return question.options.map((option, optionIndex) => `
        <div class="option-block">
            <input type="radio" name="correct_${questionIndex}" 
                   ${option === question.correct_answer ? 'checked' : ''}
                   onchange="updateAnswer(${questionIndex}, '${option}')">
            <input type="text" value="${option}" 
                   onchange="updateOption(${questionIndex}, ${optionIndex}, this.value)">
            <span onclick="removeOption(${questionIndex}, ${optionIndex})">&times;</span>
        </div>
    `).join('') + `
        <button type="button" onclick="addOption(${questionIndex})" class="btn btn-secondary">
            Add Option
        </button>
    `;
}

function addQuestion() {
    currentQuestions.push({
        question_text: '',
        question_type: 'multiple_choice',
        correct_answer: '',
        options: ['', '', '', '']
    });
    renderQuestions();
}

// Add other necessary functions (updateQuestion, removeQuestion, etc.)

document.getElementById('editQuizForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = {
        quiz_id: document.getElementById('editQuizId').value,
        title: document.getElementById('editQuizTitle').value,
        description: document.getElementById('editQuizDescription').value,
        difficulty_level: document.getElementById('editQuizDifficulty').value,
        questions: currentQuestions
    };
    
    try {
        const response = await fetch('../../actions/quiz/update_quiz.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        if (response.ok) {
            alert('Quiz updated successfully');
            closeModal();
            window.location.reload();
        } else {
            throw new Error('Failed to update quiz');
        }
    } catch (error) {
        console.error('Error updating quiz:', error);
        alert('Error updating quiz');
    }
});