<?php
session_start();
require_once '../../db/database.php';
require_once '../../functions/admin_functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit('Unauthorized');
}

$topic_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$topic_id) {
    $_SESSION['error'] = "Invalid topic ID.";
    header('Location: ../../view/admin/manage_forum.php');
    exit();
}

try {
    $adminFunctions = new AdminFunctions($pdo);
    $stmt = $pdo->prepare("SELECT topic_id FROM forum_topics WHERE topic_id = ?");
    $stmt->execute([$topic_id]);
    
    if (!$stmt->fetch()) {
        throw new Exception("Topic not found.");
    }

    $deleted = $adminFunctions->deleteTopic($topic_id);

    if ($deleted) {
        $_SESSION['success'] = "Topic and all associated replies have been successfully deleted.";
    } else {
        $_SESSION['error'] = "Failed to delete topic.";
    }

} catch (PDOException $e) {
    error_log("Error deleting topic: " . $e->getMessage());
    $_SESSION['error'] = "A database error occurred while deleting the topic.";
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
}

header('Location: ../../view/admin/manage_forum.php');
exit();