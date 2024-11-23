// quiz_editor.js
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
        
        // Initialize questions with default option texts
        currentQuestions = data.questions.map(q => ({
            ...q,
            optionA: 'Option A',
            optionB: 'Option B',
            optionC: 'Option C',
            optionD: 'Option D'
        }));
        
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
    
    // Multiple choice with editable options
    return `
        <div class="option-block">
            <input type="radio" name="correct_${questionIndex}" 
                   ${question.correct_answer === 'A' ? 'checked' : ''}
                   onchange="updateAnswer(${questionIndex}, 'A')">
            <input type="text" value="${question.optionA}"
                   onchange="updateOptionText(${questionIndex}, 'optionA', this.value)">
        </div>
        <div class="option-block">
            <input type="radio" name="correct_${questionIndex}" 
                   ${question.correct_answer === 'B' ? 'checked' : ''}
                   onchange="updateAnswer(${questionIndex}, 'B')">
            <input type="text" value="${question.optionB}"
                   onchange="updateOptionText(${questionIndex}, 'optionB', this.value)">
        </div>
        <div class="option-block">
            <input type="radio" name="correct_${questionIndex}" 
                   ${question.correct_answer === 'C' ? 'checked' : ''}
                   onchange="updateAnswer(${questionIndex}, 'C')">
            <input type="text" value="${question.optionC}"
                   onchange="updateOptionText(${questionIndex}, 'optionC', this.value)">
        </div>
        <div class="option-block">
            <input type="radio" name="correct_${questionIndex}" 
                   ${question.correct_answer === 'D' ? 'checked' : ''}
                   onchange="updateAnswer(${questionIndex}, 'D')">
            <input type="text" value="${question.optionD}"
                   onchange="updateOptionText(${questionIndex}, 'optionD', this.value)">
        </div>
    `;
}

function updateQuestion(index, field, value) {
    currentQuestions[index][field] = value;
}

function updateQuestionType(index, newType) {
    const question = currentQuestions[index];
    question.question_type = newType;
    question.correct_answer = newType === 'true_false' ? 'true' : 'A';
    renderQuestions();
}

function updateAnswer(questionIndex, value) {
    currentQuestions[questionIndex].correct_answer = value;
}

function updateOptionText(questionIndex, optionField, value) {
    currentQuestions[questionIndex][optionField] = value;
}

function addQuestion() {
    currentQuestions.push({
        question_text: '',
        question_type: 'multiple_choice',
        correct_answer: 'A',
        points: 1,
        optionA: 'Option A',
        optionB: 'Option B',
        optionC: 'Option C',
        optionD: 'Option D'
    });
    renderQuestions();
}

function removeQuestion(index) {
    currentQuestions.splice(index, 1);
    renderQuestions();
}

document.getElementById('editQuizForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    // Strip the option texts before sending to server since they're not stored in DB
    const questionsForServer = currentQuestions.map(({ optionA, optionB, optionC, optionD, ...rest }) => rest);
    
    const formData = {
        quiz_id: document.getElementById('editQuizId').value,
        title: document.getElementById('editQuizTitle').value,
        description: document.getElementById('editQuizDescription').value,
        difficulty_level: document.getElementById('editQuizDifficulty').value,
        questions: questionsForServer
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
