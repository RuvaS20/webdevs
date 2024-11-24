//update quiz
<?php
session_start();
require_once '../../db/database.php';

header('Content-Type: application/json');

// Check if user is logged in as teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

try {
    // Get JSON data
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        throw new Exception('Invalid data format');
    }

    // Validate required fields
    if (empty($data['quiz_id']) || empty($data['title'])) {
        throw new Exception('Missing required fields');
    }

    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Start transaction
    $conn->begin_transaction();
    
    // Update quiz details
    $stmt = $conn->prepare("
        UPDATE quizzes 
        SET title = ?, 
            description = ?, 
            difficulty_level = ?
        WHERE quiz_id = ? AND teacher_id = ?
    ");
    
    $stmt->bind_param("sssii", 
        $data['title'],
        $data['description'],
        $data['difficulty_level'],
        $data['quiz_id'],
        $_SESSION['user_id']
    );
    
    $stmt->execute();
    
    // Delete existing questions
    $stmt = $conn->prepare("DELETE FROM quiz_questions WHERE quiz_id = ?");
    $stmt->bind_param("i", $data['quiz_id']);
    $stmt->execute();
    
    // Insert updated questions
    if (!empty($data['questions'])) {
        $stmt = $conn->prepare("
            INSERT INTO quiz_questions 
            (quiz_id, question_text, question_type, correct_answer, points, order_number)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($data['questions'] as $index => $question) {
            $points = isset($question['points']) ? $question['points'] : 1;
            $stmt->bind_param("isssii",
                $data['quiz_id'],
                $question['question_text'],
                $question['question_type'],
                $question['correct_answer'],
                $points,
                $index + 1
            );
            $stmt->execute();
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Quiz updated successfully'
    ]);
    
} catch (Exception $e) {
    // Rollback transaction if there was an error
    if (isset($conn)) {
        $conn->rollback();
    }
    
    error_log("Error updating quiz: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error updating quiz: ' . $e->getMessage()
    ]);
}
?>