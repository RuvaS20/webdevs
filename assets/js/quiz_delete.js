// assets/js/quiz-delete.js

function deleteQuiz(quizId) {
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
        const button = event.target;
        const originalText = button.innerText;
        button.innerText = 'Deleting...';
        button.disabled = true;

        // Redirect to delete_quiz.php
        window.location.href = 'delete_quiz.php?id=' + quizId;
    }
}

// Add event listener for escape key to cancel deletion
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const deleteButtons = document.querySelectorAll('.btn-delete[disabled]');
        deleteButtons.forEach(button => {
            button.innerText = 'Delete';
            button.disabled = false;
        });
    }
});

// Check for success/error messages on page load
document.addEventListener('DOMContentLoaded', function() {
    // Get URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    
    // Check for success/error messages in PHP session
    // These would be displayed if they exist in your dashboard.php
    const successMessage = document.querySelector('.success-message');
    const errorMessage = document.querySelector('.error-message');
    
    if (successMessage) {
        // Add fade out effect after 3 seconds
        setTimeout(() => {
            successMessage.style.transition = 'opacity 0.5s ease';
            successMessage.style.opacity = '0';
            setTimeout(() => {
                successMessage.remove();
            }, 500);
        }, 3000);
    }

    if (errorMessage) {
        // Add fade out effect after 3 seconds
        setTimeout(() => {
            errorMessage.style.transition = 'opacity 0.5s ease';
            errorMessage.style.opacity = '0';
            setTimeout(() => {
                errorMessage.remove();
            }, 500);
        }, 3000);
    }
});