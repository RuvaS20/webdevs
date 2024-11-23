<?php
session_start();
require_once '../../db/database.php';
error_log("Session messages - Success: " . 
    (isset($_SESSION['success']) ? $_SESSION['success'] : 'none') . 
    ", Error: " . (isset($_SESSION['error']) ? $_SESSION['error'] : 'none'));

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../../auth/login.php');
    exit();
}

// Get teacher's quizzes with error checking
try {
    $stmt = $pdo->prepare("
        SELECT 
            q.quiz_id,
            q.title,
            q.description,
            q.difficulty_level,
            q.created_date,
            COUNT(DISTINCT qa.attempt_id) as attempt_count,
            AVG(qa.total_score) as average_score
        FROM quizzes q
        LEFT JOIN quiz_attempts qa ON q.quiz_id = qa.quiz_id
        WHERE q.teacher_id = :teacher_id AND q.is_active = 1
        GROUP BY q.quiz_id
        ORDER BY q.created_date DESC
    ");

    $stmt->execute([':teacher_id' => $_SESSION['user_id']]);
    $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get recent quiz attempts
    $stmt = $pdo->prepare("SELECT 
        qa.attempt_id,
        qa.total_score,
        qa.start_time,
        q.title as quiz_title,
        u.username as student_name
        FROM quiz_attempts qa
        JOIN quizzes q ON qa.quiz_id = q.quiz_id
        JOIN users u ON qa.student_id = u.user_id
        WHERE q.teacher_id = :teacher_id
        ORDER BY qa.start_time DESC
        LIMIT 5");
    $stmt->execute([':teacher_id' => $_SESSION['user_id']]);
    $recent_attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    $_SESSION['error'] = "Database Error: " . $e->getMessage();
    $quizzes = [];
    $recent_attempts = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Arimo:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        /* Base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Arimo", sans-serif;
            color: #EBE5D5;
            background-color: #052B2B;
            line-height: 1.6;
        }

        /* Dashboard Container */
        .dashboard {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header Section */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 0.5px solid rgba(235, 229, 213, 0.4);
        }

        .header h1 {
            font-family: "DM Serif Display", serif;
            color: #EBE5D5;
            font-size: 2.5em;
        }

        .create-quiz-btn {
            background-color: #FECE63;
            color: #3A4E3C;
            padding: 1rem 2rem;
            border: none;
            border-radius: 10px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .create-quiz-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(254, 206, 99, 0.3);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: #041f1f;
            color: #EBE5D5;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            transition: transform 0.3s ease;
            border: 1px solid rgba(235, 229, 213, 0.2);
        }

        .stat-card:hover {
            transform: translateY(-3px);
            border-color: #FECE63;
        }

        .stat-card h3 {
            font-family: "DM Serif Display", serif;
            color: #FECE63;
            font-size: 2em;
            margin-bottom: 5px;
        }

        /* Main Content */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }

        .card {
            background: #041f1f;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            margin-bottom: 20px;
            border: 1px solid rgba(235, 229, 213, 0.2);
        }

        .card h2 {
            font-family: "DM Serif Display", serif;
            color: #FECE63;
            margin-bottom: 20px;
            font-size: 1.5em;
        }

        /* Quiz Items */
        .quiz-item {
            border: 1px solid rgba(235, 229, 213, 0.2);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            transition: all 0.2s ease;
            background: #052B2B;
        }

        .quiz-item:hover {
            transform: translateY(-2px);
            border-color: #FECE63;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .quiz-item h3 {
            color: #FECE63;
            margin-bottom: 5px;
            font-family: "DM Serif Display", serif;
        }

        /* Quiz Stats and Actions */
        .quiz-stats {
            display: flex;
            gap: 20px;
            margin: 10px 0;
            color: #EBE5D5;
            font-size: 0.9em;
        }

        .quiz-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        /* Buttons */
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9em;
            transition: all 0.3s ease;
        }

        .btn-view {
            background-color: #EBE5D5;
            color: #3A4E3C;
        }

        .btn-edit {
            background-color: #FECE63;
            color: #3A4E3C;
        }

        .btn-delete {
            background-color: #f44336;
            color: #EBE5D5;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        /* Recent Attempts */
        .attempt-item {
            padding: 10px 0;
            border-bottom: 1px solid rgba(235, 229, 213, 0.2);
        }

        .attempt-item:last-child {
            border-bottom: none;
        }

        /* Modal Styles */
        .modal {
    display: none; /* Start hidden by default */
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(5, 43, 43, 0.9);
}

.modal-content {
    position: relative;
    background-color: #041f1f;
    color: #EBE5D5;
    border: 1px solid rgba(235, 229, 213, 0.2);
    margin: 5% auto;
    padding: 20px;
    width: 80%;
    max-width: 800px;
    border-radius: 10px;
}

        .modal-header {
            border-bottom: 1px solid rgba(235, 229, 213, 0.2);
        }

        .close {
            color: #EBE5D5;
        }

        .close:hover {
            color: #FECE63;
        }

        /* Form Styles */
        .form-group label {
            color: #EBE5D5;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            background-color: #052B2B;
            border: 1px solid rgba(235, 229, 213, 0.2);
            color: #EBE5D5;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: #FECE63;
            outline: none;
        }

        /* Messages */
        .success-message {
            background-color: rgba(76, 175, 80, 0.1);
            border: 1px solid #4CAF50;
            color: #EBE5D5;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }

        .error-message {
            background-color: rgba(244, 67, 54, 0.1);
            border: 1px solid #f44336;
            color: #EBE5D5;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }

        .success-message.fade-out,
        .error-message.fade-out {
            opacity: 0;
            transition: opacity 0.5s ease;
        }
/* Add/update these styles in your dashboard.php */

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(5, 43, 43, 0.9);
    overflow-y: auto;
}

.modal-content {
    position: relative;
    background-color: #041f1f;
    color: #EBE5D5;
    border: 1px solid rgba(235, 229, 213, 0.2);
    margin: 5% auto;
    padding: 20px;
    width: 80%;
    max-width: 800px;
    border-radius: 10px;
}

/* Question Block Styles */
.question-block {
    background: #052B2B;
    border: 1px solid rgba(235, 229, 213, 0.2);
    padding: 20px;
    margin: 15px 0;
    border-radius: 10px;
}

.question-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.question-header h3 {
    color: #FECE63;
    margin: 0;
}

.option-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 10px 0;
    padding: 10px;
    background: rgba(235, 229, 213, 0.05);
    border-radius: 5px;
}

.option-row input[type="text"] {
    flex: 1;
    background: #041f1f;
    border: 1px solid rgba(235, 229, 213, 0.2);
    color: #EBE5D5;
    padding: 8px;
    border-radius: 5px;
}

.option-row input[type="radio"] {
    margin: 0;
    cursor: pointer;
}

/* Form Styles Update */
.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #EBE5D5;
}

.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 10px;
    background: #041f1f;
    border: 1px solid rgba(235, 229, 213, 0.2);
    color: #EBE5D5;
    border-radius: 5px;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    border-color: #FECE63;
    outline: none;
}

