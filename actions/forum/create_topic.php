<?php
// actions/forum/create_topic.php
require_once '../../db/database.php';
require_once '../../utils/validation.php';
require_once '../../utils/session_helper.php';

function createTopic($userId, $title, $content, $category) {
    $db = new Database();
    $conn = $db->getConnection();
    
    try {
        $stmt = $conn->prepare("
            INSERT INTO forum_topics (user_id, title, content, category) 
            VALUES (?, ?, ?, ?)
        ");
        
        $stmt->execute([$userId, $title, $content, $category]);
        return $conn->lastInsertId();
    } catch (PDOException $e) {
        error_log("Error creating topic: " . $e->getMessage());
        return false;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_STRING);
    $category = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_STRING);
    
    if (!empty($title) && !empty($content) && !empty($category)) {
        $userId = $_SESSION['user_id'];
        $topicId = createTopic($userId, $title, $content, $category);
        
        if ($topicId) {
            header("Location: ../../view/forum/topic.php?id=" . $topicId);
            exit();
        }
    }
    
    header("Location: ../../view/forum/create_topic.php?error=1");
    exit();
}
?>