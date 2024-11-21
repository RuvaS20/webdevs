<?php
// actions/forum/post_reply.php
require_once '../../db/database.php';
require_once '../../utils/validation.php';
require_once '../../utils/session_helper.php';

function postReply($topicId, $userId, $content) {
    $db = new Database();
    $conn = $db->getConnection();
    
    try {
        // Begin transaction
        $conn->beginTransaction();
        
        // Insert reply
        $stmt = $conn->prepare("
            INSERT INTO forum_replies (topic_id, user_id, content) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$topicId, $userId, $content]);
        
        // Update reply count in topics table
        $stmt = $conn->prepare("
            UPDATE forum_topics 
            SET reply_count = reply_count + 1,
                last_updated_date = CURRENT_TIMESTAMP 
            WHERE topic_id = ?
        ");
        $stmt->execute([$topicId]);
        
        $conn->commit();
        return true;
    } catch (PDOException $e) {
        $conn->rollBack();
        error_log("Error posting reply: " . $e->getMessage());
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $topicId = filter_input(INPUT_POST, 'topic_id', FILTER_VALIDATE_INT);
    $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_STRING);
    
    if ($topicId && !empty($content)) {
        $userId = $_SESSION['user_id'];
        if (postReply($topicId, $userId, $content)) {
            header("Location: ../../view/forum/topic.php?id=" . $topicId);
            exit();
        }
    }
    
    header("Location: ../../view/forum/topic.php?id=" . $topicId . "&error=1");
    exit();
}
?>