/* Button styles for the modal */
.btn-danger {
    background-color: #f44336;
    color: #EBE5D5;
}

.form-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 20px;
}

#addQuestionBtn {
    background-color: #052B2B;
    color: #FECE63;
    border: 1px solid #FECE63;
}

button[type="submit"] {
    background-color: #FECE63;
    color: #3A4E3C;
}

        /* Responsive Design */
        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .quiz-stats {
                flex-direction: column;
                gap: 5px;
            }

            .quiz-actions {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php if(isset($_SESSION['success'])): ?>
            <div class="success-message">
                <?php 
                    echo $_SESSION['success']; 
                    unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if(isset($_SESSION['error'])): ?>
            <div class="error-message">
                <?php 
                    echo $_SESSION['error']; 
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>
        
        <div class="header">
            <h1>Teacher Dashboard</h1>
            <a href="create_quiz.php" class="create-quiz-btn">Create New Quiz</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo count($quizzes); ?></h3>
                <p>Total Quizzes</p>
            </div>
            <div class="stat-card">
                <h3><?php 
                    $total_attempts = 0;
                    foreach($quizzes as $quiz) {
                        $total_attempts += $quiz['attempt_count'];
                    }
                    echo $total_attempts;
                ?></h3>
                <p>Total Attempts</p>
            </div>
            <div class="stat-card">
                <h3><?php 
                    $avg_score = 0;
                    $count = 0;
                    foreach($quizzes as $quiz) {
                        if($quiz['average_score']) {
                            $avg_score += $quiz['average_score'];
                            $count++;
                        }
                    }
                    echo $count ? round($avg_score/$count, 1) : 0;
                ?>%</h3>
                <p>Average Score</p>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="main-content">
                <div class="card">
                    <h2>Your Quizzes</h2>
                    <?php if(empty($quizzes)): ?>
                        <p>You haven't created any quizzes yet.</p>
                    <?php else: ?>
                        <ul class="quiz-list">
                            <?php foreach($quizzes as $quiz): ?>
                                <li class="quiz-item" data-quiz-id="<?php echo $quiz['quiz_id']; ?>">
                                    <h3><?php echo htmlspecialchars($quiz['title']); ?></h3>
                                    <p><?php echo htmlspecialchars($quiz['description']); ?></p>
                                    <div class="quiz-stats">
                                        <span>Difficulty: <?php echo htmlspecialchars($quiz['difficulty_level']); ?></span>
                                        <span>Attempts: <?php echo $quiz['attempt_count']; ?></span>
                                        <span>Average Score: <?php echo $quiz['average_score'] ? round($quiz['average_score'], 1) : 0; ?>%</span>
                                    </div>
                                    <div class="quiz-actions">
                                        <a href="view_results.php?id=<?php echo $quiz['quiz_id']; ?>" class="btn btn-view">View Results</a>
                                        <a href="#" onclick="openEditQuizModal(<?php echo $quiz['quiz_id']; ?>)" class="btn btn-edit">Edit Quiz</a>
                                        <button onclick="deleteQuiz(<?php echo $quiz['quiz_id']; ?>, event)" class="btn btn-delete">Delete</button>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>

            <div class="sidebar">
                <div class="card">
                    <h2>Recent Attempts</h2>
                    <?php if(empty($recent_attempts)): ?>
                        <p>No quiz attempts yet.</p>
                    <?php else: ?>
                        <ul class="attempt-list">
                            <?php foreach($recent_attempts as $attempt): ?>
                                <li class="attempt-item">
                                    <p><strong><?php echo htmlspecialchars($attempt['student_name']); ?></strong></p>
                                    <p>Quiz: <?php echo htmlspecialchars($attempt['quiz_title']); ?></p>
                                    <p>Score: <?php echo round($attempt['total_score'], 1); ?>%</p>
                                    <p>Date: <?php echo date('M d, Y', strtotime($attempt['start_time'])); ?></p>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Edit Quiz Modal -->
        <!-- Edit Quiz Modal -->
<div id="editQuizModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit Quiz</h2>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <form id="editQuizForm">
                <input type="hidden" id="editQuizId" name="quiz_id">
                
                <div class="form-group">
                    <label for="editQuizTitle">Quiz Title</label>
                    <input type="text" id="editQuizTitle" name="title" required>
                </div>
                
                <div class="form-group">
                    <label for="editQuizDescription">Description</label>
                    <textarea id="editQuizDescription" name="description" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="editQuizDifficulty">Difficulty Level</label>
                    <select id="editQuizDifficulty" name="difficulty_level">
                        <option value="easy">Easy</option>
                        <option value="medium">Medium</option>
                        <option value="hard">Hard</option>
                    </select>
                </div>

                <div id="questionsContainer">
                    <!-- Questions will be loaded here dynamically -->
                </div>

                <div class="form-actions">
                    <button type="button" id="addQuestionBtn" class="btn">Add New Question</button>
                    <button type="submit" class="btn">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
    </div>

    <script>
        // Auto-hide success and error messages after 3 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const messages = document.querySelectorAll('.success-message, .error-message');
            messages.forEach(message => {
                setTimeout(() => {
                    message.classList.add('fade-out');
                    setTimeout(() => {
                        message.style.display = 'none';
                    }, 500);
                }, 3000);
            });
        });
    </script>

    <script src="../../assets/js/quiz_delete.js"></script>
    <script src="../../assets/js/quiz_editor.js"></script>
</body>
</html>
