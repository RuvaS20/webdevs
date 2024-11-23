function deleteQuiz(quizId, event) {  // Add event parameter here
    // Show confirmation dialog with detailed message
    const confirmation = confirm(
        'Are you sure you want to delete this quiz?\n\n' +
        'This will permanently remove:\n' +
        '- All quiz questions\n' +
        '- All student attempts\n' +
        '- All quiz scores\n\n' +
        'This action cannot be undone.'
    );
    
    if (confirmation) {
        // Show loading state on the button
        const button = event.target;  // Now this will work correctly
        const originalText = button.innerText;
        button.innerText = 'Deleting...';
        button.disabled = true;
        
        // Redirect to delete_quiz.php
        window.location.href = '../../actions/quiz/delete_quiz.php?id=' + quizId;
    }
}
