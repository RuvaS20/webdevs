<?php
// actions/forum/create_topic.php
session_start(); // Add this at the top

require_once '../../db/database.php';
require_once '../../utils/validation.php';
require_once '../../utils/session_helper.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../view/auth/login.php');
    exit();
}

// Move the function outside of the request handling
function createTopic($userId, $title, $content, $category) {
    try {
        // Create database connection using your existing database.php configuration
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        $stmt = $pdo->prepare("
            INSERT INTO forum_topics (user_id, title, content, category) 
            VALUES (:user_id, :title, :content, :category)
        ");
        
        $stmt->execute([
            ':user_id' => $userId,
            ':title' => $title,
            ':content' => $content,
            ':category' => $category
        ]);
        
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log("Error creating topic: " . $e->getMessage());
        return false;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $title = htmlspecialchars(trim($_POST['title'] ?? ''));
    $content = htmlspecialchars(trim($_POST['content'] ?? ''));
    $category = htmlspecialchars(trim($_POST['category'] ?? ''));
    
    // Validate inputs
    if (empty($title) || empty($content) || empty($category)) {
        $_SESSION['error'] = "All fields are required";
        header("Location: ../../view/forum/create_topic.php");
        exit();
    }
    
    // Get user ID from session
    $userId = $_SESSION['user_id'];
    
    // Create the topic
    $topicId = createTopic($userId, $title, $content, $category);
    
    if ($topicId) {
        $_SESSION['success'] = "Topic created successfully";
        header("Location: ../../view/forum/topic.php?id=" . $topicId);
        exit();
    } else {
        $_SESSION['error'] = "Error creating topic";
        header("Location: ../../view/forum/create_topic.php");
        exit();
    }
}

// If not POST request, redirect to create topic page
header("Location: ../../view/forum/create_topic.php");
exit();
?>