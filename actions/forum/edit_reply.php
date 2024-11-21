<?php
// actions/forum/edit_reply.php
require_once '../../db/database.php';
require_once '../../utils/session_helper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $replyId = filter_input(INPUT_POST, 'reply_id', FILTER_VALIDATE_INT);
    $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_STRING);
    $userId = $_SESSION['user_id'];
    
    if ($replyId && !empty($content)) {
        $db = new Database();
        $conn = $db->getConnection();
        
        try {
            // Verify user owns the reply or is a teacher
            $stmt = $conn->prepare("
                SELECT topic_id, user_id 
                FROM forum_replies 
                WHERE reply_id = ?
            ");
            $stmt->execute([$replyId]);
            $reply = $stmt->fetch();
            
            if ($reply && ($reply['user_id'] == $userId || $_SESSION['role'] === 'teacher')) {
                $stmt = $conn->prepare("
                    UPDATE forum_replies 
                    SET content = ?, 
                        last_edited_date = CURRENT_TIMESTAMP 
                    WHERE reply_id = ?
                ");
                $stmt->execute([$content, $replyId]);
                
                header("Location: ../../view/forum/topic.php?id=" . $reply['topic_id']);
                exit();
            }
        } catch (PDOException $e) {
            error_log("Error editing reply: " . $e->getMessage());
        }
    }
    
    header("Location: ../../view/forum/index.php");
    exit();
}