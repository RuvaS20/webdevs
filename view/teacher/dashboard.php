<?php
session_start();
require_once '../../db/database.php';

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
        WHERE q.teacher_id = :teacher_id
        GROUP BY q.quiz_id
        ORDER BY q.created_date DESC
    ");

    $stmt->execute([':teacher_id' => $_SESSION['user_id']]);
    $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);  // Added PDO::FETCH_ASSOC here

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
    $recent_attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);  // Added PDO::FETCH_ASSOC here

} catch(PDOException $e) {
    echo "Database Error: " . $e->getMessage();
    die();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f4f6f8;
        }

        .dashboard {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }

        .header h1 {
            color: #333;
        }

        .create-quiz-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
        }

        .create-quiz-btn:hover {
            background-color: #45a049;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }

        .card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .card h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 1.5em;
        }

        .quiz-list {
            list-style: none;
        }

        .quiz-item {
            border: 1px solid #eee;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 10px;
        }

        .quiz-item h3 {
            color: #444;
            margin-bottom: 5px;
        }

        .quiz-stats {
            display: flex;
            gap: 20px;
            margin: 10px 0;
            color: #666;
            font-size: 0.9em;
        }

        .quiz-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .btn {
            padding: 5px 15px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9em;
        }

        .btn-view {
            background-color: #2196F3;
            color: white;
        }

        .btn-edit {
            background-color: #FFC107;
            color: #000;
        }

        .btn-delete {
            background-color: #f44336;
            color: white;
        }

        .attempt-list {
            list-style: none;
        }

        .attempt-item {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .attempt-item:last-child {
            border-bottom: none;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }

        .stat-card h3 {
            font-size: 2em;
            margin-bottom: 5px;
        }

        .stat-card p {
            font-size: 0.9em;
            opacity: 0.9;
        }

        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            display: none;
            z-index: 1000;
        }

        .modal-content {
            position: relative;
            background-color: #fff;
            margin: 5% auto;
            padding: 20px;
            width: 80%;
            max-width: 800px;
            border-radius: 8px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .close {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 28px;
            cursor: pointer;
        }

        .question-block {
            border: 1px solid #ddd;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
        }

        .answer-options {
            margin-top: 10px;
        }

        .option-block {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 5px 0;
        }

        .remove-question {
            color: #f44336;
            cursor: pointer;
            float: right;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .success-message, .error-message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .success-message.fade-out, .error-message.fade-out {
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
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
            <a href="create-quiz.php" class="create-quiz-btn">Create New Quiz</a>
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
                                <li class="quiz-item">
                                    <h3><?php echo htmlspecialchars($quiz['title']); ?></h3>
                                    <p><?php echo htmlspecialchars($quiz['description']); ?></p>
                                    <div class="quiz-stats">
                                        <span>Difficulty: <?php echo htmlspecialchars($quiz['difficulty_level']); ?></span>
                                        <span>Attempts: <?php echo $quiz['attempt_count']; ?></span>
                                        <span>Average Score: <?php echo $quiz['average_score'] ? round($quiz['average_score'], 1) : 0; ?>%</span>
                                    </div>
                                    <div class="quiz-actions">
                                        <a href="view-results.php?id=<?php echo $quiz['quiz_id']; ?>" class="btn btn-view">View Results</a>
                                        <a href="#" onclick="openEditQuizModal(<?php echo $quiz['quiz_id']; ?>)" class="btn btn-edit">Edit Quiz</a>
                                        <button onclick="deleteQuiz(<?php echo $quiz['quiz_id']; ?>)" class="btn btn-delete">Delete</button>
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
                                    <p>Score: <?php echo $attempt['total_score']; ?></p>
                                    <p>Date: <?php echo date('M d, Y', strtotime($attempt['start_time'])); ?></p>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Edit Quiz Modal -->
        <div id="editQuizModal" class="modal" style="display: none;">
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
                            <textarea id="editQuizDescription" name="description"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="editQuizDifficulty">Difficulty Level</label>
                            <select id="editQuizDifficulty" name="difficulty_level">
                                <option value="beginner">Beginner</option>
                                <option value="intermediate">Intermediate</option>
                                <option value="advanced">Advanced</option>
                            </select>
                        </div>

                        <div id="questionsContainer">
                            <!-- Questions will be loaded here dynamically -->
                        </div>

                        <button type="button" id="addQuestionBtn" class="btn btn-secondary">Add New Question</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/quiz_delete.js"></script>
    <script src="../../assets/js/quiz_editor.js"></script>
</body>
</html>
